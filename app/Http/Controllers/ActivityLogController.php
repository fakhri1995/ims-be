<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Spatie\Activitylog\Models\Activity;
use App\InventoryColumn;
use App\Vendor;
use App\Asset;
use Exception;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    // Normal Route

    public function getActivityInventoryLogs(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }

        $id = $request->get('id', null);
        try{
            $logs = [];
            if($id === null) return response()->json(["success" => false, "message" => "Silahkan Tambahkan Parameter Id"], 400);
            $inventory_logs = Activity::where('log_name', 'Inventory')->where('subject_id', $id)->get();
            foreach($inventory_logs as $inventory_log){
                $inventory_decode = json_decode($inventory_log->properties,true);
                if($inventory_log->description === 'created'){
                    $vendor = Vendor::where('id', $inventory_decode['attributes']['vendor_id'])->first();
                    if($vendor === null) $vendor_name = "Id Vendor Tidak Ditemukan";
                    else $vendor_name = $vendor->name;
                    $inventory_decode['attributes']['vendor_name'] = $vendor_name;
                    $asset = Asset::where('id', $inventory_decode['attributes']['asset_id'])->first();
                    if($asset === null) $asset_name = "Id Asset Tidak Ditemukan";
                    else $asset_name = $asset->name;
                    $inventory_decode['attributes']['asset_id_name'] = $asset_name;
                } else {
                    if (array_key_exists('vendor_id', $inventory_decode['attributes'])){
                        $vendor = Vendor::where('id', $inventory_decode['attributes']['vendor_id'])->first();
                        if($vendor === null) $vendor_name = "Id Vendor Tidak Ditemukan";
                        else $vendor_name = $vendor->name;
                        $inventory_decode['attributes']['vendor_name'] = $vendor_name;
                    }
                }
                $temp = (object) [
                    'date' => $inventory_log->created_at,
                    'description' => $inventory_log->description.' inventory',
                    'properties' => $inventory_decode
                ];
                array_push($logs, $temp);
            }   

            $inventory_value_logs = Activity::where('log_name', 'Inventory Value')->where('properties->attributes->inventory_id', $id)->get();
            $inventory_columns = InventoryColumn::select('id','name')->get();
            foreach($inventory_value_logs as $inventory_value_log){
                if($inventory_value_log->description === 'updated'){
                    $inventory_decode = json_decode($inventory_value_log->properties,true);
                    $inventory_decode['attributes']['name'] = $inventory_columns->where('id', $inventory_decode['attributes']['inventory_column_id'])->first()->name;
                    $temp = (object) [
                        'date' => $inventory_value_log->created_at,
                        'description' => $inventory_value_log->description.' inventory value',
                        'properties' => $inventory_decode
                    ];
                    array_push($logs, $temp);
                }
            } 

            usort($logs, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });
            
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $logs]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}