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
use App\Vendor;
use DB;
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


    public function getData($parent){
        $assets = Asset::where('code', 'like', $parent.".%")->where('code', 'not like', $parent.".___.%")->orderBy('code', 'asc')->get();
        $new_assets = [];
        foreach($assets as $asset){
            $data = $this->getData($asset->code);
            if($data !== []){
                $temp = (object)[
                    'id' => $asset->id,
                    'title' => $asset->name,
                    'key' => $asset->code,
                    'value' => $asset->code,
                    'children' => $data
                ];
            } else {
                $temp = (object)[
                    'id' => $asset->id,
                    'title' => $asset->name,
                    'key' => $asset->code,
                    'value' => $asset->code
                ];
            }
            $new_assets[] = $temp;
        }
        return $new_assets;
    }

    // Asset 
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
            // $assets = Asset::orderBy('code')->get();
            $assets = Asset::where('code', 'not like', "%.%")->orderBy('code')->get();
            $new_assets = [];
            foreach($assets as $asset){
                $temp = (object)[
                    'id' => $asset->id,
                    'title' => $asset->name,
                    'key' => $asset->code,
                    'value' => $asset->code,
                    'children' => $this->getData($asset->code)
                ];
                $new_assets[] = $temp;
            }
            if($assets->isEmpty()) return response()->json(["success" => true, "message" => "Asset Belum Terisi"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $new_assets]);
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
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
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
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
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
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
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

            $inventory_columns = InventoryColumn::where('asset_id', $id)->get();
            foreach($inventory_columns as $inventory_column){
                $inventory_column->delete();
            }

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

    //Inventory Column
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
            $id = $request->get('id', null);
            $inventory_columns = InventoryColumn::where('asset_id', $id)->get();
            if($inventory_columns === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $vendors = Vendor::select('id','name')->get();
            $assets = Asset::select('id','name')->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["inventory_columns" => $inventory_columns, "vendors" => $vendors, "assets" => $assets]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function cudInventoryColumn(Request $request)
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
            $asset_id = $request->get('asset_id', null);
            if($asset_id === null) return response()->json(["success" => false, "message" => "Parameter Asset ID Kosong"], 400);
            $add_inventory_columns = $request->get('add_inventory_columns', []);
            $update_inventory_columns = $request->get('update_inventory_columns', []);
            $delete_inventory_columns = $request->get('delete_inventory_columns', []);

            foreach($add_inventory_columns as $add_inventory_column){
                $inventory_column = new InventoryColumn;
                $inventory_column->asset_id = $asset_id;
                $inventory_column->name = $add_inventory_column['name'];
                $inventory_column->data_type = $add_inventory_column['data_type'];
                $inventory_column->default = $add_inventory_column['default'];
                $inventory_column->required = $add_inventory_column['required'];
                $inventory_column->unique = $add_inventory_column['unique'];
                $inventory_column->save();
            }

            foreach($update_inventory_columns as $update_inventory_column){
                $inventory_column = InventoryColumn::find($update_inventory_column['id']);
                if($update_inventory_column === null) return response()->json(["success" => false, "message" => "Salah Satu Data Update Tidak Ditemukan"], 400);
                $inventory_column->name = $update_inventory_column['name'];
                $inventory_column->data_type = $update_inventory_column['data_type'];
                $inventory_column->default = $update_inventory_column['default'];
                $inventory_column->required = $update_inventory_column['required'];
                $inventory_column->unique = $update_inventory_column['unique'];
                $inventory_column->save();
            }

            foreach($delete_inventory_columns as $delete_inventory_column){
                $inventory_column = InventoryColumn::find($delete_inventory_column['id']);
                if($inventory_column === null) return response()->json(["success" => false, "message" => "Salah Satu Data Delete Tidak Ditemukan"], 400);
                $inventory_column->delete();
            }

            return response()->json(["success" => true, "message" => "Data Berhasil Diproses"]);
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
        $inventory_column = InventoryColumn::find($id);
        if($inventory_column === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            $inventory_column->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    //Inventory Value
    public function getInventoryValues(Request $request)
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
            $inventory_values = InventoryValue::all();
            if($inventory_values->isEmpty()) return response()->json(["success" => true, "message" => "Data Inventory Column Belum Terisi"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory_values]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addInventoryValue(Request $request)
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
        $inventory_value = new InventoryValue;
        $inventory_value->inventory_id = $request->get('inventory_id');
        $inventory_value->inventory_column_id = $request->get('inventory_column_id');
        $inventory_value->value = $request->get('value');
        try{
            $inventory_value->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateInventoryValue(Request $request)
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
            "value" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }
        $id = $request->get('id', null);
        try{
            $inventory_value = InventoryValue::find($id);
            if($inventory_value === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $inventory_value->value = $request->get('value');
            $inventory_value->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }    
    
    public function deleteInventoryValue(Request $request)
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
        $inventory_value = InventoryValue::find($id);
        if($inventory_value === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            $inventory_value->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    //Inventory Value
    public function getAllInventories(Request $request)
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
            $inventories = Inventory::all();
            if($inventories->isEmpty()) return response()->json(["success" => true, "message" => "Data Inventory Belum Terisi"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
    
    public function getAssetInventories(Request $request)
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
            $inventories = Inventory::where('asset_id', $id)->get();
            if($inventories->isEmpty()) return response()->json(["success" => true, "message" => "Data Inventory pada Aset Ini Belum Terisi"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
    
    public function getInventory(Request $request)
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
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            // $additional_attributes = DB::table('inventory_values')
            // ->where('inventory_values.deleted_at', null)->where('inventory_values.inventory_id', '=', $id)
            // ->join('inventory_columns', 'inventory_values.inventory_column_id', '=', 'inventory_columns.id')
            // ->select('inventory_values.id', 'inventory_columns.name', 'inventory_values.value')
            // ->get();
            // $inventory['additional_attributes'] = $additional_attributes;
            $inventory_values = InventoryValue::all();
            $inventory_columns = InventoryColumn::select('id','name','data_type')->get();
            $temp_values = $inventory_values->where('inventory_id',$id);
            $needed_inventory_values = [];
            foreach($temp_values as $temp_value){
                $inventory_column = $inventory_columns->where('id', $temp_value->inventory_column_id)->first();
                if($inventory_column === null){
                    $inventory->additional_attributes = "Inventory Column Name of an Inventory Value not Found";
                    return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory]);
                } 
                $temp_value->name = $inventory_column->name;
                $temp_value->data_type = $inventory_column->data_type;
                array_push($needed_inventory_values, $temp_value);
            }
            $inventory->additional_attributes = $needed_inventory_values;
            $vendors = Vendor::select('id','name')->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)['inventory' => $inventory, 'vendors' => $vendors] ]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addInventory(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
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
            "asset_id" => "required",
            "vendor_id" => "required",
            "asset_code" => "required",
            "asset_name" => "required",
            "status" => "required",
            "kepemilikan" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }
        $inventory = new Inventory;
        $inventory->asset_id = $request->get('asset_id');
        $inventory->vendor_id = $request->get('vendor_id');
        $inventory->asset_code = $request->get('asset_code');
        $inventory->asset_name = $request->get('asset_name');
        $inventory->mig_number = $request->get('mig_number');
        $inventory->serial_number = $request->get('serial_number');
        $inventory->model = $request->get('model');
        $inventory->invoice_label = $request->get('invoice_label');
        $inventory->status = $request->get('status');
        $inventory->kepemilikan = $request->get('kepemilikan');
        $inventory->kondisi = $request->get('kondisi');
        $inventory->tanggal_beli = $request->get('tanggal_beli');
        $inventory->harga_beli = $request->get('harga_beli');
        $inventory->tanggal_efektif = $request->get('tanggal_efektif');
        $inventory->depresiasi = $request->get('depresiasi');
        $inventory->nilai_sisa = $request->get('nilai_sisa');
        $inventory->nilai_buku = $request->get('nilai_buku');
        $inventory->masa_pakai = $request->get('masa_pakai');
        $inventory->lokasi = $request->get('lokasi');
        $inventory->departmen = $request->get('departmen');
        $inventory->service_point = $request->get('service_point');
        $inventory->gudang = $request->get('gudang');
        $inventory->used_by = $request->get('used_by');
        $inventory->managed_by = $request->get('managed_by');
        $inventory_values = $request->get('inventory_values',[]);
        try{
            $inventory->save();
            foreach($inventory_values as $inventory_value){
                $model = new InventoryValue;
                $model->inventory_id = $inventory->id;
                $model->inventory_column_id = $inventory_value['inventory_column_id'];
                $model->value = $inventory_value['value'];
                $model->save();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateInventory(Request $request)
    {
        
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
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
            "vendor_id" => "required",
            "asset_code" => "required",
            "asset_name" => "required",
            "status" => "required",
            "kepemilikan" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }
        
        $id = $request->get('id', null);
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $inventory->vendor_id = $request->get('vendor_id');
            $inventory->asset_code = $request->get('asset_code');
            $inventory->asset_name = $request->get('asset_name');
            $inventory->mig_number = $request->get('mig_number');
            $inventory->serial_number = $request->get('serial_number');
            $inventory->model = $request->get('model');
            $inventory->invoice_label = $request->get('invoice_label');
            $inventory->status = $request->get('status');
            $inventory->kepemilikan = $request->get('kepemilikan');
            $inventory->kondisi = $request->get('kondisi');
            $inventory->tanggal_beli = $request->get('tanggal_beli');
            $inventory->harga_beli = $request->get('harga_beli');
            $inventory->tanggal_efektif = $request->get('tanggal_efektif');
            $inventory->depresiasi = $request->get('depresiasi');
            $inventory->nilai_sisa = $request->get('nilai_sisa');
            $inventory->nilai_buku = $request->get('nilai_buku');
            $inventory->masa_pakai = $request->get('masa_pakai');
            $inventory->lokasi = $request->get('lokasi');
            $inventory->departmen = $request->get('departmen');
            $inventory->service_point = $request->get('service_point');
            $inventory->gudang = $request->get('gudang');
            $inventory->used_by = $request->get('used_by');
            $inventory->managed_by = $request->get('managed_by');
            $inventory_values = $request->get('inventory_values',[]);
            $inventory->save();

            foreach($inventory_values as $inventory_value){
                $model = InventoryValue::find($inventory_value['id']);
                $model->value = $inventory_value['value'];
                $model->save();
            }

            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }    
    
    public function deleteInventory(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
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
        $inventory = Inventory::find($id);
        if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            // DB::table('inventories')->where('id', $inventory->id)->update(array('vendor_id' => $log_user_id));
            $inventory->delete();
            $inventory_values = InventoryValue::where('inventory_id', $id)->get();
            foreach($inventory_values as $inventory_value){
                $inventory_value->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}