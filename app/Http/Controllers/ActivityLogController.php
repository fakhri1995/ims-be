<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Spatie\Activitylog\Models\Activity;
use App\InventoryColumn;
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
                $temp = (object) [
                    'date' => $inventory_log->created_at,
                    'description' => $inventory_log->description.' inventory',
                    'properties' => $inventory_log->properties
                ];
                array_push($logs, $temp);
            }   

            $inventory_value_logs = Activity::where('log_name', 'Inventory Value')->where('properties->attributes->inventory_id', $id)->get();
            $inventory_columns = InventoryColumn::select('id','name')->get();
            foreach($inventory_value_logs as $inventory_value_log){
                if($inventory_value_log->description === 'updated'){
                    $temp = (object) [
                        'date' => $inventory_value_log->created_at,
                        'description' => $inventory_value_log->description.' inventory value',
                        'properties' => $inventory_value_log->properties,
                        'column_name' => $inventory_columns->where('id', $inventory_value_log->properties['attributes']['inventory_column_id'])->first()->name
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