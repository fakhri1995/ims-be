<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Spatie\Activitylog\Models\Activity;
use App\AccessFeature;
use App\Asset;
use App\AssetColumn;
use App\Inventory;
use App\InventoryValue;
use App\InventoryInventoryPivot;
use App\Manufacturer;
use App\ModelInventory;
use App\ModelInventoryColumn;
use App\ModelModelPivot;
use App\Relationship;
use App\RelationshipAsset;
use App\RelationshipInventory;
use App\Vendor;
use DB;
use Exception;

class AssetController extends Controller
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

    public function checkRoute($name, $auth)
    {
        $protocol = $name;
        $access_feature = AccessFeature::where('name',$protocol)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
                    "server_code" => 400,
                    "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
                ]
            ]];
        }
        $body = [
            'path_url' => $access_feature->feature_key
        ];
        $headers = [
            'Authorization' => $auth,
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/validate-feature', [
                    'headers'  => $headers,
                    'json' => $body
            ]);
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
            return ["success" => true, "id" => $log_user_id];
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]];
        }
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

    public function getAsset(Request $request)
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
            $asset = Asset::find($id);
            if($asset === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $asset_column = [];
            $asset_columns = AssetColumn::where('asset_id', $id)->get();
            $asset->asset_columns = $asset_columns;
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $asset]);
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
        $asset->description = $request->get('description');
        $asset->required_sn = $request->get('required_sn');
        $parent = $request->get('parent', null);
        try{
            if($parent !== null){
                $check_parent = Asset::where('code', $parent)->first();
                if($check_parent === null) return response()->json(["success" => false, "message" => "Parent Tidak Ditemukan"], 400);
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
            $asset_columns = $request->get('asset_columns', []);
            if(count($asset_columns)) {
                foreach($asset_columns as $asset_column){
                    $new_asset_column = new AssetColumn;
                    $new_asset_column->asset_id = $asset->id;
                    $new_asset_column->name = $asset_column['name'];
                    $new_asset_column->data_type = $asset_column['data_type'];
                    $new_asset_column->default = $asset_column['default'];
                    $new_asset_column->required = $asset_column['required'];
                    $new_asset_column->save();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan", "id" => $asset->id]);
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
        $description = $request->get('description');
        $required_sn = $request->get('required_sn');
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
            $asset->description = $description;
            $asset->required_sn = $required_sn;

            $asset_columns = AssetColumn::where('asset_id', $id)->get();
            $update_columns = $request->get('update_columns', []);
            $delete_column_ids = $request->get('delete_column_ids', []);
            if(count($update_columns)){
                foreach($update_columns as $update_column){
                    $check_column = $asset_columns->where('id', $update_column['id'])->first();
                    if($check_column === null) return response()->json(["success" => false, "message" => "Kolom Tidak Bisa Diupdate, Id Kolom Tidak Dimiliki Asset", "id" => $update_column['id']], 400);
                }
            }
            
            if(count($delete_column_ids)){
                foreach($delete_column_ids as $delete_column_id){
                    $check_column = $asset_columns->where('id', $delete_column_id)->first();
                    if($check_column === null) return response()->json(["success" => false, "message" => "Kolom Tidak Bisa Didelete, Id Kolom Tidak Dimiliki Asset", "id" => $delete_column_id], 400);
                }
            }
            
            if(count($update_columns)){
                foreach($update_columns as $update_column){
                    $update_asset_column = $asset_columns->where('id', $update_column['id'])->first();
                    $update_asset_column->name = $update_column['name'];
                    $update_asset_column->data_type = $update_column['data_type'];
                    $update_asset_column->default = $update_column['default'];
                    $update_asset_column->required = $update_column['required'];
                    $update_asset_column->save();
                }
            }

            if(count($delete_column_ids)){
                foreach($delete_column_ids as $delete_column_id){
                    $deleted_asset_column = $asset_columns->where('id', $delete_column_id)->first();
                    $deleted_asset_column->delete();
                }
            }
            
            $add_columns = $request->get('add_columns', []);
            if(count($add_columns)) {
                foreach($add_columns as $asset_column){
                    $new_asset_column = new AssetColumn;
                    $new_asset_column->asset_id = $asset->id;
                    $new_asset_column->name = $asset_column['name'];
                    $new_asset_column->data_type = $asset_column['data_type'];
                    $new_asset_column->default = $asset_column['default'];
                    $new_asset_column->required = $asset_column['required'];
                    $new_asset_column->save();
                }
            }

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
        $new_parent = $request->get('new_parent', null);
        $new_model_asset_id = $request->get('new_model_asset_id', null);
        if($new_parent !== null){
            $check_format_code = explode(".", $new_parent);
            foreach($check_format_code as $checker){
                $checker = preg_replace( '/[^0-9]/', '', $checker);
                if(strlen($checker) !== 3) return response()->json(["success" => false, "message" => "New Parent Tidak Sesuai dengan Format"], 400);
            }
        }
        $core_asset = Asset::find($id);
        if($core_asset === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $old_code = $core_asset->code;
        try{
            $assets = Asset::where('code', 'like', $core_asset->code.".%")->orderBy('code', 'asc')->get();
            if(count($assets)){
                if($new_parent !== null){
                    $assets_check_number = Asset::where('code', 'like', $new_parent.".%")->where('code', 'not like', $new_parent.".___.%")->orderBy('code', 'desc')->get();
                    if(count($assets_check_number)) $start_number = (int)substr($assets_check_number->first()->code, -3);
                    else $start_number = 0;
                    foreach($assets as $asset){
                        $split_string = str_split($asset->code,(strlen($old_code) + 4));
                        $array_length = count($split_string);
                        $back_string = "";
                        for($i = 1; $i < $array_length; $i++){
                            $back_string = $back_string . $split_string[$i];
                        }
                        $new_number = $start_number + (int)str_split($asset->code,(strlen($old_code) + 1))[1];
                        $new_string = (string)$new_number;
                        if($new_number < 10) {
                            $parent = $new_parent.".00".$new_string;
                        } else if($new_number < 100) {
                            $parent = $new_parent.".0".$new_string;
                        } else {
                            $parent = $new_parent.".".$new_string;
                        }
                        $new_code = $parent.$back_string;
                        $asset->code = $new_code;
                        $asset->save();
                    }
                } else {
                    foreach($assets as $asset){
                        $asset->delete();
                    }
                }
            }
            if($new_model_asset_id === null){
                $full_models = ModelInventory::get();
                if(count($assets)){
                    foreach($assets as $asset){
                        $models = $full_models->where('asset_id', $asset->id);
                        if(count($models)){
                            foreach($models as $model){
                                $model->delete();
                            }
                        }
                    }
                }
            } else {
                $models = ModelInventory::where('asset_id', $id)->get();
                if(count($models)){
                    foreach($models as $model){
                        $model->asset_id = $new_model_asset_id;
                        $model->save();
                    }
                }
            }
            // $models = ModelInventory::where('asset_id', $id)->get();
            // if($new_model_asset_id === null){
            //     foreach($models as $model){
            //         $model->delete();
            //     }
            // } else {
            //     foreach($models as $model){
            //         $model->asset_id = $new_model_asset_id;
            //         $model->save();
            //     }
            // }
            $core_asset->delete();

            $asset_columns = AssetColumn::where('asset_id', $id)->get();
            foreach($asset_columns as $asset_column){
                $asset_column->delete();
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


    // Model
    public function getChildModel($model_part, $models, $pivots, $model_columns, $assets){
        $search = array_search($model_part['child_id'], array_column($models, 'id'));
        if($search !== false){
            $model = $models[$search];
            $model_part['name'] = $model['name'];
            $model_child = [];
            foreach($pivots as $pivot){
                if($pivot['parent_id'] === $model_part['child_id']){
                    $model_child[] = $this->getChildModel($pivot, $models, $pivots, $model_columns, $assets);
                }
            }
            $temp_model_columns = [];
            foreach($model_columns as $model_column){
                if($model_column['model_id'] === $model_part['child_id']){
                    $temp_model_columns[] = $model_column;
                }
            }
            foreach($assets as $asset){
                if($model['asset_id'] === $asset['id']){
                    $asset_name = $asset['name'];
                    break;
                }
            }
            $model_part['asset_name'] = $asset_name ? $asset_name : "Asset Tidak Ditemukan";
            $model_part['model_column'] = $temp_model_columns;
            $model_part['model_child'] = $model_child;
            return $model_part;
        } else {
            $template = ['id' => 0, "parent_id" => $model_part['parent_id'], "child_id" => $model_part['child_id'], "quantity" => 0, "deleted_at" => null, "name" => "Model Tidak Ditemukan", "model_column" => [], "model_child" => []];
            return $template;   
        }
    }

    public function getModels(Request $request)
    {
        // $check = $this->checkRoute("MODELS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $models = ModelInventory::get();
        if($models->isEmpty()) return response()->json(["success" => true, "message" => "Model Belum Terisi", "data" => []]);
        $inventories = Inventory::get();
        $assets = Asset::select('id', 'name', 'code')->get();
        foreach($models as $model){
            $model->count = $inventories->where('model_id', $model->id)->count();
            $asset = $assets->where('id', $model->asset_id)->first();
            if($asset === null) {
                $model->asset_name = "Asset Tidak Ditemukan";
            } else {
                $model->asset_name = $asset->name;
                if(strlen($asset->code) > 3){
                    $parent_model = substr($asset->code, 0, 3);
                    $parent_name = $assets->where('code', $parent_model)->first();
                    $parent_name = $parent_name === null ? "Asset Not Found" : $parent_name->name;
                    $model->asset_name = $parent_name . " / ". $model->asset_name;
                }
            }
        }
        return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $models]);
    }

    public function getModel(Request $request)
    {
        // $check = $this->checkRoute("MODEL_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id');
            $model = ModelInventory::withTrashed()->find($id);
            if($model === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $assets = Asset::withTrashed()->get();
            $asset = $assets->where(('id'), $model->asset_id)->first();
            $model->count = Inventory::where('model_id', $id)->count();
            if($asset === null) {
                $model->asset = [
                    "id" => $model->asset_id,
                    "name" => "Data Tidak Ditemukan",
                    "code" => "Data Tidak Ditemukan",
                    "description" => "Data Tidak Ditemukan",
                    "deleted_at" => null
                ];
            } else {
                $model->asset = $asset;
            }
            // $asset_columns = AssetColumn::where('asset_id', $model->asset_id)->get();
            $model->manufacturer = Manufacturer::withTrashed()->find($model->manufacturer_id);
            // $model->asset_columns = $asset_columns;
            $model_parts = ModelModelPivot::where('parent_id', $id)->get();
            $model_columns = ModelInventoryColumn::get();
            $core_model_columns = [];
            $temp_model_columns = $model_columns->where('model_id', $id);
            foreach($temp_model_columns as $temp_model_column){
                $core_model_columns[] = $temp_model_column;
            }
            $model->model_columns = $core_model_columns;
            $full_model_parts = [];
            if(count($model_parts)){
                $model_columns = $model_columns->toArray();
                $assets = $assets->toArray();
                $models = ModelInventory::get()->toArray();
                $pivots = ModelModelPivot::get()->toArray();
                foreach($model_parts as $model_part){
                    $full_model_parts[] = $this->getChildModel($model_part, $models, $pivots, $model_columns, $assets); 
                }
            }
            
            $model->model_parts = $full_model_parts; 
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $model]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        
    }

    public function getModelRelations(Request $request){
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        try{
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
            $manufacturers = Manufacturer::select('id', 'name')->get();
            $data = (object)['assets' => $new_assets, 'manufacturers' => $manufacturers];
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $data]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addModel(Request $request)
    {
        // $check = $this->checkRoute("MODEL_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $name = $request->get('name');
        $check_name = ModelInventory::where('name', $name)->first();
        if($check_name !== null) return response()->json(["success" => false, "message" => "Nama Model Telah Terdaftar"]);
        $model = new ModelInventory;
        $model->asset_id = $request->get('asset_id');
        $model->name = $name;
        $model->description = $request->get('description');
        $model->manufacturer_id = $request->get('manufacturer_id');
        $model->required_sn = $request->get('required_sn');
        try{
            $model->save();
            $model_columns = $request->get('model_columns', []);
            if(count($model_columns)) {
                foreach($model_columns as $model_column){
                    $new_model_column = new ModelInventoryColumn;
                    $new_model_column->model_id = $model->id;
                    $new_model_column->name = $model_column['name'];
                    $new_model_column->data_type = $model_column['data_type'];
                    $new_model_column->default = $model_column['default'];
                    $new_model_column->required = $model_column['required'];
                    $new_model_column->save();
                }
            }
            $model_parts = $request->get('model_parts', []);
            if(count($model_parts)){
                foreach($model_parts as $model_part){
                    $new_model_part = new ModelModelPivot;
                    $new_model_part->parent_id = $model->id;
                    $new_model_part->child_id = $model_part['id'];
                    $new_model_part->quantity = $model_part['quantity'];
                    $new_model_part->save();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan", "id" => $model->id]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateModel(Request $request)
    {
        // $check = $this->checkRoute("MODEL_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id');
        $model = ModelInventory::find($id);
        if($model === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        $model->asset_id = $request->get('asset_id');
        $model->name = $request->get('name');
        $model->description = $request->get('description');
        $model->manufacturer_id = $request->get('manufacturer_id');
        $model->required_sn = $request->get('required_sn');
        try{
            $delete_column_ids = $request->get('delete_column_ids', []);
            $update_columns = $request->get('update_columns', []);
            $model_columns = ModelInventoryColumn::where('model_id', $id)->get();
            if(count($update_columns)){
                foreach($update_columns as $update_column){
                    $check_column = $model_columns->where('id', $update_column['id'])->first();
                    if($check_column === null) return response()->json(["success" => false, "message" => "Kolom Tidak Bisa Diupdate, Id Kolom Tidak Dimiliki Model", "id" => $update_column['id']], 400);
                }
            }
            
            if(count($delete_column_ids)){
                foreach($delete_column_ids as $delete_column_id){
                    $check_column = $model_columns->where('id', $delete_column_id)->first();
                    if($check_column === null) return response()->json(["success" => false, "message" => "Kolom Tidak Bisa Didelete, Id Kolom Tidak Dimiliki Model", "id" => $delete_column_id], 400);
                }
            }
            
            if(count($update_columns)){
                foreach($update_columns as $update_column){
                    $update_model_column = $model_columns->where('id', $update_column['id'])->first();
                    $update_model_column->name = $update_column['name'];
                    $update_model_column->data_type = $update_column['data_type'];
                    $update_model_column->default = $update_column['default'];
                    $update_model_column->required = $update_column['required'];
                    $update_model_column->save();
                }
            }

            if(count($delete_column_ids)){
                $inventory_values = InventoryValue::get();
                foreach($delete_column_ids as $delete_column_id){
                    $inventory_value_models = $inventory_values->where('model_inventory_column_id', $delete_column_id);
                    foreach($inventory_value_models as $inventory_value_model){
                        $inventory_value_model->delete();
                    }
                    $deleted_model_column = $model_columns->where('id', $delete_column_id)->first();
                    if($deleted_model_column !== null)$deleted_model_column->delete();
                }
            }
            $add_columns = $request->get('add_columns', []);
            if(count($add_columns)) {
                foreach($add_columns as $model_column){
                    $new_model_column = new ModelInventoryColumn;
                    $new_model_column->model_id = $model->id;
                    $new_model_column->name = $model_column['name'];
                    $new_model_column->data_type = $model_column['data_type'];
                    $new_model_column->default = $model_column['default'];
                    $new_model_column->required = $model_column['required'];
                    $new_model_column->save();
                }
            }
            
            $delete_model_ids = $request->get('delete_model_ids', []);
            $model_pivots = ModelModelPivot::where('parent_id', $id)->get();
            if(count($delete_model_ids)){
                foreach($delete_model_ids as $delete_model_id){
                    $deleted_model = $model_pivots->where('child_id', $delete_model_id)->first();
                    if($deleted_model !== null) $deleted_model->delete();
                }
            }
            
            $add_models = $request->get('add_models', []);
            if(count($add_models)) {
                foreach($add_models as $model_column){
                    $new_model = new ModelModelPivot;
                    $new_model->parent_id = $id;
                    $new_model->child_id = $model_column['id'];
                    $new_model->quantity = $model_column['quantity'];
                    $new_model->save();
                }
            }
            $model->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteModel(Request $request)
    {
        // $check = $this->checkRoute("MODEL_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id');
        $model = ModelInventory::find($id);
        if($model === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            $columns = ModelInventoryColumn::where('model_id', $id)->get();
            foreach($columns as $column){
                $column->delete();
            }
            $pivots = ModelModelPivot::where('parent_id', $id)->get();
            foreach($pivots as $pivot){
                $pivot->delete();
            }
            $pivots = ModelModelPivot::where('child_id', $id)->get();
            foreach($pivots as $pivot){
                $pivot->child_id = 0;
                $pivot->save();
            }
            $model->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }


    // Inventory
    public function saveInventoryChild($inventory, $location, $parent_id, $causer_id){
        $new_inventory = new Inventory;
        $new_inventory->model_id = $inventory['model_id'];
        $new_inventory->vendor_id = 0;
        $new_inventory->inventory_name = $inventory['inventory_name'];
        $new_inventory->status_condition = $inventory['status_condition'];
        $new_inventory->status_usage = 1;
        $new_inventory->location = $location;
        $new_inventory->is_exist = $inventory['is_exist'];
        $new_inventory->deskripsi = $inventory['deskripsi'];
        $new_inventory->manufacturer_id = $inventory['manufacturer_id'];
        $new_inventory->mig_id = $inventory['mig_id'];
        $new_inventory->serial_number = $inventory['serial_number'];
        $inventory_values = $inventory['inventory_values'];
        $inventory_parts = $inventory['inventory_parts'];
        try{
            $new_inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $causer_id;
            $last_activity->causer_type = "Created as part of inventory with id ".$parent_id;
            $last_activity->save();
            $pivot = new InventoryInventoryPivot;
            $pivot->parent_id = $parent_id;
            $pivot->child_id = $new_inventory->id;
            $pivot->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $causer_id;
            $last_activity->save();

            if(count($inventory_values)){
                foreach($inventory_values as $inventory_value){
                    $model = new InventoryValue;
                    $model->inventory_id = $new_inventory->id;
                    $model->model_inventory_column_id = $inventory_value['model_inventory_column_id'];
                    $model->value = $inventory_value['value'];
                    $model->save();
                    $last_activity = Activity::all()->last();
                    $last_activity->causer_id = $causer_id;
                    $last_activity->save();
                }
            } 
            if(count($inventory_parts)){
                foreach($inventory_parts as $inventory_part){
                    $this->saveInventoryChild($inventory_part, $location, $new_inventory->id, $causer_id);
                }
            }
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getInventoryChildren($pivot, $pivots, $inventories, $models, $assets){
        $inventory = null;
        foreach($inventories as $check_inventory){
            if($check_inventory['id'] === $pivot['child_id']){
                $inventory = $check_inventory;
                break;
            }
        }
        if($inventory !== null){
            $temp_model = null;
            foreach($models as $model){
                if($model['id'] === $inventory['model_id']){
                    $temp_model = $model;
                }
            }
            if($temp_model === null){
                // $temp_model = ['id' => 0, "parent_id" => $model_part['parent_id'], "child_id" => $model_part['child_id'], "quantity" => 0, "deleted_at" => null, "name" => "Model Tidak Ditemukan", "model_column" => [], "model_child" => []];
                // $temp_asset = [
                //     "id" => 0,
                //     "name" => "Model Tidak Ditemukan",
                //     "code" => "Model Tidak Ditemukan",
                //     "description" => "Model Tidak Ditemukan",
                //     "deleted_at" => null
                // ];
                $temp_model['name'] = "Model Tidak Ditemukan";
                $temp_asset['name'] = "Model Tidak Ditemukan";
            } else {
                $temp_asset = null;
                foreach($assets as $asset){
                    if($asset['id'] === $temp_model['asset_id']){
                        $temp_asset = $asset;
                    }
                }
                if($temp_asset === null){
                    $temp_asset['name'] = "Asset Tidak Ditemukan";
                }
            }
            $inventory_parts = [];
            foreach($pivots as $p){
                if($p['parent_id'] === $pivot['child_id']){
                    $inventory_parts[] = $this->getInventoryChildren($p, $pivots, $inventories, $models, $assets);
                }
            }
            $data = [
                'id' => $inventory['id'],
                'serial_number' => $inventory['serial_number'],
                'model' => $temp_model['name'],
                'asset' => $temp_asset['name'],
                'inventory_parts' => $inventory_parts
            ];
        } else {
            $data = [
                'id' => 0,
                'serial_number' => "Inventory Tidak Ditemukan",
                'model' => "Inventory Tidak Ditemukan",
                'asset' => "Inventory Tidak Ditemukan",
                'inventory_parts' => []
            ];
        }
        return $data;
    }

    public function getInventoryRelations(Request $request){
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        try{
            $models = ModelInventory::select('id','name','asset_id')->get();
            $manufacturers = Manufacturer::select('id', 'name')->get();
            $assets = Asset::select('id', 'name')->get();
            $vendors = Vendor::select('id', 'name')->get();
            $status_condition = [
                (object)['id' => 1, 'name' => "Good"],
                (object)['id' => 2, 'name' => "Grey"],
                (object)['id' => 3, 'name' => "Bad"],
            ];
            $status_usage = [
                (object)['id' => 1, 'name' => "In Used"],
                (object)['id' => 2, 'name' => "In Stock"],
                (object)['id' => 3, 'name' => "Replacement"],
            ];
            $headers = ['Authorization' => $header];
            $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                    'headers'  => $headers
                ]);
            $cgx_companies = json_decode((string) $response->getBody(), true)['data']['companies'];
            $companies = [];
            $company['id'] = 0;
            $company['name'] = "None";
            $companies[] = $company;
            foreach($cgx_companies as $cgx_company){
                $company['id'] = $cgx_company['company_id'];
                $company['name'] = $cgx_company['company_name'];
                $companies[] = $company;
            }
            $data = (object)['models' => $models, 'assets' => $assets, 'vendors' => $vendors, 'manufacturers' => $manufacturers, 'status_condition' => $status_condition, 'status_usage' => $status_usage, 'companies' => $companies];
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $data]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getInventories(Request $request)
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
            $assets = Asset::select('id','name')->get();
            $models = ModelInventory::select('id','name', 'asset_id')->get();
            $inventories = Inventory::select('id', 'model_id', 'status_condition', 'status_usage')->get();
            foreach($inventories as $inventory){
                $model = $models->where('id', $inventory->model_id)->first();
                if($model === null){
                    $inventory->model_name = "Model Tidak Ditemukan";
                    $inventory->asset_name = "Model Tidak Ditemukan";
                } else {
                    $inventory->model_name = $model->name;
                    $asset = $assets->where('id', $model->asset_id)->first();
                    if($asset === null) $inventory->asset_name = "Aset Tidak Ditemukan";
                    else $inventory->asset_name = $asset->name;
                } 
               
            }
            if($inventories->isEmpty()) return response()->json(["success" => true, "message" => "Data Inventory Belum Terisi"]);
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
            $model = ModelInventory::find($inventory->model_id);
            if($model === null){
                $inventory->model_name = "Model Tidak Ditemukan";
                $inventory->asset_name = "Model Tidak Ditemukan";
            } else {
                $asset = Asset::find($model->asset_id);
                $inventory->asset_name = "Asset Tidak Ditemukan";
                $inventory->model_name = $model->name;
            }
            $all_inventory_values = InventoryValue::get();
            $model_inventory_columns = ModelInventoryColumn::get();
            $temp_values = $all_inventory_values->where('inventory_id',$id);
            $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                'headers'  => $headers
            ]);
            $companies = json_decode((string) $response->getBody(), true)['data']['companies'];
            $not_exist = true;
            foreach($companies as $company){
                if($company['company_id'] === $inventory->location){
                    $inventory->location_name = $company['company_name'];
                    $not_exist = false;
                    break;
                } 
            }
            if($not_exist) $inventory->location_name = "Perusahaan Tidak Ditemukan";
            $inventory_values = [];
            foreach($temp_values as $temp_value){
                $model_inventory_column = $model_inventory_columns->where('id', $temp_value->model_inventory_column_id)->first();
                // return [$model_inventory_column, $temp_value->model_inventory_column_id];
                if($model_inventory_column === null){
                    $temp_value->name = "Inventory Column Name not Found";
                    $temp_value->data_type = "Inventory Column Name not Found";
                    $temp_value->required = "Inventory Column Name not Found";
                    array_push($inventory_values, $temp_value);
                    continue;
                    // return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory]);
                } 
                $temp_value->name = $model_inventory_column->name;
                $temp_value->data_type = $model_inventory_column->data_type;
                $temp_value->required = $model_inventory_column->required;
                array_push($inventory_values, $temp_value);
            }
            $inventory->additional_attributes = $inventory_values;
            $pivots = InventoryInventoryPivot::get();
            $core_pivots = $pivots->where('parent_id', $inventory->id);
            if(count($pivots)){
                $all_inventories = Inventory::get()->toArray();
                $models = ModelInventory::get()->toArray();
                $assets = Asset::get()->toArray();
                $pivots = $pivots->toArray();
                $inventory_parts = [];
                if(count($core_pivots)){
                    foreach($core_pivots as $pivot){
                        $inventory_parts[] = $this->getInventoryChildren($pivot, $pivots, $all_inventories, $models, $assets);
                    }
                }
                $inventory->inventory_parts = $inventory_parts;
            } else {
                $inventory->inventory_parts = [];
            }
            // return $data;
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory ]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getInventoryReplacements(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("CONTRACTS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        try{
            // $model = ModelInventory::withTrashed()->find($id);
            // if($model === null)return response()->json(["success" => false, "message" => "Model Tidak Ditemukan"]);
            $inventories = Inventory::get();
            $models = ModelInventory::get();
            foreach($inventories as $inventory){
                $model = $models->find($inventory->model_id);
                if($model === null) $inventory->asset_id = null;
                else $inventory->asset_id = $model->asset_id;
            }
            $inventory_replacements = $inventories->where('asset_id', $id)->where('status_usage', 2);
            $datas = [];
            if(count($inventory_replacements)){
                foreach($inventory_replacements as $inventory_replacement){
                    $datas[] = $inventory_replacement;
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $datas ]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
    
    public function checkMigIdInventory($inventories, $inventory_part){
        foreach($inventories as $inventory){
            if($inventory['mig_id'] === $inventory_part['mig_id']){
                return ["success" => false, "mig_id" => $inventory_part['mig_id']];
            }
        }
        return ["success" => true];
    }

    public function addInventory(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        
        $mig_id = $request->get('mig_id');
        $inventories = Inventory::select('id', 'inventory_name', 'mig_id')->get();
        $check_inventory = $inventories->where('mig_id', $mig_id)->first();
        if($check_inventory) return response()->json(["success" => false, "message" => "MIG ID ".$mig_id." Sudah Terdaftar"], 400);
        $inventory_parts = $request->get('inventory_parts',[]);
        if(count($inventory_parts)){
            $inventories = $inventories->toArray();
            foreach($inventory_parts as $inventory_part){
                $result = $this->checkMigIdInventory($inventories, $inventory_part);
                if(!$result['success']){
                    return response()->json(["success" => false, "message" => "MIG ID ".$result['mig_id']." Sudah Terdaftar"], 400);
                }
            }
        }
        $inventory = new Inventory;
        $inventory->model_id = $request->get('model_id');
        $inventory->vendor_id = $request->get('vendor_id', null);
        $inventory->inventory_name = $request->get('inventory_name');
        $inventory->status_condition = $request->get('status_condition');
        $inventory->status_usage = $request->get('status_condition');
        $inventory->location = $request->get('location', null);
        $inventory->is_exist = $request->get('is_exist', null);
        $inventory->deskripsi = $request->get('deskripsi', null);
        $inventory->manufacturer_id = $request->get('manufacturer_id', null);
        $inventory->mig_id = $mig_id;
        $inventory->serial_number = $request->get('serial_number', null);
        $inventory_values = $request->get('inventory_values',[]);
        $notes = $request->get('notes', null);
        try{
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();

            foreach($inventory_values as $inventory_value){
                $model = new InventoryValue;
                $model->inventory_id = $inventory->id;
                $model->model_inventory_column_id = $inventory_value['model_inventory_column_id'];
                $model->value = $inventory_value['value'];
                $model->save();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->save();
                
            }
            if(count($inventory_parts)){
                foreach($inventory_parts as $inventory_part){
                    $this->saveInventoryChild($inventory_part, $inventory->location, $inventory->id, $check['id']);
                }
            }
            return response()->json(["success" => true, "message" => "Inventory Berhasil Ditambah", "id" => $inventory->id]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addInventoryStock(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        
        $mig_id = $request->get('mig_id');
        $check_inventory = Inventory::where('mig_id', $mig_id)->first();
        if($check_inventory) return response()->json(["success" => false, "message" => "MIG ID Sudah Terdaftar"], 400);
        $inventory = new Inventory;
        $inventory->model_id = $request->get('model_id');
        $inventory->vendor_id = $request->get('vendor_id', null);
        $inventory->inventory_name = $request->get('inventory_name');
        $inventory->status_condition = $request->get('status_condition');
        $inventory->status_usage = 2;
        $inventory->location = $request->get('location', null);
        $inventory->is_exist = $request->get('is_exist', null);
        $inventory->deskripsi = $request->get('deskripsi', null);
        $inventory->manufacturer_id = $request->get('manufacturer_id', null);
        $inventory->mig_id = $mig_id;
        $inventory->serial_number = $request->get('serial_number', null);
        $inventory_values = $request->get('inventory_values',[]);
        $notes = $request->get('notes', null);
        try{
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            foreach($inventory_values as $inventory_value){
                $model = new InventoryValue;
                $model->inventory_id = $inventory->id;
                $model->model_inventory_column_id = $inventory_value['model_inventory_column_id'];
                $model->value = $inventory_value['value'];
                $model->save();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->save();
            }
            return response()->json(["success" => true, "message" => "Inventory Berhasil Ditambah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateInventory(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        
        $id = $request->get('id', null);
        $mig_id = $request->get('mig_id');
        $notes = $request->get('notes', null);
        $check_inventory = Inventory::where('mig_id', $mig_id)->first();
        if($check_inventory && $check_inventory->id !== $id) return response()->json(["success" => false, "message" => "MIG ID Sudah Terdaftar"], 400);
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $inventory->vendor_id = $request->get('vendor_id', null);
            $inventory->inventory_name = $request->get('inventory_name');
            $inventory->status_condition = $inventory->status_condition;
            $inventory->status_usage = $inventory->status_usage;
            $inventory->location = $request->get('location', null);
            $inventory->is_exist = $request->get('is_exist', null);
            $inventory->deskripsi = $request->get('deskripsi', null);
            $inventory->manufacturer_id = $request->get('manufacturer_id', null);
            $inventory->mig_id = $mig_id;
            $inventory->serial_number = $request->get('serial_number', null);
            $inventory->save();
            $last_activity = Activity::all()->last();
            if($last_activity->subject_id === $id){
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = $notes;
                $last_activity->save();
            }
            
            $new_inventory_values = $request->get('inventory_values',[]);
            $inventory_values = InventoryValue::get();

            foreach($new_inventory_values as $inventory_value){
                $new_value = $inventory_values->where('id', $inventory_value['id'])->first();
                if($new_value === null) return response()->json(["success" => false, "message" => "Id Inventory Value Tidak Ditemukan", "error_id" => $inventory_value['id']], 400);
                if($new_value->inventory_id !== $id) return response()->json(["success" => false, "message" => "Id Inventory Value Bukan Milik Inventory Ini", "error_id" => $inventory_value['id']], 400);
            }
            foreach($new_inventory_values as $inventory_value){
                $new_value = $inventory_values->where('id', $inventory_value['id'])->first();
                $new_value->value = $inventory_value['value'];
                $new_value->save();
                $check_activity = Activity::all()->last();;
                if (array_key_exists('inventory_id', $check_activity->properties['attributes'])) {
                    if($check_activity->properties['attributes']['inventory_id'] === $id) {
                        $check_activity->causer_id = $check['id'];
                        $check_activity->save();
                    }
                }
            }

            return response()->json(["success" => true, "message" => "Inventory Berhasil Diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }    

    public function setStatusInventoryPartReplacements($pivot, $login_id, $status){
        $pivots = InventoryInventoryPivot::get();
        $inventory = Inventory::find($pivot['child_id']);
        $inventory->status_usage = $status;
        $inventory->save();
        $last_activity = Activity::all()->last();
        $last_activity->causer_id = $login_id;
        $last_activity->save();
        $pivot_children = $pivots->where('parent_id', $pivot['child_id']);
        if(count($pivot_children)){
            foreach($pivot_children as $pivot_child){
                $this->setStatusInventoryPartReplacements($pivot_child, $login_id, $status);
            }
        }
    }

    public function replaceInventoryPart(Request $request){
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        
        $id = $request->get('id', null);
        $replacement_id = $request->get('replacement_id', null);
        $notes = $request->get('notes', null);
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Id Inventori yang akan Diganti Tidak Ditemukan"], 400);
            $pivot_old_inventory = InventoryInventoryPivot::where('child_id', $id)->first();
            
            $inventory_replacement = Inventory::find($replacement_id);
            if($inventory_replacement === false) return response()->json(["success" => false, "message" => "Id Inventori Pengganti Tidak Ditemukan"], 400);
            // if($inventory_replacement->status_usage === 1) return response()->json(["success" => false, "message" => "Inventori Sedang Digunakan"], 400);

            if($inventory_replacement->model_id !== $inventory->model_id) return response()->json(["success" => false, "message" => "Model Kedua Inventori Tidak Sama"], 400);
            $pivots = InventoryInventoryPivot::get();
            $temp_status_usage = $inventory->status_usage;
            $inventory->status_usage = $inventory_replacement->status_usage;
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            $pivot_children = $pivots->where('parent_id', $id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->setStatusInventoryPartReplacements($pivot_child, $check['id'], $inventory->status_usage);
                }
            }

            $inventory_replacement->status_usage = $temp_status_usage;
            $inventory_replacement->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = "Replacement of inventory with id ".$id;
            $last_activity->save();
            $pivot_old_replacement = InventoryInventoryPivot::where('child_id', $replacement_id)->first();
            if($pivot_old_replacement === null){
                $remove_old_pivot = $pivots->where('child_id', $id)->first();
                $remove_old_pivot->delete();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = $notes;
                $last_activity->save();
                $new_replacement_pivot = new InventoryInventoryPivot;
                $new_replacement_pivot->parent_id = $pivot_old_inventory->parent_id;
                $new_replacement_pivot->child_id = $replacement_id;
                $new_replacement_pivot->save();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = "Replacement of inventory with id ".$id;
                $last_activity->save();
            } else {
                $parent_old_inventory = $pivot_old_inventory->parent_id;
                $pivot_old_inventory->parent_id = $pivot_old_replacement->parent_id;
                $pivot_old_inventory->save();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = $notes;
                $last_activity->save();

                $pivot_old_replacement->parent_id = $parent_old_inventory;
                $pivot_old_replacement->save();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = "Replacement of inventory with id ".$id;
                $last_activity->save();
                
            }
            
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->save();

            $pivot_children = $pivots->where('parent_id', $replacement_id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->setStatusInventoryPartReplacements($pivot_child, $check['id'], $inventory_replacement->status_usage);
                }
            }
            return response()->json(["success" => true, "message" => "Berhasil Melakukan Replacement Part Inventory"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function checkParent($id, $core_id, $pivots){
        foreach($pivots as $pivot){
            if($pivot['child_id'] === $id){
                if($pivot['parent_id'] === $core_id) return true;
                else return $this->checkParent($pivot['parent_id'], $core_id, $pivots);
            }
        }
        return false;
    }

    public function removeChildInventoryPart($pivot, $login_id){
        $pivots = InventoryInventoryPivot::get();
        $inventory = Inventory::find($pivot['child_id']);
        $inventory->status_usage = 2;
        $inventory->save();
        $last_activity = Activity::all()->last();
        $last_activity->causer_id = $login_id;
        $last_activity->save();
        $pivot_children = $pivots->where('parent_id', $pivot['child_id']);
        if(count($pivot_children)){
            foreach($pivot_children as $pivot_child){
                $this->removeChildInventoryPart($pivot_child, $login_id);
                // $pivot_child->delete();
                // $last_activity = Activity::all()->last();
                // $last_activity->causer_id = $check['id'];
                // $last_activity->save();
            }
        }
    }

    public function removeInventoryPart(Request $request){
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        $id = $request->get('id', null);
        $notes = $request->get('notes', null);
        try{
            $inventory_part_id = $request->get('inventory_part_id', null);
            $pivots = InventoryInventoryPivot::get();
            $array_pivots = $pivots->toArray();
            $check_parent = $this->checkParent($inventory_part_id, $id, $array_pivots);
            if($check_parent === false) return response()->json(["success" => false, "message" => "Id Part Tidak Termasuk dari Part yang Dimiliki Inventory Ini", "error_id" => $inventory_part_id], 400);

            $inventory = Inventory::find($inventory_part_id);
            $inventory->status_usage = 2;
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            $remove_pivot = $pivots->where('child_id', $inventory_part_id)->first();
            $remove_pivot->delete();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            $pivot_children = $pivots->where('parent_id', $inventory_part_id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->removeChildInventoryPart($pivot_child, $check['id']);
                    // $pivot_child->delete();
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $check['id'];
                    // $last_activity->save();
                }
            }
            return response()->json(["success" => true, "message" => "Berhasil Menghapus Part Inventory"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
    
    public function checkUsed($id){
        $pivot_is_exist = InventoryInventoryPivot::where('child_id', $id)->first();
        if($pivot_is_exist === null) return ["exist" => false, "id" => 0];
        return ["exist" => true, "id" => $pivot_is_exist->parent_id];
    }

    public function addChildInventoryPart($pivot, $login_id){
        $pivots = InventoryInventoryPivot::get();
        $inventory = Inventory::find($pivot['child_id']);
        $inventory->status_usage = 1;
        $inventory->save();
        $last_activity = Activity::all()->last();
        $last_activity->causer_id = $login_id;
        $last_activity->save();
        $pivot_children = $pivots->where('parent_id', $pivot['child_id']);
        if(count($pivot_children)){
            foreach($pivot_children as $pivot_child){
                $this->addChildInventoryPart($pivot_child, $login_id);
            }
        }
    }

    public function addInventoryParts(Request $request){
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        $id = $request->get('id', null);
        $notes = $request->get('notes', null);
        $inventory_part_ids = $request->get('inventory_part_ids', []);
        try{
            if(count($inventory_part_ids)){
                foreach($inventory_part_ids as $inventory_part_id){
                    $check_used = $this->checkUsed($inventory_part_id);
                    if($check_used['exist']) return response()->json(["success" => false, "message" => "Part Id ".$inventory_part_id." sedang digunakan oleh Id ".$check_used['id']], 400);
                }
                foreach($inventory_part_ids as $inventory_part_id){
                    $inventory = Inventory::find($inventory_part_id);
                    if($inventory === null)return response()->json(["success" => false, "message" => "Id Inventory Tidak Terdaftar"], 400);
                    if($inventory->status_usage === 1)return response()->json(["success" => false, "message" => "Inventory Sedang Digunakan"], 400);
                    $inventory->status_usage = 1;
                    $inventory->save();
                    $last_activity = Activity::all()->last();
                    $last_activity->causer_id = $check['id'];
                    $last_activity->causer_type = $notes;
                    $last_activity->save();
                    $pivot = new InventoryInventoryPivot;
                    $pivot->parent_id = $id;
                    $pivot->child_id = $inventory_part_id;
                    $pivot->save();
                    $last_activity = Activity::all()->last();
                    $last_activity->causer_id = $check['id'];
                    $last_activity->causer_type = $notes;
                    $last_activity->save();

                    $pivots = InventoryInventoryPivot::get();
                    $pivot_children = $pivots->where('parent_id', $inventory_part_id);
                    if(count($pivot_children)){
                        foreach($pivot_children as $pivot_child){
                            $this->addChildInventoryPart($pivot_child, $check['id']);
                        }
                    }
                }
                return response()->json(["success" => true, "message" => "Berhasil Menambah Part Inventory"]);
            } else {
                return response()->json(["success" => false, "message" => "Id Part yang Ingin Ditambahkan Kosong"]);
            }
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteInventory(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        
        $id = $request->get('id', null);
        $notes = $request->get('notes', null);
        $inventory = Inventory::find($id);
        if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            // DB::table('inventories')->where('id', $inventory->id)->update(array('vendor_id' => $log_user_id));
            $inventory->delete();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            $inventory_values = InventoryValue::where('inventory_id', $id)->get();
            foreach($inventory_values as $inventory_value){
                $inventory_value->delete();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->save();
            }
            $pivots = InventoryInventoryPivot::get();
            $pivot_children = $pivots->where('parent_id', $id);
            $inventories = Inventory::get();
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $pivot_child->delete();
                    $last_activity = Activity::all()->last();
                    $last_activity->causer_id = $check['id'];
                    $last_activity->save();
                    $inventory = $inventories->where('id', $pivot_child->child_id)->first();
                    $inventory->status_usage = 2;
                    $inventory->save();
                    $last_activity = Activity::all()->last();
                    $last_activity->causer_id = $check['id'];
                    $last_activity->save();
                    $this->removeChildInventoryPart($pivot_child, $check['id']);
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // Adding Notes

    public function addInventoryNotes(Request $request){
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $notes = $request->get('notes', null);
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Inventory Tidak Ditemukan"]);
            activity()->log('note');
            $last_activity = Activity::all()->last();
            $last_activity->log_name = "Inventory";
            $last_activity->subject_type = "App\Inventory";
            $last_activity->subject_id = $id;
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            
            return response()->json(["success" => true, "message" => "Berhasil Mebuat Note Inventory"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // Change Status Inventory

    public function changeStatusCondition(Request $request){
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $notes = $request->get('notes', null);
            $status_condition = $request->get('status_condition');
            if($status_condition < 1 || $status_condition > 3){
                return response()->json(["success" => false, "message" => "Status Usage Tidak Tepat"], 400);
            }
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Inventory Tidak Ditemukan"]);
            $inventory->status_condition = $status_condition;
            $inventory->save();
            $last_activity = Activity::all()->last();
            if($last_activity->subject_id === $id){
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = $notes;
                $last_activity->save();
            }
            return response()->json(["success" => true, "message" => "Status Kondisi Inventory Berhasil Diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function changeStatusUsage(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $status_usage = $request->get('status_usage', null);
            if($status_usage < 1 || $status_usage > 3){
                return response()->json(["success" => false, "message" => "Status Usage Tidak Tepat"], 400);
            }
            $id = $request->get('id', null);
            $notes = $request->get('notes', null);
            $relationship_type_id = $request->get('relationship_type_id', null);
            $connected_id = $request->get('connected_id', null);
            $detail_connected_id = $request->get('detail_connected_id', null);
            if($status_usage === 1){
                if($relationship_type_id === null) return response()->json(["success" => false, "message" => "Relationship Type Belum Terisi"], 400);
                if($relationship_type_id < 1 || $relationship_type_id > 3) return response()->json(["success" => false, "message" => "Relationship Type Id Tidak Tepat"], 400);
                if($connected_id === null) return response()->json(["success" => false, "message" => "Connected Id Belum Terisi"], 400);
            }
            
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Inventory Tidak Ditemukan"]);
            $model = ModelInventory::find($inventory->model_id);
            if($model === null) return response()->json(["success" => false, "message" => "Tipe Model pada Inventory Tidak Ditemukan"]);
            $asset = Asset::find($model->asset_id);
            if($asset === null) return response()->json(["success" => false, "message" => "Tipe Aset pada Tipe Model pada Inventory Tidak Ditemukan"]);
            $inventory->status_usage = $status_usage;
            $inventory->save();
            $last_activity = Activity::all()->last();
            if($last_activity->subject_id === $id){
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = $notes;
                $last_activity->save();
            }
            if($status_usage !== 1){
                //Delete Relationship except Inventory type (4)
                $relationship_inventories = RelationshipInventory::where('subject_id', $inventory->id)->where('type_id', '<>', 4)->get();
                if(count($relationship_inventories)){
                    foreach($relationship_inventories as $relationship_inventory){
                        $relationship_inventory->delete();
                        $last_activity = Activity::all()->last();
                        $last_activity->causer_id = $check['id'];
                        $last_activity->causer_type = "Ubah Status Pemakaian";
                        $last_activity->save();
                    }
                }
                return response()->json(["success" => true, "message" => "Status Pemakaian Inventory Berhasil Diubah"]);
            }

            $relationship = Relationship::where('inverse_relationship_type', "Digunakan Oleh")->first();
            if($relationship === null){
                $relationship = new Relationship;
                $relationship->relationship_type = "Menggunakan";
                $relationship->inverse_relationship_type = "Digunakan Oleh";
                $relationship->description = null;
                $relationship->save();
            }
            $relationship_asset = RelationshipAsset::where('subject_id', $asset->id)->where('type_id', $relationship_type_id)->where('connected_id', null)->first();
            if($relationship_asset === null){
                $relationship_asset = new RelationshipAsset;
                $relationship_asset->subject_id = $asset->id;
                $relationship_asset->relationship_id = $relationship->id;
                $relationship_asset->is_inverse = false;
                $relationship_asset->type_id = $relationship_type_id;
                $relationship_asset->connected_id = null;
                $relationship_asset->save();
            }
            $relationship_inventory = new RelationshipInventory;
            $relationship_inventory->relationship_asset_id = $relationship_asset->id;
            $relationship_inventory->subject_id = $inventory->id;
            $relationship_inventory->connected_id = $connected_id;
            $relationship_inventory->type_id = $relationship_type_id;
            $relationship_inventory->is_inverse = false;
            if($relationship_type_id === 3){
                $relationship_inventory->detail_connected_id = $detail_connected_id;
            }
            $relationship_inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            
            return response()->json(["success" => true, "message" => "Status Pemakaian Inventory Berhasil Diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getChildren($datas, $id_parent){
        $new_data = [];
        foreach($datas as $data){
            if (array_key_exists('members', $data)){
                    $temp = (object)[
                        'id' => $data['company_id'],
                        'title' => $data['company_name'],
                        'key' => $data['company_id'],
                        'value' => $data['company_id'],
                        'id_parent' => $id_parent,
                        'children' => $this->getChildren($data['members'], $data['company_id'])
                    ];
                } else {
                    $temp = (object)[
                        'id' => $data['company_id'],
                        'title' => $data['company_name'],
                        'key' => $data['company_id'],
                        'value' => $data['company_id'],
                        'id_parent' => $id_parent
                    ];
                }
                $new_data[] = $temp;
        }
        return $new_data;
    }

    public function getChangeStatusUsageDetailList(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = (int)$request->get('id');
            if($id < 1) return response()->json(["success" => false, "message" => "Tipe Id Tidak Tepat"], 400);
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            if($id === 1 || $id === 2){     
                $role_checker = $id ;       
                $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                        'headers'  => $headers
                ]);
                $response = json_decode((string) $response->getBody(), true);
                if(array_key_exists('error', $response)) {
                    $users[] = "Error API Server C**";
                } else {
                    foreach($response['data']['accounts'] as $user){
                        if($user['role'] === $role_checker){
                            $users[] = ['id' => $user['user_id'], 'name' => $user['fullname']];
                        }
                    }
                } 
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $users]);
            } else if($id === 3){
                $response = $this->client->request('GET', '/account/v1/company-hierarchy', [
                    'headers'  => $headers
                ]);
                $response = json_decode((string) $response->getBody(), true);
                if(array_key_exists('error', $response)) {
                    return response()->json(["success" => false, "message" => (object)[
                        "errorInfo" => [
                            "status" => 400,
                            "reason" => $response['error']['detail'],
                            "server_code" => $response['error']['code'],
                            "status_detail" => $response['error']['detail']
                        ]
                    ]], 400);
                } else {
                    $client_company_list = [];
                    foreach($response['data']['members'] as $company){
                        if($company['role'] === 2){ 
                            $temp = [];
                            $temp['company_id'] = $company['company_id'];
                            $temp['company_name'] = $company['company_name'];
                            $client_company_list[] = $temp;
                        } 
                    }
                    return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $client_company_list]);
                } 
            } else {
                $response = $this->client->request('GET', '/account/v1/company-hierarchy?company_id='.$id, [
                        'headers'  => $headers
                    ]);
                $data = json_decode((string) $response->getBody(), true)['data'];
                if (array_key_exists('members', $data)){
                    $temp = (object)[
                        'id' => $data['company_id'],
                        'title' => $data['company_name'],
                        'key' => $data['company_id'],
                        'value' => $data['company_id'],
                        'id_parent' => $data['company_id'],
                        'children' => $this->getChildren($data['members'], $data['company_id'])
                    ];
                } else {
                    $temp = (object)[
                        'id' => $data['company_id'],
                        'title' => $data['company_name'],
                        'key' => $data['company_id'],
                        'value' => $data['company_id'],
                        'id_parent' => $data['company_id']
                    ];
                }
                
                $front_end_data = [$temp];
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $front_end_data]);
            }
        } catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            if(json_decode($error_response->getBody())->error->code === 4046){
                return ["success" => false, "message" => "Tidak Memiliki Akses untuk Company Id tersebut."];
            }
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]]);
        }
    }

    // Manufacturer

    public function getManufacturers(Request $request)
    {
        // $check = $this->checkRoute("MANUFACTURERS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $manufacturers = Manufacturer::get();
            // $manufacturers = Manufacturer::withTrashed()->get();
            // $manufacturers = Manufacturer::withTrashed()->find(2);
            if($manufacturers->isEmpty()) return response()->json(["success" => false, "message" => "Manufacturer Belum dibuat"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $manufacturers]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addManufacturer(Request $request)
    {
        // $check = $this->checkRoute("MANUFACTURER_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $manufacturer = new Manufacturer;
        $manufacturer->name = $request->get('name');
        try{
            $manufacturer->save();
            return response()->json(["success" => true, "message" => "Manufacturer berhasil dibuat"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateManufacturer(Request $request)
    {
        // $check = $this->checkRoute("MANUFACTURER_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $manufacturer = Manufacturer::find($id);
        if($manufacturer === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $manufacturer->name = $request->get('name');
        try{
            $manufacturer->save();
            return response()->json(["success" => true, "message" => "Manufacturer berhasil diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteManufacturer(Request $request)
    {
        // $check = $this->checkRoute("MANUFACTURER_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $manufacturer = Manufacturer::find($id);
        if($manufacturer === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $manufacturer->delete();
            return response()->json(["success" => true, "message" => "Manufacturer berhasil dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // Relationship
    // Relationship Type

    public function getRelationships(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $relationships = Relationship::get();
            if($relationships->isEmpty()) return response()->json(["success" => false, "message" => "Relationship Belum dibuat"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationships]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationship(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $relationship = Relationship::find($id);
            if($relationship === null) return response()->json(["success" => false, "message" => "Relationship Tidak Ditemukan"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addRelationship(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIP_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $relationship = new Relationship;
        $relationship->relationship_type = $request->get('relationship_type');
        $relationship->inverse_relationship_type = $request->get('inverse_relationship_type');
        $relationship->description = $request->get('description', null);
        try{
            $relationship->save();
            return response()->json(["success" => true, "message" => "Relationship berhasil dibuat"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateRelationship(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIP_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $relationship = Relationship::find($id);
        if($relationship === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $relationship->relationship_type = $request->get('relationship_type');
        $relationship->inverse_relationship_type = $request->get('inverse_relationship_type');
        $relationship->description = $request->get('description', null);
        try{
            $relationship->save();
            return response()->json(["success" => true, "message" => "Relationship berhasil diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteRelationship(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIP_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $relationship = Relationship::find($id);
        if($relationship === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $asset = RelationshipAsset::where('relationship_id', $relationship->id)->first();
        if($asset) return response()->json(["success" => false, "message" => "Relationship Masih Digunakan Asset"]);
        try{
            $relationship->delete();
            return response()->json(["success" => true, "message" => "Relationship berhasil dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // Relationship Asset

    public function getRelationshipAssets(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $relationship_assets = RelationshipAsset::get();
            if($relationship_assets->isEmpty()) return response()->json(["success" => false, "message" => "Relationship Asset Belum dibuat"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship_assets]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationshipAsset(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $type_id = (int)$request->get('type_id', null);
            $relationship_assets = RelationshipAsset::get();
            $relationships = Relationship::get();
            $assets = Asset::get();
            $relationship_assets_from_inverse = $relationship_assets->where('connected_id', $id)->where('type_id', $type_id);
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            $users = [];
            $companies = [];
            if($type_id === 1 || 2){
                $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                    'headers'  => $headers
                ]);
                $users = json_decode((string) $response->getBody(), true)['data']['accounts'];
            } 
            if($type_id === 3){
                $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                    'headers'  => $headers
                ]);
                $companies = json_decode((string) $response->getBody(), true)['data']['companies'];
            } 
            $data_not_from_invers = [];
            if($type_id === 4){
                $relationship_assets_not_from_inverse = $relationship_assets->where('subject_id', $id);
                $first_type = $relationship_assets_not_from_inverse->where('type_id', 1)->first();
                $second_type = $relationship_assets_not_from_inverse->where('type_id', 2)->first();
                $third_type = $relationship_assets_not_from_inverse->where('type_id', 3)->first();
                if(($first_type !== null || $second_type !== null) && $users === []){
                    $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                        'headers'  => $headers
                    ]);
                    $users = json_decode((string) $response->getBody(), true)['data']['accounts'];
                }
                if($third_type !== null && $companies === []){
                    $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                        'headers'  => $headers
                    ]);
                    $companies = json_decode((string) $response->getBody(), true)['data']['companies'];
                }
                if(count($relationship_assets_not_from_inverse)){
                    foreach($relationship_assets_not_from_inverse as $relationship_asset){
                        $relationship = $relationships->find($relationship_asset->relationship_id);
                        $relationship_subject_detail = $assets->find($relationship_asset->subject_id);
                        if($relationship_subject_detail === null) $relationship_asset->subject_detail_name = "Asset Not Found";
                        else $relationship_asset->subject_detail_name = $relationship_subject_detail->name;
                        $relationship_asset->relationship = $relationship_asset->is_inverse ? $relationship->inverse_relationship_type : $relationship->relationship_type;
                        $relationship_asset->type = $relationship_asset->type_id === 1 ? "Agent" : ($relationship_asset->type_id === 2 ? "Requester" : ($relationship_asset->type_id === 3 ? "Company" : "Asset Type"));
                        if($relationship_asset->connected_id === null){
                            $relationship_asset->connected_detail_name = "Detail ID Kosong";
                        } else {
                            if($relationship_asset->type_id === 1 || $relationship_asset->type_id === 2){
                                $name = "Not Found";
                                $check_id = $relationship_asset->connected_id;
                                foreach($users as $user){
                                    if($user['user_id'] === $check_id){
                                        if(($relationship_asset->type_id === 1 && $user['role'] === 1) || ($relationship_asset->type_id === 2 && $user['role'] === 2)){
                                            $name = $user['fullname'];
                                            break;
                                        }
                                    }
                                }
                                $relationship_asset->connected_detail_name = $name;
                            } else if($relationship_asset->type_id === 3){
                                $name = "Not Found";
                                $check_id = $relationship_asset->connected_id;
                                foreach($companies as $company){
                                    if($company['company_id'] === $check_id){
                                        $name = $company['company_name'];
                                        break;
                                    }
                                }
                                $relationship_asset->connected_detail_name = $name;
                            } else {
                                $asset = $assets->find($relationship_asset->connected_id);
                                if($asset === null) $relationship_asset->connected_detail_name = "Asset Not Found";
                                else $relationship_asset->connected_detail_name = $asset->name;
                            }
                        }
                        $relationship_asset->from_inverse = false;
                        $data_not_from_invers[] = $relationship_asset;
                    }
                }
            }
            $data_from_invers = [];
            if(count($relationship_assets_from_inverse)){
                foreach($relationship_assets_from_inverse as $relationship_asset){
                    $relationship = $relationships->find($relationship_asset->relationship_id);
                    $relationship_subject_detail = $assets->find($relationship_asset->subject_id);
                    if($relationship_subject_detail === null) $relationship_asset->subject_detail_name = "Not Found";
                    else $relationship_asset->subject_detail_name = $relationship_subject_detail->name;
                    $relationship_asset->relationship = $relationship_asset->is_inverse ? $relationship->relationship_type : $relationship->inverse_relationship_type;
                    $relationship_asset->type = $relationship_asset->type_id === 1 ? "Agent" : ($relationship_asset->type_id === 2 ? "Requester" : ($relationship_asset->type_id === 3 ? "Company" : "Asset Type"));
                    if($relationship_asset->connected_id === null){
                        $relationship_asset->connected_detail_name = "Detail ID Kosong";
                    } else {
                        if($relationship_asset->type_id === 1 || $relationship_asset->type_id === 2){
                            $name = "Not Found";
                            $check_id = $relationship_asset->connected_id;
                            if(array_key_exists('error', $response)) {
                                $name = "Error API Server C**";
                            } else {
                                foreach($users as $user){
                                    if($user['user_id'] === $check_id){
                                        if(($relationship_asset->type_id === 1 && $user['role'] === 1) || ($relationship_asset->type_id === 2 && $user['role'] === 2)){
                                            $name = $user['fullname'];
                                            break;
                                        }
                                    }
                                }
                            } 
                            $relationship_asset->connected_detail_name = $name;
                        } else if($relationship_asset->type_id === 3){
                            $name = "Not Found";
                            $check_id = $relationship_asset->connected_id;
                            foreach($companies as $company){
                                if($company['company_id'] === $check_id){
                                    $name = $company['company_name'];
                                    break;
                                }
                            }
                            
                            $relationship_asset->connected_detail_name = $name;
                        } else {
                            $asset = $assets->find($relationship_asset->connected_id);
                            if($asset === null) $relationship_asset->connected_detail_name = "Asset Not Found";
                            else $relationship_asset->connected_detail_name = $asset->name;
                        }
                    }
                    $temp_subject_id = $relationship_asset->subject_id;
                    $temp_subject_detail_name = $relationship_asset->subject_detail_name;
                    $relationship_asset->subject_id = $relationship_asset->connected_id;
                    $relationship_asset->subject_detail_name = $relationship_asset->connected_detail_name;
                    $relationship_asset->connected_id = $temp_subject_id;
                    $relationship_asset->connected_detail_name = $temp_subject_detail_name;

                    $relationship_asset->from_inverse = true;
                    $data_from_invers[] = $relationship_asset;
                }
            }
             
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["from_inverse" => $data_from_invers, "not_from_inverse" => $data_not_from_invers]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationshipAssetRelation(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $relationships = Relationship::get();
            $types = [["id" => 1, "name" => "Agent"], ["id" => 2, "name" => "Requester"], ["id" => 3, "name" => "Company"], ["id" => 4, "name" => "Asset"]];
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["relationships" => $relationships, "types" => $types]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationshipAssetDetailList(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $type = (int)$request->get('type_id');
            if($type < 1 || $type > 4) return response()->json(["success" => false, "message" => "Tipe Id Tidak Tepat"], 400);
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            if($type === 1 || $type === 2){     
                $role_checker = $type ;       
                $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                        'headers'  => $headers
                ]);
                $response = json_decode((string) $response->getBody(), true);
                if(array_key_exists('error', $response)) {
                    $users[] = "Error API Server C**";
                } else {
                    $users[] = ['id' => null, 'name' => "-"];
                    foreach($response['data']['accounts'] as $user){
                        if($user['role'] === $role_checker){
                            $users[] = ['id' => $user['user_id'], 'name' => $user['fullname']];
                        }
                    }
                } 
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $users]);
            } else if($type === 3){
                $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                        'headers'  => $headers
                ]);
                $response = json_decode((string) $response->getBody(), true);
                if(array_key_exists('error', $response)) {
                    $companies[] = "Error API Server C**";
                } else {
                    $companies[] = ['id' => null, 'name' => "-"];
                    foreach($response['data']['companies'] as $company){
                        $companies[] = ['id' => $company['company_id'], 'name' => $company['company_name']];
                    }
                } 
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies]);
            } else {
                $assets = Asset::where('code', 'not like', "%.%")->orderBy('code')->get();
                $temp = (object)[
                        'id' => null,
                        'title' => "-",
                        'key' => "000",
                        'value' => "000",
                        'children' => []
                    ];
                $tree_assets[] = $temp;
                foreach($assets as $asset){
                    $temp = (object)[
                        'id' => $asset->id,
                        'title' => $asset->name,
                        'key' => $asset->code,
                        'value' => $asset->code,
                        'children' => $this->getData($asset->code)
                    ];
                    $tree_assets[] = $temp;
                }
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $tree_assets]);
            }
        } catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]]);
        }
    }

    public function addRelationshipAsset(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIP_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $type_id = $request->get('type_id');
        if($type_id < 1 || $type_id > 4) return response()->json(["success" => false, "message" => "Tipe Id Tidak Tepat"], 400);
        $relationship_asset = new RelationshipAsset;
        $relationship_asset->relationship_id = $request->get('relationship_id');
        $relationship_asset->subject_id = $request->get('subject_id');
        $relationship_asset->is_inverse = $request->get('is_inverse');
        $relationship_asset->type_id = $type_id;
        $relationship_asset->connected_id = $request->get('connected_id', null);
        try{
            $relationship_asset->save();
            
            return response()->json(["success" => true, "message" => "Relationship Asset berhasil dibuat"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateRelationshipAsset(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIP_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $from_inverse = $request->get('from_inverse');
        // $type_id = $request->get('type_id');
        // if($type_id < 1 || $type_id > 4) return response()->json(["success" => false, "message" => "Tipe Id Tidak Tepat"], 400);
        $relationship_asset = RelationshipAsset::find($id);
        if($relationship_asset === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $relationship_asset->relationship_id = $request->get('relationship_id');
        if($from_inverse){
            // $relationship_asset->subject_id = $request->get('connected_id');
            $relationship_asset->is_inverse = !$request->get('is_inverse');
        } else {
            // $relationship_asset->connected_id = $request->get('connected_id');
            $relationship_asset->is_inverse = $request->get('is_inverse');
        } 
        // $relationship_asset->type_id = $type_id;
        try{
            $relationship_asset->save();
            return response()->json(["success" => true, "message" => "Relationship Asset berhasil diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteRelationshipAsset(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIP_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $relationship_asset = RelationshipAsset::find($id);
        if($relationship_asset === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $relationship_inventory = RelationshipInventory::where('relationship_asset_id', $relationship_asset->id)->first();
        if($relationship_inventory)return response()->json(["success" => false, "message" => "Relationship Masih Digunakan Inventory"]);
        try{
            $relationship_asset->delete();
            return response()->json(["success" => true, "message" => "Relationship Asset berhasil dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // Relationship Iventory

    public function getRelationshipInventories(Request $request)
    {
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $relationship_inventories = RelationshipInventory::get();
            if($relationship_inventories->isEmpty()) return response()->json(["success" => false, "message" => "Relationship Inventory Belum dibuat"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship_inventories]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationshipInventory(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $type_id = (int)$request->get('type_id', null);
            $relationship_inventories = RelationshipInventory::get();
            $relationship_assets = RelationshipAsset::get();
            $relationships = Relationship::get();
            $inventories = Inventory::get();
            $relationship_inventories_from_inverse = $relationship_inventories->where('connected_id', $id)->where('type_id', $type_id);
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            $users = [];
            $companies = [];
            if($type_id === 1 || 2){
                $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                    'headers'  => $headers
                ]);
                $users = json_decode((string) $response->getBody(), true)['data']['accounts'];
            } 
            if($type_id === 3){
                $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                    'headers'  => $headers
                ]);
                $companies = json_decode((string) $response->getBody(), true)['data']['companies'];
            } 
            $data_not_from_invers = [];
            if($type_id === 4){
                $relationship_inventories_not_from_inverse = $relationship_inventories->where('subject_id', $id);
                $first_type = $relationship_inventories_not_from_inverse->where('type_id', 1)->first();
                $second_type = $relationship_inventories_not_from_inverse->where('type_id', 2)->first();
                $third_type = $relationship_inventories_not_from_inverse->where('type_id', 3)->first();
                if(($first_type !== null || $second_type !== null) && $users === []){
                    $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                        'headers'  => $headers
                    ]);
                    $users = json_decode((string) $response->getBody(), true)['data']['accounts'];
                }
                if($third_type !== null && $companies === []){
                    $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                        'headers'  => $headers
                    ]);
                    $companies = json_decode((string) $response->getBody(), true)['data']['companies'];
                }
                if(count($relationship_inventories_not_from_inverse)){
                    foreach($relationship_inventories_not_from_inverse as $relationship_inventory){
                        $relationship_asset = $relationship_assets->find($relationship_inventory->relationship_asset_id);
                        if($relationship_asset === null){
                            $relationship_inventory->relationship = "Relationship Asset Not Found";
                        } else {
                            $relationship = $relationships->find($relationship_asset->relationship_id);
                            if($relationship_asset === null){
                                $relationship_inventory->relationship = "Relationship Not Found";
                            } else {
                                $is_inverse_inventory_relationship = $relationship_asset->is_inverse === $relationship_inventory->is_inverse ? false : true;
                                $relationship_inventory->relationship = $is_inverse_inventory_relationship ? $relationship->inverse_relationship_type : $relationship->relationship_type;
                            }
                        }
                        
                        $relationship_inventory->type = $relationship_inventory->type_id === 1 ? "Agent" : ($relationship_inventory->type_id === 2 ? "Requester" : ($relationship_inventory->type_id === 3 ? "Company" : "Inventory Type"));
                        $relationship_subject_detail = $inventories->find($relationship_inventory->subject_id);
                        if($relationship_subject_detail === null) $relationship_inventory->subject_detail_name = "Inventory Not Found";
                        else $relationship_inventory->subject_detail_name = $relationship_subject_detail->inventory_name;
                        if($relationship_inventory->connected_id === null){
                            $relationship_inventory->connected_detail_name = "Detail ID Kosong";
                        } else {
                            if($relationship_inventory->type_id === 1 || $relationship_inventory->type_id === 2){
                                $name = "Not Found";
                                $check_id = $relationship_inventory->connected_id;
                                foreach($users as $user){
                                    if($user['user_id'] === $check_id){
                                        if(($relationship_inventory->type_id === 1 && $user['role'] === 1) || ($relationship_inventory->type_id === 2 && $user['role'] === 2)){
                                            $name = $user['fullname'];
                                            break;
                                        }
                                    }
                                }
                                $relationship_inventory->connected_detail_name = $name;
                            } else if($relationship_inventory->type_id === 3){
                                $name = "Not Found";
                                $check_id = $relationship_inventory->connected_id;
                                foreach($companies as $company){
                                    if($company['company_id'] === $check_id){
                                        $name = $company['company_name'];
                                        break;
                                    }
                                }
                                $relationship_inventory->connected_detail_name = $name;
                            } else {
                                $inventory = $inventories->find($relationship_inventory->connected_id);
                                if($inventory === null) $relationship_inventory->connected_detail_name = "Inventory Not Found";
                                else $relationship_inventory->connected_detail_name = $inventory->inventory_name;
                            }
                        }
                        $relationship_inventory->from_inverse = false;
                        $data_not_from_invers[] = $relationship_inventory;
                    }
                }
            }
            $data_from_invers = [];
            if(count($relationship_inventories_from_inverse)){
                foreach($relationship_inventories_from_inverse as $relationship_inventory){
                    $relationship_asset = $relationship_assets->find($relationship_inventory->relationship_asset_id);
                    if($relationship_asset === null){
                        $relationship_inventory->relationship = "Relationship Asset Not Found";
                    } else {
                        $relationship = $relationships->find($relationship_asset->relationship_id);
                        if($relationship_asset === null){
                            $relationship_inventory->relationship = "Relationship Not Found";
                        } else {
                            $is_inverse_inventory_relationship = $relationship_asset->is_inverse === $relationship_inventory->is_inverse ? true : false;
                            $relationship_inventory->relationship = $is_inverse_inventory_relationship ? $relationship->inverse_relationship_type : $relationship->relationship_type;
                        }
                    }

                    $relationship_inventory->type = $relationship_inventory->type_id === 1 ? "Agent" : ($relationship_inventory->type_id === 2 ? "Requester" : ($relationship_inventory->type_id === 3 ? "Company" : "Inventory Type"));
                    $relationship_subject_detail = $inventories->find($relationship_inventory->subject_id);
                    if($relationship_subject_detail === null) $relationship_inventory->subject_detail_name = "Not Found";
                    else $relationship_inventory->subject_detail_name = $relationship_subject_detail->inventory_name;
                    if($relationship_inventory->connected_id === null){
                        $relationship_inventory->connected_detail_name = "Detail ID Kosong";
                    } else {
                        if($relationship_inventory->type_id === 1 || $relationship_inventory->type_id === 2){
                            $name = "Not Found";
                            $check_id = $relationship_inventory->connected_id;
                            foreach($users as $user){
                                if($user['user_id'] === $check_id){
                                    if(($relationship_inventory->type_id === 1 && $user['role'] === 1) || ($relationship_inventory->type_id === 2 && $user['role'] === 2)){
                                        $name = $user['fullname'];
                                        break;
                                    }
                                }
                            }
                            $relationship_inventory->connected_detail_name = $name;
                        } else if($relationship_inventory->type_id === 3){
                            $name = "Not Found";
                            $check_id = $relationship_inventory->connected_id;
                            foreach($companies as $company){
                                if($company['company_id'] === $check_id){
                                    $name = $company['company_name'];
                                    break;
                                }
                            }
                            $relationship_inventory->connected_detail_name = $name;
                        } else {
                            $inventory = $inventories->find($relationship_inventory->connected_id);
                            if($inventory === null) $relationship_inventory->connected_detail_name = "Inventory Not Found";
                            else $relationship_inventory->connected_detail_name = $inventory->inventory_name;
                        }
                    }
                    $temp_subject_id = $relationship_inventory->subject_id;
                    $temp_subject_detail_name = $relationship_inventory->subject_detail_name;
                    $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                    $relationship_inventory->subject_detail_name = $relationship_inventory->connected_detail_name;
                    $relationship_inventory->connected_id = $temp_subject_id;
                    $relationship_inventory->connected_detail_name = $temp_subject_detail_name;

                    $relationship_inventory->from_inverse = true;
                    $data_from_invers[] = $relationship_inventory;
                }
            }
             
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["from_inverse" => $data_from_invers, "not_from_inverse" => $data_not_from_invers]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationshipInventoryRelation(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $relationship_assets = RelationshipAsset::get();
            if($relationship_assets->isEmpty()) return response()->json(["success" => false, "message" => "Relationship Inventory Belum dibuat"]);
            $relationships = Relationship::get();
            $inventories = Inventory::get();
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                'headers'  => $headers
            ]);
            $users = json_decode((string) $response->getBody(), true)['data']['accounts'];
            $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                'headers'  => $headers
            ]);
            $companies = json_decode((string) $response->getBody(), true)['data']['companies'];
            foreach($relationship_assets as $relationship_asset){
                $relationship = $relationships->find($relationship_asset->relationship_id);
                if($relationship === null) $relationship_asset->relationship_detail_name = "Relationship Not Found";
                else $relationship_asset->relationship_detail_name = $relationship->is_inverse ? $relationship->inverse_relationship_type : $relationship->relationship_type;
                
                $check_id = $relationship_asset->connected_id;
                $inventory = $inventories->find($relationship_asset->subject_id);
                if($inventory === null) $relationship_asset->subject_detail_name = "Inventory Not Found";
                else $relationship_asset->subject_detail_name = $inventory->inventory_name;
                if($relationship_asset->type_id === 1 || $relationship_asset->type_id === 2){
                    $name = "Not Found";
                    foreach($users as $user){
                        if($user['user_id'] === $check_id){
                            if(($relationship_asset->type_id === 1 && $user['role'] === 1) || ($relationship_asset->type_id === 2 && $user['role'] === 2)){
                                $name = $user['fullname'];
                                break;
                            }
                        }
                    }
                    $relationship_asset->connected_detail_name = $name;
                } else if($relationship_asset->type_id === 3){
                    $name = "Not Found";
                    foreach($companies as $company){
                        if($company['company_id'] === $check_id){
                            $name = $company['company_name'];
                            break;
                        }
                    }
                    $relationship_asset->connected_detail_name = $name;
                } else {
                    $inventory = $inventories->find($check_id);
                    if($inventory === null) $relationship_asset->connected_detail_name = "Inventory Not Found";
                    else $relationship_asset->connected_detail_name = $inventory->inventory_name;
                }
                $relationship_asset->detail = $relationship_asset->subject_detail_name . " " . $relationship_asset->relationship_detail_name . " " . $relationship_asset->connected_detail_name;
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship_assets]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRelationshipInventoryDetailList(Request $request)
    {
        $header = $request->header("Authorization");
        // $check = $this->checkRoute("RELATIONSHIPS_GET", $header);
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $relationship_asset_id = (int)$request->get('relationship_asset_id');
            $relationship_asset = RelationshipAsset::find($relationship_asset_id);
            if($relationship_asset === null) return response()->json(["success" => false, "message" => "Relationship Asset Tidak Ditemukan"]);
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            if($relationship_asset->type_id === 1 || $relationship_asset->type_id === 2){     
                $role_checker = $relationship_asset->type_id ;       
                $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                        'headers'  => $headers
                ]);
                $response = json_decode((string) $response->getBody(), true);
                if(array_key_exists('error', $response)) {
                    $users[] = "Error API Server C**";
                } else {
                    $users = [];
                    foreach($response['data']['accounts'] as $user){
                        if($user['role'] === $role_checker){
                            $users[] = ['id' => $user['user_id'], 'name' => $user['fullname']];
                        }
                    }
                } 
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $users]);
            } else if($relationship_asset->type_id === 3){
                $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
                        'headers'  => $headers
                ]);
                $response = json_decode((string) $response->getBody(), true);
                if(array_key_exists('error', $response)) {
                    $companies[] = "Error API Server C**";
                } else {
                    foreach($response['data']['companies'] as $company){
                        $companies[] = ['id' => $company['company_id'], 'name' => $company['company_name']];
                    }
                } 
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies]);
            } else {
                $inventories = Inventory::get();
                if($relationship_asset->connected_id){
                    $models = ModelInventory::get();
                    $assets = Asset::get();
                    foreach($inventories as $inventory){
                        $model = $models->find($inventory->model_id);
                        if($model === null) $inventory->asset_id = null;
                        else $inventory->asset_id = $model->asset_id;
                    }
                    $inventories = $inventories->where('asset_id', $relationship_asset->connected_id);
                    $new_inventories = [];
                    foreach($inventories as $inventory){
                        $new_inventories[] = $inventory; 
                    }
                    return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $new_inventories]);
                } 
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories]);
            }
        } catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]]);
        }
    }

    public function addRelationshipInventories(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        
        $relationship_asset_id = $request->get('relationship_asset_id');
        $relationship_asset = RelationshipAsset::find($relationship_asset_id);
        if($relationship_asset === null) return response()->json(["success" => false, "message" => "Relationship Asset Id Tidak Ditemukan"], 400);
        
        $connected_ids = $request->get('connected_ids', []);
        $subject_id = $request->get('subject_id');
        $is_inverse = $request->get('is_inverse');
        $notes = $request->get('notes', null);
        try{
            foreach($connected_ids as $connected_id){
                $relationship_inventory = new RelationshipInventory;
                $relationship_inventory->relationship_asset_id = $relationship_asset_id;
                $relationship_inventory->type_id = $relationship_asset->type_id;
                $relationship_inventory->subject_id = $subject_id;
                $relationship_inventory->is_inverse = $is_inverse;
                $relationship_inventory->connected_id = $connected_id;
                $relationship_inventory->save();
                $last_activity = Activity::all()->last();
                $last_activity->causer_id = $check['id'];
                $last_activity->causer_type = $notes;
                $last_activity->save();
            }
            return response()->json(["success" => true, "message" => "Relationship Inventory berhasil dibuat"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateRelationshipInventory(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $from_inverse = $request->get('from_inverse');
        $relationship_asset_id = $request->get('relationship_asset_id');
        $notes = $request->get('notes', null);
        
        $relationship_inventory = RelationshipInventory::find($id);
        if($relationship_inventory === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $relationship_asset = RelationshipAsset::find($relationship_asset_id);
        if($relationship_asset === null) return response()->json(["success" => false, "message" => "Relationship Asset Id Tidak Ditemukan"], 400);
        
        $relationship_inventory->relationship_asset_id = $relationship_asset_id;
        $relationship_inventory->type_id = $relationship_asset->type_id;
        if($from_inverse){
            $relationship_inventory->subject_id = $request->get('connected_id');
            $relationship_inventory->is_inverse = !$request->get('is_inverse');
        } else {
            $relationship_inventory->connected_id = $request->get('connected_id');
            $relationship_inventory->is_inverse = $request->get('is_inverse');
        } 
        try{
            $relationship_inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            return response()->json(["success" => true, "message" => "Relationship Inventory berhasil diubah"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteRelationshipInventory(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $relationship_inventory = RelationshipInventory::find($id);
        if($relationship_inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $relationship_inventory->delete();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->causer_type = $notes;
            $last_activity->save();
            return response()->json(["success" => true, "message" => "Relationship Inventory berhasil dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}