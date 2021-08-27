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
use App\AssetRelatedPivot;
use App\ModelInventory;
use App\ModelInventoryColumn;
use App\ModelInventoryValue;
use App\ModelModelPivot;
use App\Inventory;
use App\InventoryValue;
use App\InventoryColumn;
use App\InventoryInventoryPivot;
use App\Manufacturer;
use App\Vendor;
use DB;
use Exception;

class AssetModelInventoryController extends Controller
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

            $delete_column_ids = $request->get('delete_column_ids', []);
            $asset_columns = AssetColumn::where('asset_id', $id)->get();
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
                    $parent_name = $assets->where('code', $parent_model)->first()->name;
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
            $model = ModelInventory::find($id);
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
            $asset_columns = AssetColumn::where('asset_id', $model->asset_id)->get();
            $model->manufacturer = Manufacturer::withTrashed()->find($model->manufacturer_id);
            $model->asset_columns = $asset_columns;
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
            $model_columns = ModelInventoryColumn::where('model_id', $id)->get();
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


    // // Child type column responses
    // public function getDataInventoryColumnTurunan($parent_id){
    //     $assets = Asset::select('id','name')->get();
    //     $inventory_columns = InventoryColumn::get();
    //     $related_asset_pivots = AssetRelatedPivot::get();
    //     $related_asset_pivots_inti = $related_asset_pivots->where('parent_asset_id', $parent_id);
    //     $inventory_columns_related = [];
    //     foreach($related_asset_pivots_inti as $asset_pivot){
    //         $asset_id = $asset_pivot->asset_id;
    //         $asset_pivot_turunan = $related_asset_pivots->where('parent_asset_id', $asset_id);
    //         $inventory_columns_in_loop = $inventory_columns->where('asset_id', $asset_id);
    //         $temp_inventory_columns = [];
    //         foreach($inventory_columns_in_loop as $inventory_column){
    //             $temp_inventory_columns[] = $inventory_column;
    //         }
    //         if($asset_pivot_turunan->count()){
    //             $temp = (object)[
    //                 "asset_type" => $assets->where('id', $asset_id)->first()->name,
    //                 "asset_id" => $asset_id,
    //                 "quantity_needed" => $asset_pivot->quantity,
    //                 "inventory_columns" => $temp_inventory_columns,
    //                 "child" => $this->getDataInventoryColumnTurunan($asset_id)
    //             ];
    //         } else {
    //             $temp = (object)[
    //                 "asset_type" => $assets->where('id', $asset_id)->first()->name,
    //                 "asset_id" => $asset_id,
    //                 "quantity_needed" => $asset_pivot->quantity,
    //                 "inventory_columns" => $temp_inventory_columns
    //             ];
    //         }
    //         $inventory_columns_related[] = $temp;
    //     }
    //     return $inventory_columns_related;
    // }

    // //Inventory Column
    // public function getInventoryColumns(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }

    //     try{
    //         $id = $request->get('id', null);
    //         if(Asset::find($id) === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
    //         $inventory_columns = InventoryColumn::get();
    //         $inventory_columns_turunan = $inventory_columns->where('asset_id', $id);
    //         $temp_inventory_columns = [];
    //         foreach($inventory_columns_turunan as $inventory_column){
    //             $temp_inventory_columns[] = $inventory_column;
    //         }
    //         $inventory_columns_turunan = $temp_inventory_columns;
    //         $related_asset_pivots = AssetRelatedPivot::get();
    //         $vendors = Vendor::select('id','name')->get();
    //         $assets = Asset::select('id','name')->get();
    //         $related_asset_pivots_inti = $related_asset_pivots->where('parent_asset_id', $id);
    //         $inventory_columns_related = [];
    //         foreach($related_asset_pivots_inti as $asset_pivot){
    //             $asset_id = $asset_pivot->asset_id;
    //             $asset_pivot_turunan = $related_asset_pivots->where('parent_asset_id', $asset_id);
    //             $inventory_columns_in_loop = $inventory_columns->where('asset_id', $asset_id);
    //             $temp_inventory_columns = [];
    //             foreach($inventory_columns_in_loop as $inventory_column){
    //                 $temp_inventory_columns[] = $inventory_column;
    //             }
    //             if($asset_pivot_turunan->count()){
    //                 $temp = (object)[
    //                     "asset_type" => $assets->where('id', $asset_id)->first()->name,
    //                     "asset_id" => $asset_id,
    //                     "quantity_needed" => $asset_pivot->quantity,
    //                     "inventory_columns" => $temp_inventory_columns,
    //                     "child" => $this->getDataInventoryColumnTurunan($asset_id)
    //                 ];
    //             } else {
    //                 $temp = (object)[
    //                     "asset_type" => $assets->where('id', $asset_id)->first()->name,
    //                     "asset_id" => $asset_id,
    //                     "quantity_needed" => $asset_pivot->quantity,
    //                     "inventory_columns" => $temp_inventory_columns
    //                 ];
    //             }
    //             $inventory_columns_related[] = $temp;
    //         }       
    //         return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["inventory_columns_turunan" => $inventory_columns_turunan, "inventory_columns_related" => $inventory_columns_related, "vendors" => $vendors, "assets" => $assets]]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }


    // //One Line Related Column Responses
    // public function getDataInventoryColumnTurunan($parent_id, $inventory_columns_related){
    //     $assets = Asset::select('id','name')->get();
    //     $inventory_columns = InventoryColumn::get();
    //     $related_asset_pivots = AssetRelatedPivot::get();
    //     $related_asset_pivots_inti = $related_asset_pivots->where('parent_asset_id', $parent_id);
    //     foreach($related_asset_pivots_inti as $asset_pivot){
    //         $asset_id = $asset_pivot->asset_id;
    //         $asset_pivot_turunan = $related_asset_pivots->where('parent_asset_id', $asset_id);
    //         $inventory_columns_in_loop = $inventory_columns->where('asset_id', $asset_id);
    //         $temp_inventory_columns = [];
    //         foreach($inventory_columns_in_loop as $inventory_column){
    //             $temp_inventory_columns[] = $inventory_column;
    //         }
    //         if($asset_pivot_turunan->count()){
    //             $inventory_columns_related = $this->getDataInventoryColumnTurunan($asset_id, $inventory_columns_related);
    //         } 
    //         $temp = (object)[
    //             "asset_type" => $assets->where('id', $asset_id)->first()->name,
    //             "asset_id" => $asset_id,
    //             "quantity_needed" => $asset_pivot->quantity,
    //             "inventory_columns" => $temp_inventory_columns
    //         ];
    //         $inventory_columns_related[] = $temp;
    //     }
    //     return $inventory_columns_related;
    // }

    // //Inventory Column
    // public function getInventoryColumns(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }

    //     try{
    //         $id = $request->get('id', null);
    //         if(Asset::find($id) === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
    //         $inventory_columns = InventoryColumn::get();
    //         $inventory_columns_turunan = $inventory_columns->where('asset_id', $id);
    //         $temp_inventory_columns = [];
    //         foreach($inventory_columns_turunan as $inventory_column){
    //             $temp_inventory_columns[] = $inventory_column;
    //         }
    //         $inventory_columns_turunan = $temp_inventory_columns;
    //         $related_asset_pivots = AssetRelatedPivot::get();
    //         $vendors = Vendor::select('id','name')->get();
    //         $assets = Asset::select('id','name')->get();
    //         $related_asset_pivots_inti = $related_asset_pivots->where('parent_asset_id', $id);
    //         $inventory_columns_related = [];
    //         foreach($related_asset_pivots_inti as $asset_pivot){
    //             $asset_id = $asset_pivot->asset_id;
    //             $asset_pivot_turunan = $related_asset_pivots->where('parent_asset_id', $asset_id);
    //             $inventory_columns_in_loop = $inventory_columns->where('asset_id', $asset_id);
    //             $temp_inventory_columns = [];
    //             foreach($inventory_columns_in_loop as $inventory_column){
    //                 $temp_inventory_columns[] = $inventory_column;
    //             }
    //             if($asset_pivot_turunan->count()){
    //                 $inventory_columns_related = $this->getDataInventoryColumnTurunan($asset_id, $inventory_columns_related);
    //             } 
    //             $temp = (object)[
    //                 "asset_type" => $assets->where('id', $asset_id)->first()->name,
    //                 "asset_id" => $asset_id,
    //                 "quantity_needed" => $asset_pivot->quantity,
    //                 "inventory_columns" => $temp_inventory_columns
    //             ];
    //             $inventory_columns_related[] = $temp;
    //         }
    //         return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["inventory_columns_turunan" => $inventory_columns_turunan, "inventory_columns_related" => $inventory_columns_related, "vendors" => $vendors, "assets" => $assets]]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

    // public function cudInventoryColumn(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }

    //     try{
    //         $asset_id = $request->get('asset_id', null);
    //         if($asset_id === null) return response()->json(["success" => false, "message" => "Parameter Asset ID Kosong"], 400);
    //         $new_asset_relateds = $request->get('new_asset_relateds', []);
    //         $add_inventory_columns = $request->get('add_inventory_columns', []);
    //         $update_inventory_columns = $request->get('update_inventory_columns', []);
    //         $delete_inventory_columns = $request->get('delete_inventory_columns', []);

    //         $new_asset_related_ids = [];
    //         foreach($new_asset_relateds as $new_asset_related){
    //             array_push($new_asset_related_ids, $new_asset_related['asset_id']);
    //         }
            
    //         $asset_related_ids = AssetRelatedPivot::where('parent_asset_id', $asset_id)->pluck('asset_id')->toArray();
    //         if(!count($asset_related_ids)) {
    //             foreach($new_asset_relateds as $asset_related){
    //                 $pivot = new AssetRelatedPivot;
    //                 $pivot->parent_asset_id = $asset_id;
    //                 $pivot->asset_id = $asset_related['asset_id'];
    //                 $pivot->quantity = $asset_related['quantity'];
    //                 $pivot->save();
    //             }
    //         } else {
    //             $same_array = array_intersect($new_asset_related_ids, $asset_related_ids);
    //             $difference_array_new = array_diff($new_asset_related_ids, $asset_related_ids);
    //             $difference_array_delete = array_diff($asset_related_ids, $new_asset_related_ids);
                
    //             $asset_related_pivots = AssetRelatedPivot::where('parent_asset_id', $asset_id)->get();
    //             // Update
    //             foreach($same_array as $pivot_asset_id){
    //                 $object_search = array_search($pivot_asset_id, array_column($new_asset_relateds, 'asset_id'));
    //                 $new_asset_pivot = $new_asset_relateds[$object_search];
    //                 $asset_pivot = $asset_related_pivots->where('asset_id', $pivot_asset_id)->first();
    //                 $asset_pivot->asset_id = $new_asset_pivot['asset_id'];
    //                 $asset_pivot->quantity = $new_asset_pivot['quantity'];
    //                 $asset_pivot->save();
    //             }
    //             // Delete
    //             foreach($difference_array_delete as $pivot_asset_id){
    //                 $asset_pivot = $asset_related_pivots->where('asset_id', $pivot_asset_id)->first();
    //                 $asset_pivot->delete();
    //             }
    //             // Create
    //             foreach($difference_array_new as $pivot_asset_id){
    //                 $object_search = array_search($pivot_asset_id, array_column($new_asset_relateds, 'asset_id'));
    //                 $new_asset_pivot = $new_asset_relateds[$object_search];
    //                 $asset_pivot = new AssetRelatedPivot;
    //                 $asset_pivot->parent_asset_id = $asset_id;
    //                 $asset_pivot->asset_id = $new_asset_pivot['asset_id'];
    //                 $asset_pivot->quantity = $new_asset_pivot['quantity'];
    //                 $asset_pivot->save();
    //             }

    //         }

    //         foreach($add_inventory_columns as $add_inventory_column){
    //             $inventory_column = new InventoryColumn;
    //             $inventory_column->asset_id = $asset_id;
    //             $inventory_column->name = $add_inventory_column['name'];
    //             $inventory_column->data_type = $add_inventory_column['data_type'];
    //             $inventory_column->default = $add_inventory_column['default'];
    //             $inventory_column->required = $add_inventory_column['required'];
    //             $inventory_column->unique = $add_inventory_column['unique'];
    //             $inventory_column->save();
    //         }

    //         foreach($update_inventory_columns as $update_inventory_column){
    //             $inventory_column = InventoryColumn::find($update_inventory_column['id']);
    //             if($update_inventory_column === null) return response()->json(["success" => false, "message" => "Salah Satu Data Update Tidak Ditemukan"], 400);
    //             $inventory_column->name = $update_inventory_column['name'];
    //             $inventory_column->data_type = $update_inventory_column['data_type'];
    //             $inventory_column->default = $update_inventory_column['default'];
    //             $inventory_column->required = $update_inventory_column['required'];
    //             $inventory_column->unique = $update_inventory_column['unique'];
    //             $inventory_column->save();
    //         }

    //         foreach($delete_inventory_columns as $delete_inventory_column){
    //             $inventory_column = InventoryColumn::find($delete_inventory_column['id']);
    //             if($inventory_column === null) return response()->json(["success" => false, "message" => "Salah Satu Data Delete Tidak Ditemukan"], 400);
    //             $inventory_column->delete();
    //         }

    //         return response()->json(["success" => true, "message" => "Data Berhasil Diproses"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

    // public function addInventoryColumn(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     $inventory_column = new InventoryColumn;
    //     $inventory_column->asset_id = $request->get('asset_id');
    //     $inventory_column->name = $request->get('name');
    //     $inventory_column->data_type = $request->get('data_type');
    //     $inventory_column->default = $request->get('default');
    //     $inventory_column->required = $request->get('required', true);
    //     $inventory_column->unique = $request->get('unique', true);
    //     try{
    //         $inventory_column->save();
    //         return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

    // public function updateInventoryColumn(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     $validator = Validator::make($request->all(), [
    //         "id" => "required",
    //         "name" => "required",
    //         "data_type" => "required",
    //         "required" => "required",
    //         "unique" => "required"
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => $validator->errors()
    //         ]], 400);
    //     }
    //     $id = $request->get('id', null);
    //     try{
    //         $inventory_column = InventoryColumn::find($id);
    //         if($inventory_column === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
    //         $inventory_column->name = $request->get('name');
    //         $inventory_column->data_type = $request->get('data_type');
    //         $inventory_column->default = $request->get('default');
    //         $inventory_column->required = $request->get('required');
    //         $inventory_column->unique = $request->get('unique');
    //         $inventory_column->save();
    //         return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }    
    
    // public function deleteInventoryColumn(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     $id = $request->get('id', null);
    //     $inventory_column = InventoryColumn::find($id);
    //     if($inventory_column === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
    //     try{
    //         $inventory_column->delete();
    //         return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

    // //Inventory Value
    // public function getInventoryValues(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     try{
    //         $inventory_values = InventoryValue::all();
    //         if($inventory_values->isEmpty()) return response()->json(["success" => true, "message" => "Data Inventory Column Belum Terisi"]);
    //         return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory_values]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

    // public function addInventoryValue(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     $inventory_value = new InventoryValue;
    //     $inventory_value->inventory_id = $request->get('inventory_id');
    //     $inventory_value->inventory_column_id = $request->get('inventory_column_id');
    //     $inventory_value->value = $request->get('value');
    //     try{
    //         $inventory_value->save();
    //         return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

    // public function updateInventoryValue(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     $validator = Validator::make($request->all(), [
    //         "id" => "required",
    //         "value" => "required"
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => $validator->errors()
    //         ]], 400);
    //     }
    //     $id = $request->get('id', null);
    //     try{
    //         $inventory_value = InventoryValue::find($id);
    //         if($inventory_value === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
    //         $inventory_value->value = $request->get('value');
    //         $inventory_value->save();
    //         return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }    
    
    // public function deleteInventoryValue(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    //     $id = $request->get('id', null);
    //     $inventory_value = InventoryValue::find($id);
    //     if($inventory_value === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
    //     try{
    //         $inventory_value->delete();
    //         return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

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
            $status_condition = [
                (object)['id' => 1, 'name' => "Green"],
                (object)['id' => 2, 'name' => "Grey"],
                (object)['id' => 3, 'name' => "Red"],
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
            foreach($cgx_companies as $cgx_company){
                $company['id'] = $cgx_company['company_id'];
                $company['name'] = $cgx_company['company_name'];
                $companies[] = $company;
            }
            $data = (object)['models' => $models, 'assets' => $assets, 'manufacturers' => $manufacturers, 'status_condition' => $status_condition, 'status_usage' => $status_usage, 'companies' => $companies];
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
                    $inventory->additional_attributes = "Inventory Column Name of an Inventory Value not Found";
                    return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory]);
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
                foreach($core_pivots as $pivot){
                    $inventory_parts[] = $this->getInventoryChildren($pivot, $pivots, $all_inventories, $models, $assets);
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

    public function addInventory(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        
        $mig_id = $request->get('mig_id');
        $check_inventory = Inventory::where('mig_id', $mig_id)->first();
        if($check_inventory) return response()->json(["success" => false, "message" => "MIG ID Sudah Terdaftar"], 400);
        $inventory = new Inventory;
        $inventory->model_id = $request->get('model_id');
        $inventory->vendor_id = 0;
        $inventory->inventory_name = $request->get('inventory_name');
        $inventory->status_condition = $request->get('status_condition');
        $inventory->status_usage = 1;
        $inventory->location = $request->get('location');
        $inventory->is_exist = $request->get('is_exist');
        $inventory->deskripsi = $request->get('deskripsi');
        $inventory->manufacturer_id = $request->get('manufacturer_id');
        $inventory->mig_id = $mig_id;
        $inventory->serial_number = $request->get('serial_number');
        $inventory_values = $request->get('inventory_values',[]);
        $inventory_parts = $request->get('inventory_parts',[]);
        try{
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
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
        $inventory->vendor_id = 0;
        $inventory->inventory_name = $request->get('inventory_name');
        $inventory->status_condition = $request->get('status_condition');
        $inventory->status_usage = 2;
        $inventory->location = $request->get('location');
        $inventory->is_exist = $request->get('is_exist');
        $inventory->deskripsi = $request->get('deskripsi');
        $inventory->manufacturer_id = $request->get('manufacturer_id');
        $inventory->mig_id = $mig_id;
        $inventory->serial_number = $request->get('serial_number');
        $inventory_values = $request->get('inventory_values',[]);
        try{
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
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
        $check_inventory = Inventory::where('mig_id', $mig_id)->first();
        if($check_inventory && $check_inventory->id !== $id) return response()->json(["success" => false, "message" => "MIG ID Sudah Terdaftar"], 400);
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $inventory->vendor_id = 0;
            $inventory->inventory_name = $request->get('inventory_name');
            $inventory->status_condition = $request->get('status_condition');
            $inventory->status_usage = $request->get('status_usage');
            $inventory->location = $request->get('location');
            $inventory->is_exist = $request->get('is_exist');
            $inventory->deskripsi = $request->get('deskripsi');
            $inventory->manufacturer_id = $request->get('manufacturer_id');
            $inventory->mig_id = $mig_id;
            $inventory->serial_number = $request->get('serial_number');
            $inventory->save();
            $last_activity = Activity::all()->last();
            if($last_activity->subject_id === $id){
                $last_activity->causer_id = $check['id'];
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

    public function checkParent($id, $core_id, $pivots){
        $new_child_id = null;
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
            $last_activity->save();
            $remove_pivot = $pivots->where('child_id', $inventory_part_id)->first();
            $remove_pivot->delete();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
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

    public function addInventoryPart(Request $request){
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // return $check['id'];
        $id = $request->get('id', null);
        $inventory_part_id = $request->get('inventory_part_id', null);
        try{
            $check_used = $this->checkUsed($inventory_part_id);
            if($check_used['exist']) return response()->json(["success" => false, "message" => "Part sedang digunakan oleh id ".$check_used['id']], 400);
            $inventory = Inventory::find($inventory_part_id);
            if($inventory === null)return response()->json(["success" => false, "message" => "Id Inventory Tidak Terdaftar"], 400);
            if($inventory->status_usage === 1)return response()->json(["success" => false, "message" => "Inventory Sedang Digunakan"], 400);
            $inventory->status_usage = 1;
            $inventory->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->save();
            $pivot = new InventoryInventoryPivot;
            $pivot->parent_id = $id;
            $pivot->child_id = $inventory_part_id;
            $pivot->save();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
            $last_activity->save();

            $pivots = InventoryInventoryPivot::get();
            $pivot_children = $pivots->where('parent_id', $inventory_part_id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->addChildInventoryPart($pivot_child, $check['id']);
                }
            }
            return response()->json(["success" => true, "message" => "Berhasil Menambah Part Inventory"]);
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
        $inventory = Inventory::find($id);
        if($inventory === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        try{
            // DB::table('inventories')->where('id', $inventory->id)->update(array('vendor_id' => $log_user_id));
            $inventory->delete();
            $last_activity = Activity::all()->last();
            $last_activity->causer_id = $check['id'];
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
}