<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Asset;
use App\Inventory;
use App\InventoryValue;
use App\InventoryColumn;
use Exception;

class AssetInventoryController extends Controller
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

    public function getAssets(Request $request)
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
        try{
            $assets = Asset::all();
            if($assets->isEmpty()) return response()->json(["success" => true, "message" => "Asset Belum Terisi"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $assets]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addAsset(Request $request)
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
        $asset = new Asset;
        $asset->name = $request->get('name');
        $parent = $request->get('parent', null);
        try{
            if($parent !== null){
                $assets = Asset::where('code', 'like', $parent.".%")->where('code', 'not like', $parent.".___.%")->orderBy('code', 'desc')->get();
                if(count($assets)){
                    $new_number = (int)substr($assets->first()->code, -3) + 1;
                    $new_string = (string)$new_number;
                    if($new_number < 10) {
                        $asset->code = $parent.".00".$new_string;
                    } else if($new_number < 100) {
                        $asset->code = $parent.".0".$new_string;
                    } else {
                        $asset->code = $parent.".".$new_string;
                    }
                } else {
                    $asset->code = $parent.".001";
                }
            } else {
                $assets = Asset::where('code', 'not like', "%.%")->orderBy('code', 'desc')->get();
                if(count($assets)){
                    $new_number = (int)$assets->first()->code + 1;
                    $new_string = (string)$new_number;
                    if($new_number < 10) {
                        $asset->code = "00".$new_string;
                    } else if($new_number < 100) {
                        $asset->code = "0".$new_string;
                    } else {
                        $asset->code = $new_string;
                    }
                } else {
                    $asset->code = "001";
                }
            }
            $asset->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateAsset(Request $request)
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
        $name = $request->get('name');
        $code = $request->get('code');
        try{
            $asset = Asset::find($id);
            if($asset === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $check_asset = Asset::where('code', $code)->first();
            if($check_asset && $code !== $asset->code) return response()->json(["success" => false, "message" => "Code Sudah Terpakai"], 400);
            $check_format_code = explode(".", $code);
            foreach($check_format_code as $checker){
                $checker = preg_replace( '/[^0-9]/', '', $checker);
                if(strlen($checker) !== 3) return response()->json(["success" => false, "message" => "Code Tidak Sesuai dengan Format"], 400);
            }
            $asset->name = $name;
            $asset->code = $code;
            $asset->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }    
    
    public function deleteAsset(Request $request)
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
        $asset = Asset::find($id);
        if($asset === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $asset->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getDeletedAssets(Request $request)
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
        try{
            $assets = Asset::onlyTrashed()->get();;
            if($assets->isEmpty()) return response()->json(["success" => true, "message" => "Belum ada Aset Terhapus"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $assets]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getInventoryColumns(Request $request)
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
        try{
            $assets = InventoryColumn::all();
            if($assets->isEmpty()) return response()->json(["success" => true, "message" => "Data Inventory Column Belum Terisi"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $assets]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addInventoryColumn(Request $request)
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
        $inventory_column = new InventoryColumn;
        $inventory_column->asset_id = $request->get('asset_id');
        $inventory_column->name = $request->get('name');
        $inventory_column->data_type = $request->get('data_type');
        $inventory_column->default = $request->get('default');
        $inventory_column->required = $request->get('required', true);
        $inventory_column->unique = $request->get('unique', true);
        try{
            $inventory_column->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateInventoryColumn(Request $request)
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
        $validator = Validator::make($request->all(), [
            "id" => "required",
            "asset_id" => "required",
            "name" => "required",
            "data_type" => "required",
            "required" => "required",
            "unique" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }
        $id = $request->get('id', null);
        try{
            $inventory_column = InventoryColumn::find($id);
            if($inventory_column === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $inventory_column->asset_id = $request->get('asset_id');
            $inventory_column->name = $request->get('name');
            $inventory_column->data_type = $request->get('data_type');
            $inventory_column->default = $request->get('default');
            $inventory_column->required = $request->get('required');
            $inventory_column->unique = $request->get('unique');
            $inventory_column->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }    
    
    public function deleteInventoryColumn(Request $request)
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
        $asset = InventoryColumn::find($id);
        if($asset === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            $asset->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}