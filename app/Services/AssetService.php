<?php 

namespace App\Services;
use Excel;
use App\Role;
use App\User;
use App\Asset;
use Exception;
use App\Vendor;
use App\Company;
use App\Inventory;
use App\AssetColumn;
use App\Manufacturer;
use App\Relationship;
use App\InventoryValue;
use App\ModelInventory;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\ModelInventoryColumn;
use App\Services\UserService;
use App\StatusUsageInventory;
use App\RelationshipInventory;
use App\Services\CompanyService;
use App\StatusConditionInventory;
use App\Imports\InventoriesImport;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalService;

class AssetService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function getData($parent)
    {
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

    public function getTreeAssets(){
        // $assets = Asset::where('code', 'not like', "%.%")->orderBy('code')->get();
        // if($assets->isEmpty()) return $assets;
        // $new_assets = [];
        // foreach($assets as $asset){
        //     $temp = (object)[
        //         'id' => $asset->id,
        //         'title' => $asset->name,
        //         'key' => $asset->code,
        //         'value' => $asset->code,
        //         'children' => $this->getData($asset->code)
        //     ];
        //     $new_assets[] = $temp;
        // }
        // return $new_assets;
        $assets = Asset::with('children')->whereNull('parent_id')->select('id', 'name AS title', 'code AS key', 'code AS value', 'parent_id')->get();
        return $assets;
    }

    private function assetChildrenTreeList($id)
    {
        $asset = Asset::find($id);
        if($asset === null) return [];
        $list_assets = $asset->getChildrenTreeList()->pluck('id');
        $list_assets[] = $asset->id;
        return $list_assets;
    }

    public function getAssets($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $assets = $this->getTreeAssets();
            if(!count($assets)) return ["success" => true, "message" => "Asset Belum Terisi", "data" => [], "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $assets, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAsset($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $asset = Asset::with('assetColumns:id,asset_id,name,data_type,default,required')->find($id);
            if($asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $asset, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function registerCodeAsset($parent){
        if($parent !== null){
            $check_parent = Asset::where('code', $parent)->first();
            if($check_parent === null) return ["success" => false, "message" => "Parent Tidak Ditemukan", "status" => 400];
            $assets = Asset::where('code', 'like', $parent.".%")->where('code', 'not like', $parent.".___.%")->orderBy('code', 'desc')->get();
            if(count($assets)){
                $new_number = (int)substr($assets->first()->code, -3) + 1;
                $new_string = (string)$new_number;
                if($new_number < 10) {
                    return ["success" => true, "id" => $parent.".00".$new_string];
                } else if($new_number < 100) {
                    return ["success" => true, "id" => $parent.".0".$new_string];
                } else {
                    return ["success" => true, "id" => $parent.".".$new_string];
                }
            } else {
                return ["success" => true, "id" => $parent.".001"];
            }
        } else {
            $assets = Asset::where('code', 'not like', "%.%")->orderBy('code', 'desc')->get();
            if(count($assets)){
                $new_number = (int)$assets->first()->code + 1;
                $new_string = (string)$new_number;
                if($new_number < 10) {
                    return ["success" => true, "id" => "00".$new_string];
                } else if($new_number < 100) {
                    return ["success" => true, "id" => "0".$new_string];
                } else {
                    return ["success" => true, "id" => $new_string];
                }
            } else {
                return ["success" => true, "id" => "001"];
            }
        }
    }

    public function createAssetColumns($asset_columns, $id)
    {
        if(count($asset_columns)) {
            foreach($asset_columns as $asset_column){
                $new_asset_column = new AssetColumn;
                $new_asset_column->asset_id = $id;
                $new_asset_column->name = $asset_column['name'];
                $new_asset_column->data_type = $asset_column['data_type'];
                $new_asset_column->default = $asset_column['default'];
                $new_asset_column->required = $asset_column['required'];
                $new_asset_column->save();
            }
        }
    }

    public function addAsset($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $asset = new Asset;
        $asset->name = $data['name'];
        $asset->description = $data['description'];
        $asset->required_sn = $data['required_sn'];
        $parent = $data['parent'];
        if($parent === null) $parent_code = null;
        else {
            $parent_asset = Asset::find($parent);
            if($parent_asset === null) return ["success" => false, "message" => "Id Parent Tidak ditemukan", "status" => 400];
            $parent_code = $parent_asset->code;
        }
        try{
            $code = $this->registerCodeAsset($parent_code);
            if($code["success"] === false) return $code;
            // return ["success" => true,  "id" => $code, "status" => 200];
            $asset->parent_id = $parent;
            $asset->code = $code["id"];
            $asset->save();
            $asset_columns = $data['asset_columns'];
            $this->createAssetColumns($asset_columns, $asset->id);
            return ["success" => true, "message" => "Data Berhasil Disimpan", "id" => $asset->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function registerUpdateCodeAsset($parent, $check_old_code, $check_old_code_length)
    {
        if($parent !== null){
            $old_parent = substr($check_old_code, -($check_old_code_length), $check_old_code_length-4); 
            if($old_parent !== $parent){
                $check_parent = Asset::where('code', $parent)->first();
                if($check_parent === null) return ["success" => false, "message" => "Parent Tidak Ditemukan", "status" => 400];
                $assets = Asset::withTrashed()->where('code', 'like', $parent.".%")->where('code', 'not like', $parent.".___.%")->orderBy('code', 'desc')->get();
                if(count($assets)){
                    $new_number = (int)substr($assets->first()->code, -3) + 1;
                    $new_string = (string)$new_number;
                    if($new_number < 10) {
                        $new_code = $parent.".00".$new_string;
                    } else if($new_number < 100) {
                        $new_code = $parent.".0".$new_string;
                    } else {
                        $new_code = $parent.".".$new_string;
                    }
                } else {
                    $new_code = $parent.".001";
                }
            } else $new_code = $check_old_code;
        } else {
            if($check_old_code_length > 3){
                $assets = Asset::withTrashed()->where('code', 'not like', "%.%")->orderBy('code', 'desc')->get();
                if(count($assets)){
                    $new_number = (int)$assets->first()->code + 1;
                    $new_string = (string)$new_number;
                    if($new_number < 10) {
                        $new_code = "00".$new_string;
                    } else if($new_number < 100) {
                        $new_code = "0".$new_string;
                    } else {
                        $new_code = $new_string;
                    }
                } else {
                    $new_code = "001";
                }
            }
        }
        return ["success" => true, "new_code" => $new_code];
    }

    public function checkColumnPosessionAsset($update_columns, $delete_column_ids, $id)
    {
        $asset_columns = AssetColumn::where('asset_id', $id)->get();
        if(count($update_columns)){
            foreach($update_columns as $update_column){
                $check_column = $asset_columns->where('id', $update_column['id'])->first();
                if($check_column === null) return ["success" => false, "message" => "Kolom Tidak Bisa Diupdate, Id Kolom Tidak Dimiliki Asset", "id" => $update_column['id'], "status" => 400];
            }
        }
        
        if(count($delete_column_ids)){
            foreach($delete_column_ids as $delete_column_id){
                $check_column = $asset_columns->where('id', $delete_column_id)->first();
                if($check_column === null) return ["success" => false, "message" => "Kolom Tidak Bisa Didelete, Id Kolom Tidak Dimiliki Asset", "id" => $delete_column_id, "status" => 400];
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

        return ["success" => true];
    }

    public function processingUpdatedChild($is_deleted, $code, $check_old_code, $check_old_code_length)
    {
        $assets = Asset::where('code', 'like', $check_old_code.".%")->get();
        if(count($assets)){
            if($is_deleted){
                foreach($assets as $asset_child){
                    $asset_child->delete();
                }
            } else {
                $length_old_parent_code = $check_old_code_length + 1;
                foreach($assets as $asset_child){
                    $back_string = substr($asset_child->code, $length_old_parent_code);
                    $asset_child->code = $code.'.'.$back_string;
                    $asset_child->save();
                }
            }
        }
    }

    public function updateAsset($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $name = $data['name'];
        $parent = $data['parent'];
        $description = $data['description'];
        $required_sn = $data['required_sn'];
        $is_deleted = $data['is_deleted'];
        $add_columns = $data['add_columns'];
        $update_columns = $data['update_columns'];
        $delete_column_ids = $data['delete_column_ids'];
        $action = false;
        try{
            $asset = Asset::find($id);
            if($asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $check_old_code = $asset->code;
            $check_old_code_length = strlen($check_old_code);
            if($parent === $id) return ["success" => false, "message" => "Id Parent Sama dengan Id Asset yang Ingin Diubah", "status" => 400];
            if($parent !== $asset->parent_id){
                if($parent === null) $parent_code = null;
                else {
                    $parent_asset = Asset::find($parent);
                    if($parent_asset === null) return ["success" => false, "message" => "Id Parent Tidak ditemukan", "status" => 400];
                    $parent_code = $parent_asset->code;
                }
                $check_code = $this->registerUpdateCodeAsset($parent_code, $check_old_code, $check_old_code_length);
                if(!$check_code["success"]) return $check_code;
                $asset->code = $check_code['new_code'];
                $action = true;
            }
            $asset->parent_id = $parent;
            $asset->name = $name;
            $asset->description = $description;
            $asset->required_sn = $required_sn;
            $update_delete_columns = $this->checkColumnPosessionAsset($update_columns, $delete_column_ids, $id);
            if(!$update_delete_columns["success"]) return $update_delete_columns;
            $this->createAssetColumns($add_columns, $asset->id);
            $asset->save();
            if($action) $this->processingUpdatedChild($is_deleted, $asset->code, $check_old_code, $check_old_code_length);
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function checkCodeString($string){
        if($string !== null){
            $check_format_code = explode(".", $string);
            foreach($check_format_code as $checker){
                $checker = preg_replace( '/[^0-9]/', '', $checker);
                if(strlen($checker) !== 3) return ["success" => false, "message" => "New Parent Tidak Sesuai dengan Format", "status" => 400];
            }
        }
        return ["success" => true];
    }
    
    public function deleteAssetColumn($id)
    {
        $asset_columns = AssetColumn::where('asset_id', $id)->get();
        foreach($asset_columns as $asset_column){
            $asset_column->delete();
        }
    }

    public function actionChildDelete($old_code, $new_parent, $new_model_asset_id, $id)
    {
        $assets = Asset::where('code', 'like', $old_code.".%")->orderBy('code', 'asc')->get();
        if(count($assets)){
            if($new_parent !== null){
                $assets_check_number = Asset::where('code', 'like', $new_parent->code.".%")->where('code', 'not like', $new_parent->code.".___.%")->orderBy('code', 'desc')->get();
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
                        $parent = $new_parent->code.".00".$new_string;
                    } else if($new_number < 100) {
                        $parent = $new_parent->code.".0".$new_string;
                    } else {
                        $parent = $new_parent->code.".".$new_string;
                    }
                    $new_code = $parent.$back_string;
                    $asset->code = $new_code;
                    $asset->save();
                }
                $top_assets = Asset::where('parent_id', $id)->get();
                if(count($top_assets)){
                    foreach($top_assets as $top_asset){
                        $top_asset->parent_id = $new_parent->id;
                        $top_asset->save();
                    }
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
                $models = $full_models->where('asset_id', $id);
                    if(count($models)){
                    foreach($models as $model){
                        $model->delete();
                    }
                }
            }
        } else {
            // return "MASUK";
            $models = ModelInventory::where('asset_id', $id)->get();
            if(count($models)){
                foreach($models as $model){
                    $model->asset_id = $new_model_asset_id;
                    $model->save();
                }
            }
        }
    }

    public function deleteAsset($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $new_parent = $data['new_parent'];
        $new_model_asset_id = $data['new_model_asset_id'];
        if($new_parent !== null){
            $new_parent_asset = Asset::find($new_parent);
            if($new_parent_asset === null) return ["success" => false, "message" => "Id New Parent Tidak Ditemukan", "status" => 400];
        } else $new_parent_asset = null;
        
        $core_asset = Asset::find($id);
        if($core_asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        $old_code = $core_asset->code;
        // return ["success" => true, "message" => $new_parent_asset->code, "status" => 200];
        try{
            // return ["success" => false, "message" => $this->actionChildDelete($old_code, $new_parent, $new_model_asset_id, $id), "status" => 200];
            $this->actionChildDelete($old_code, $new_parent_asset, $new_model_asset_id, $id);
            $core_asset->delete();
            $this->deleteAssetColumn($id);
            

            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    // Models

    public function getFilterModels($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $name = $request->get('name', null);
            $models = ModelInventory::with('asset')->select('id','name', 'asset_id', 'required_sn');
            if($name) $models->where('name', 'like', "%".$name."%");
            $models = $models->limit(50)->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $models, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getModels(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $asset_id = $request->get('asset_id', null);
        $name = $request->get('name', null);
        $rows = $request->get('rows', 10);
        $sort_by = $request->get('sort_by', null);
        $sort_type = $request->get('sort_type', 'desc');

        if($rows > 100) $rows = 100;
        if($rows < 1) $rows = 10;
        
        $models = ModelInventory::withCount('inventories')->with('asset:id,name,code,deleted_at');

        if($asset_id){
            $assetChildrenTreeList = $this->assetChildrenTreeList($asset_id);
            $models = $models->whereIn('asset_id', $assetChildrenTreeList);
        } 
        if($name) $models = $models->where('name', 'like', "%".$name."%");
        if($sort_by){
            if($sort_by === 'name') $models = $models->orderBy('name', $sort_type);
            else if($sort_by === 'count') $models = $models->orderBy('inventories_count', $sort_type);
        }
        
        $models = $models->paginate($rows);
        if($models->isEmpty()) return ["success" => true, "message" => "Model Belum Terisi", "data" => [], "status" => 400];
        
        

        foreach($models as $model){
            $model->asset_name = $model->asset->name;
            if(strlen($model->asset->code) > 3){
                $parent_model = substr($model->asset->code, 0, 3);
                $parent_name = Asset::where('code', $parent_model)->first();
                $parent_name = $parent_name === null ? "Asset Not Found" : $parent_name->name;
                $model->asset_name = $parent_name . " / ". $model->asset_name;
            }
            $model->asset_deleted_at = $model->asset->deleted_at;
            $model->count = $model->inventories_count;
            $model->makeHidden('inventories_count','asset');
        }
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $models, "status" => 200];
    }

    public function getModel($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $model = ModelInventory::withCount('inventories')->with(['asset:id,name,code,required_sn,deleted_at','manufacturer','modelColumns','modelParts'])->find($id);
            if($model === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if(count($model->modelParts)){
                foreach($model->modelParts as $part) $part->quantity = $part->pivot->quantity;
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $model, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getModelRelations($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $assets = $this->getTreeAssets();
            $manufacturers = Manufacturer::select('id', 'name')->get();
            $data = (object)['assets' => $assets, 'manufacturers' => $manufacturers];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function createModelColumns($model_columns, $id)
    {
        if(count($model_columns)) {
            foreach($model_columns as $model_column){
                $new_model_column = new ModelInventoryColumn;
                $new_model_column->model_id = $id;
                $new_model_column->name = $model_column['name'];
                $new_model_column->data_type = $model_column['data_type'];
                $new_model_column->default = $model_column['default'];
                $new_model_column->required = $model_column['required'];
                $new_model_column->save();
            }
        }
    }

    public function addModel($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $name = $request->get('name');
        $check_name = ModelInventory::where('name', $name)->first();
        if($check_name !== null) return ["success" => false, "message" => "Nama Model Telah Terdaftar", "status" => 200];
        $model = new ModelInventory;
        $model->asset_id = $request->get('asset_id');
        $model->name = $name;
        $model->description = $request->get('description');
        $model->is_consumable = $request->get('is_consumable', false);
        $model->manufacturer_id = $request->get('manufacturer_id');
        $model->required_sn = $request->get('required_sn');
        try{
            $model->save();
            $model_columns = $request->get('model_columns',[]);
            $this->createModelColumns($model_columns, $model->id);
            $model_parts = $request->get('model_parts',[]);
            foreach($model_parts as $model_part){
                $model->modelParts()->attach($model_part['id'], ['quantity' => $model_part['quantity']]);
            }
            
            return ["success" => true, "message" => "Data Berhasil Disimpan", "id" => $model->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateDeleteModelColumn($id, $update_columns, $delete_column_ids)
    {
        $model_columns = ModelInventoryColumn::where('model_id', $id)->get();
        if(count($update_columns)){
            foreach($update_columns as $update_column){
                $check_column = $model_columns->where('id', $update_column['id'])->first();
                if($check_column === null) return ["success" => false, "message" => "Kolom Tidak Bisa Diupdate, Id Kolom Tidak Dimiliki Model", "id" => $update_column['id'], "status"=> 400];
            }
        }
        
        if(count($delete_column_ids)){
            foreach($delete_column_ids as $delete_column_id){
                $check_column = $model_columns->where('id', $delete_column_id)->first();
                if($check_column === null) return ["success" => false, "message" => "Kolom Tidak Bisa Didelete, Id Kolom Tidak Dimiliki Model", "id" => $delete_column_id, "status"=> 400];
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
        return ["success" => true];
    }

    public function updateModel($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $model = ModelInventory::find($id);
        if($model === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status"=> 400];
        $model->asset_id = $request->get('asset_id');
        $model->name = $request->get('name');
        $model->description = $request->get('description');
        $model->manufacturer_id = $request->get('manufacturer_id');
        $model->required_sn = $request->get('required_sn');
        try{
            $delete_column_ids = $request->get('delete_column_ids',[]);
            $update_columns = $request->get('update_columns',[]);
            $check_update_delete_model_column = $this->updateDeleteModelColumn($id, $update_columns, $delete_column_ids);
            if(!$check_update_delete_model_column['success']) return $check_update_delete_model_column;

            $add_columns = $request->get('add_columns',[]);
            $this->createModelColumns($add_columns, $id);
            
            $delete_model_ids = $request->get('delete_model_ids',[]);
            if(count($delete_model_ids)){
                foreach($delete_model_ids as $delete_model_id){
                    $model->modelParts()->detach($delete_model_id);
                }
            }
            
            $add_models = $request->get('add_models',[]);
            if(count($add_models)){
                foreach($add_models as $add_model){
                    $model->modelParts()->attach($add_model['id'], ['quantity' => $add_model['quantity']]);
                }
            }
            $update_models = $request->get('update_models',[]);
            if(count($update_models)){
                foreach($update_models as $update_model){
                    $model->modelParts()->syncWithoutDetaching([$update_model['id'] => ['quantity' => $update_model['quantity']]]);
                }
            }

            $model->save();
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteModel($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model = ModelInventory::find($id);
        if($model === null) return ["success" => false, "message" => "Id Model Tidak Ditemukan", "status" => 400];
        try{
            $model->delete();
            $model->modelParts()->detach();
            $columns = ModelInventoryColumn::where('model_id', $id)->delete();
            return ["success" => true, "message" => "Data Berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventoryPartsAssetParent($inventory_parts, $name_attribute)
    {
        foreach($inventory_parts as $inventory_part){
            if(count($inventory_part->$name_attribute)){
                $inventory_part->makeHidden($name_attribute);
                $inventory_part->inventory_parts = $this->getInventoryPartsAssetParent($inventory_part->$name_attribute, $name_attribute);
            }
            $inventory_part->modelInventory->asset->asset_name = $inventory_part->modelInventory->asset->name;
            if(strlen($inventory_part->modelInventory->asset->code) > 3){
                $parent_model = substr($inventory_part->modelInventory->asset->code, 0, 3);
                $parent = Asset::where('code', $parent_model)->first();
                $parent_name = $parent === null ? "Asset Not Found" : $parent->name;
                $inventory_part->modelInventory->asset->asset_name = $parent_name . " / ". $inventory_part->modelInventory->asset->name;
            }
        }
        return $inventory_parts;
    }

    public function getInventoryRelations($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $manufacturers = Manufacturer::withTrashed()->select('id','name','deleted_at')->get();
            $vendors = Vendor::select('id', 'name')->get();
            $status_condition = StatusConditionInventory::get();
            $status_usage = StatusUsageInventory::get();
            
            $this->companyService = new CompanyService;
            $tree_companies = $this->companyService->getCompanyTreeSelect(auth()->user()->company_id, 'noSubChild');
            
            $data = (object)['vendors' => $vendors, 'manufacturers' => $manufacturers, 'status_condition' => $status_condition, 'status_usage' => $status_usage, 'tree_companies' => $tree_companies];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventories($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $asset_id = $request->get('asset_id', null);
            $location_id = $request->get('location_id', null);
            $model_id = $request->get('model_id', null);
            $mig_id = $request->get('mig_id', null);
            $status_condition = $request->get('status_condition', null);
            $status_usage = $request->get('status_usage', null);
            $name = $request->get('name', null);
            $rows = $request->get('rows', 10);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');


            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            
            $inventories = Inventory::with(['statusCondition', 'statusUsage', 'locationInventory', 'modelInventory.asset:id,name,code,deleted_at'])->select('id', 'model_id', 'mig_id', 'status_condition', 'status_usage', 'location');

            if($asset_id){
                $assetChildrenTreeList = $this->assetChildrenTreeList($asset_id);
                $inventories = $inventories->whereHas('modelInventory', function($q) use ($assetChildrenTreeList){
                    $q->whereIn('model_inventories.asset_id', $assetChildrenTreeList);
                });
            }
            if($location_id){
                $inventories = $inventories->whereHas('locationInventory', function($q) use ($location_id){
                    $q->where('companies.id', $location_id);
                });
            }
            if($mig_id){
                $inventories = $inventories->where('mig_id', 'like', "%".$mig_id."%");
            }
            if($model_id){
                $inventories = $inventories->where('model_id', $model_id);
            }
            if($status_condition){
                $inventories = $inventories->where('status_condition', $status_condition);
            }
            if($status_usage){
                $inventories = $inventories->where('status_usage', $status_usage);
            }

            if($sort_by){
                if($sort_by === 'status_usage') $inventories = $inventories->orderBy('status_usage', $sort_type);
                else if($sort_by === 'status_condition') $inventories = $inventories->orderBy('status_condition', $sort_type);
                else if($sort_by === 'mig_id') $inventories = $inventories->orderBy('mig_id', $sort_type);
            }
            
            $inventories = $inventories->paginate($rows);
            foreach($inventories as $inventory){
                $inventory->modelInventory->asset->asset_name = $inventory->modelInventory->asset->name;
                if(strlen($inventory->modelInventory->asset->code) > 3){
                    $parent_model = substr($inventory->modelInventory->asset->code, 0, 3);
                    $parent = Asset::where('code', $parent_model)->first();
                    $parent_name = $parent === null ? "Asset Not Found" : $parent->name;
                    $inventory->modelInventory->asset->asset_name = $parent_name . " / " . $inventory->modelInventory->asset->name;
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCompanyInventories($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->get('id', null);
            $keyword = $request->get('keyword', null);
            $name = $request->get('name', null);
            $rows = $request->get('rows', 10);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');

            $company = Company::find($id);
            if(!$company) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            
            $companyService = new CompanyService;
            $company_list = $companyService->checkSubCompanyList($company);
            $inventories = Inventory::with('locationInventory:id,name,parent_id,role')
            ->select('inventories.id', 'inventories.mig_id', 'inventories.location','model_inventories.name as model_name','assets.name as asset_name','assets.code as asset_code')
            ->join('model_inventories', 'inventories.model_id', '=', 'model_inventories.id')
            ->join('assets', 'model_inventories.asset_id', '=', 'assets.id')
            ->whereIn('inventories.location', $company_list);
            if($keyword){
                $inventories = $inventories->where(function($query) use ($keyword) {
                    $query->where('inventories.mig_id', 'like', "%".$keyword."%")
                    ->orWhere('model_inventories.name', 'like', "%".$keyword."%")
                    ->orWhere('assets.name', 'like', "%".$keyword."%");
                });
            }
            if($sort_by){
                if($sort_by === 'mig_id') $inventories = $inventories->orderBy('inventories.mig_id', $sort_type);
                else if($sort_by === 'model_name') $inventories = $inventories->orderBy('model_inventories.name', $sort_type);
                else if($sort_by === 'asset_name') $inventories = $inventories->orderBy('assets.name', $sort_type);
            }
            
            $inventories = $inventories->paginate($rows);
            foreach($inventories as $inventory){
                if(strlen($inventory->asset_code) > 3){
                    $parent_model = substr($inventory->asset_code, 0, 3);
                    $parent = Asset::where('code', $parent_model)->first();
                    $parent_name = $parent === null ? "Asset Not Found" : $parent->name;
                    $inventory->asset_name = $parent_name . " / " . $inventory->asset_name;
                }
                $inventory->full_location = $inventory->locationInventory->fullSubName();
            }
            $inventories->makeHidden('locationInventory');
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getFilterInventories($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $keyword = $request->get('keyword', null);
            $asset_id = $request->get('asset_id', null);
            // $inventories = Inventory::with('modelInventory.asset')->select('id','mig_id', 'model_id');
            $inventories = DB::table('inventories')->select('inventories.id', 'inventories.model_id', 'inventories.mig_id', 'model_inventories.name as model_name', 'assets.name as asset_name')
            ->join('model_inventories', 'inventories.model_id', '=', 'model_inventories.id')
            ->join('assets', 'model_inventories.asset_id', '=', 'assets.id');
            if($asset_id) $inventories = $inventories->where('assets.id', $asset_id);
            if($keyword){
                $inventories = $inventories->where('inventories.mig_id', 'like', "%".$keyword."%")
                ->orWhere('model_inventories.name', 'like', "%".$keyword."%")
                ->orWhere('assets.name', 'like', "%".$keyword."%");
            }
            $inventories = $inventories->limit(50)->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventory($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $inventory = Inventory::with(['modelInventory.asset', 'vendor', 'manufacturer', 'locationInventory', 'additionalAttributes', 'inventoryParts', 'associations', 'quantities'])->find($id);
            if($inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $inventory->modelInventory->asset->asset_name = $inventory->modelInventory->asset->name;
            if(strlen($inventory->modelInventory->asset->code) > 3){
                $parent_model = substr($inventory->modelInventory->asset->code, 0, 3);
                $parent = Asset::where('code', $parent_model)->first();
                $parent_name = $parent === null ? "Asset Not Found" : $parent->name;
                $inventory->modelInventory->asset->asset_name = $parent_name . " / " . $inventory->modelInventory->asset->name;
            }
            $inventory->makeHidden('inventoryParts');
            $inventory->inventory_parts = $this->getInventoryPartsAssetParent($inventory->inventoryParts, 'inventoryParts');
            if(count($inventory->associations)){
                $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
                foreach($inventory->associations as $association){
                    if(isset($association->ticket->task->status)){
                        $association->status_name = $statuses[$association->ticket->task->status];
                        $association->status = $association->ticket->task->status;
                        $association->ticket_id = $association->ticket->id;
                    } 
                    else {
                        $association->status_name = '-';
                        $association->status = 0;
                        $association->ticket_id = 0;
                    }

                    if(isset($association->ticket->type->code)) $association->ticket_name = '#'.$association->ticket->type->code.' - '.$association->ticket->ticketable_id;
                    else $association->ticket_name = '-';
                    $association->makeHidden('ticket');
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventoryAddable(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $asset_id = $request->get('asset_id', null);
            $model_id = $request->get('model_id', null);
            $name = $request->get('name', null);
            $rows = $request->get('rows', 10);
            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            
            $inventories = Inventory::with(['modelInventory.asset:id,name,deleted_at', 'inventoryAddableParts'])->select('id', 'model_id', 'mig_id')->where('status_usage', 2);

            if($asset_id){
                $assetChildrenTreeList = $this->assetChildrenTreeList($asset_id);
                $inventories = $inventories->whereHas('modelInventory', function($q) use ($assetChildrenTreeList){
                    $q->whereIn('model_inventories.asset_id', $assetChildrenTreeList);
                });
            }
            if($model_id){
                $inventories = $inventories->where('model_id', $model_id);
            }
            if($name){
                $inventories = $inventories->where('mig_id', 'like', "%".$name."%");
            }
            
            $inventories = $inventories->paginate($rows);
            foreach($inventories as $inventory){
                $inventory->modelInventory->asset->asset_name = $inventory->modelInventory->asset->name;
                if(strlen($inventory->modelInventory->asset->code) > 3){
                    $parent_model = substr($inventory->modelInventory->asset->code, 0, 3);
                    $parent_name = Asset::where('code', $parent_model)->first();
                    $parent_name = $parent_name === null ? "Asset Not Found" : $parent_name->name;
                    $inventory->modelInventory->asset->asset_name = $parent_name . " / ". $inventory->modelInventory->asset->name;
                }
                $inventory->makeHidden('inventoryAddableParts');
                $inventory->inventory_parts = $this->getInventoryPartsAssetParent($inventory->inventoryAddableParts, 'inventoryAddableParts');
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventoryReplacements(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->get('id', null);
            $name = $request->get('name', null);
            $mig_id = $request->get('mig_id', null);
            if(!$id) return ["success" => false, "message" => "Id Asset Kosong", "status" => 400];
            $assetChildrenTreeList = $this->assetChildrenTreeList($id);
            $inventories = Inventory::with(['modelInventory.asset:id,name,code,deleted_at', 'inventoryReplacementParts'])->select('id', 'model_id', 'mig_id')
                    ->whereHas('modelInventory', function($q) use ($assetChildrenTreeList){
                        $q->whereIn('model_inventories.asset_id', $assetChildrenTreeList);
                    });

            if($mig_id){
                $inventories = $inventories->where('mig_id', 'like', "%".$mig_id."%");
            }
            
            $inventories = $inventories->limit(50)->get();
            foreach($inventories as $inventory){
                $inventory->modelInventory->asset->asset_name = $inventory->modelInventory->asset->fullName();
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getChangeStatusUsageDetailList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = (int)$request->get('id', null);    
        $name = $request->get('name', null);    
        try{
            if($id < -3) return ["success" => false, "message" => "Tipe Id Tidak Tepat", "status" => 400];
            if($id === -1 || $id === -2){     
                $role_checker = $id * -1;
                $userService = new UserService;
                $users = $userService->getFullUserList($role_checker, $name);
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $users, "status" => 200];
            } else if($id === -3){
                $this->companyService = new CompanyService;
                $client_company_list = $this->companyService->getCompanyTreeSelect(1, 'clientChild');
                
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $client_company_list, "status" => 200];
            } else {
                $this->companyService = new CompanyService;
                $client_company_list = $this->companyService->getCompanyTreeSelect($id, 'subChild');
                
                $front_end_data = [$client_company_list];
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $front_end_data, "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function saveInventoryParts($parent_inventory, $inventory, $location, $causer_id)
    {
        $new_inventory = new Inventory;
        $new_inventory->model_id = $inventory['model_id'];
        $new_inventory->vendor_id = $inventory['vendor_id'];
        $new_inventory->status_condition = $inventory['status_condition'];
        $new_inventory->status_usage = 1;
        $new_inventory->location = $location;
        $new_inventory->deskripsi = $inventory['deskripsi'];
        $new_inventory->manufacturer_id = $inventory['manufacturer_id'];
        $new_inventory->mig_id = $inventory['mig_id'];
        $new_inventory->serial_number = $inventory['serial_number'];
        $inventory_values = $inventory['inventory_values'];
        $inventory_parts = $inventory['inventory_parts'];
        
        $new_inventory->save();
        $parent_inventory->inventoryParts()->attach($new_inventory->id);
        
        $logService = new LogService;
        $properties['attributes'] = [
            'parent_id' => $parent_inventory->id,
            'child_id' => $new_inventory->id
        ];
        $logService->createLogInventoryPivot($new_inventory->id, $causer_id, $properties);
        
        if(count($inventory_values)){
            foreach($inventory_values as $inventory_value){
                $new_inventory->additionalAttributes()->attach($inventory_value['model_inventory_column_id'], ['value' => $inventory_value['value']]);
                $model_inventory_column = ModelInventoryColumn::select('id', 'name')->find($inventory_value['model_inventory_column_id']);
                $new_inventory[$model_inventory_column->name] = $inventory_value['value'];
            }
        }
        
        $properties['attributes'] = $new_inventory;
        $notes = "Created as part of inventory with id ".$parent_inventory->id;
        $logService->createLogInventory($new_inventory->id, $causer_id, $properties, $notes);
        if(count($inventory_parts)){
            $properties = [];
            $list_id = [];
            foreach($inventory_parts as $inventory_part){
                $list_id[] = $this->saveInventoryParts($new_inventory, $inventory_part, $location, $causer_id);
            }

            $properties['attributes']['list_parts'] = $list_id;
            $logService->createLogInventoryPivotParts($new_inventory->id, $causer_id, $properties);
        }
        return $new_inventory->id;
        
    }

    public function allCheckMigIdsAndModelColumns($inventory, $list_ids)
    {
        if(count($inventory['inventory_parts'])){
            foreach($inventory['inventory_parts'] as $temp_inventory){
                $list_ids = $this->allCheckMigIdsAndModelColumns($temp_inventory, $list_ids);
            }
        }
        $list_ids['mig_ids'][] = $inventory['mig_id'];
        if(count($inventory['inventory_values'])){
            foreach($inventory['inventory_values'] as $inventory_value){
                $list_ids['model_column_ids'][] = $inventory_value['model_inventory_column_id'];
            }
        }
        return $list_ids;
    }

    public function addInventory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $model_id = $request->get('model_id');
        $model = ModelInventory::find($model_id);
        if($model === null) return ["success" => false, "message" => "Model Tidak Ditemukan", "status" => 400];
        $location = $request->get('location');
        if($model->is_consumable){
            $quantity = $request->get('quantity');
            if($quantity === null)  return ["success" => false, "message" => "Quantity masih kosong", "status" => 400];
            if($location === null)  return ["success" => false, "message" => "Lokasi masih kosong", "status" => 400];
        }
        $mig_id = $request->get('mig_id');
        $inventory_parts = $request->get('inventory_parts',[]);

        $mig_ids = [];
        $model_column_ids = [];
        $allCheckMigIdsAndModelColumns = [];
        if(count($inventory_parts)){
            foreach($inventory_parts as $inventory_part){
                $tempList = $this->allCheckMigIdsAndModelColumns($inventory_part, ['mig_ids' => [], 'model_column_ids' => []]);
                $mig_ids = array_merge($mig_ids, $tempList['mig_ids']);
                $model_column_ids = array_merge($model_column_ids, $tempList['model_column_ids']);
                $allCheckMigIdsAndModelColumns = array_merge($allCheckMigIdsAndModelColumns, $tempList);
            }
        }
        $mig_ids[] = $mig_id;
        $inventory = Inventory::select('id', 'mig_id')->whereIn('mig_id', $mig_ids)->first();
        if($inventory !== null) return ["success" => false, "message" => "MIG ID ".$inventory->mig_id." Sudah Terdaftar", "status" => 400];
        
        $inventory_values = $request->get('inventory_values', []);
        if(count($inventory_values)){
            foreach($inventory_values as $inventory_value){
                $model_column_ids[] = $inventory_value['model_inventory_column_id'];
            }
        }

        $model_inventory_columns = ModelInventoryColumn::select('id', 'name')->whereIn('id', $model_column_ids)->get();
        $model_inventory_column_ids = $model_inventory_columns->pluck('id')->toArray();
        $check_list_remaining = array_diff($model_column_ids, $model_inventory_column_ids);
        if(count($check_list_remaining)) return ["success" => false, "message" => "ID ".array_values($check_list_remaining)[0]." Kolom Model Inventori Tidak Ditemukan", "status" => 400];
        
        $inventory = new Inventory;
        $inventory->model_id = $model_id;
        $inventory->vendor_id = $request->get('vendor_id');
        $inventory->status_condition = $request->get('status_condition');
        $inventory->status_usage = $request->get('status_condition');
        $inventory->location = $location;
        $inventory->deskripsi = $request->get('deskripsi');
        $inventory->manufacturer_id = $request->get('manufacturer_id');
        $inventory->mig_id = $mig_id;
        $inventory->serial_number = $request->get('serial_number');
        $inventory->is_consumable = $model->is_consumable;
        $notes = $request->get('notes');
        try{
            $inventory->save();
            if($model->is_consumable) $inventory->quantities()->attach($location, ['quantity' => $quantity]);

            if(count($inventory_values)){
                foreach($inventory_values as $inventory_value){
                    $inventory->additionalAttributes()->attach($inventory_value['model_inventory_column_id'], ['value' => $inventory_value['value']]);
                    $model_inventory_column = $model_inventory_columns->find($inventory_value['model_inventory_column_id']);
                    $inventory[$model_inventory_column->name] = $inventory_value['value'];
                }
            }
            $causer_id = auth()->user()->id; 
            $logService = new LogService;
            $properties['attributes'] = $inventory;
            $logService->createLogInventory($inventory->id, $causer_id, $properties, $notes);
            if(count($inventory_parts) && !$model->is_consumable){
                $properties = [];
                $list_id = [];
                foreach($inventory_parts as $inventory_part){
                    $list_id[] = $this->saveInventoryParts($inventory, $inventory_part, $inventory->location, $causer_id);
                }

                $properties['attributes']['list_parts'] = $list_id;
                $logService->createLogInventoryPivotParts($inventory->id, $causer_id, $properties);
            }
            return ["success" => true, "message" => "Inventory Berhasil Ditambah", "id" => $inventory->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addInventoryNotes($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $logService = new LogService;
        $subject_id = $data['id'];
        $causer_id = auth()->user()->id;
        $notes = $data['notes'];
        $logService->noteLogInventory($subject_id, $causer_id, $notes);
        return ["success" => true, "message" => "Notes Berhasil Ditambah", "status" => 200];
    }

    public function checkUpdateProperties($old_inventory, $new_inventory)
    {
        $properties = false;
        foreach($new_inventory->getAttributes() as $key => $value){
            if($key === "created_at" || $key === "updated_at") continue;
            if($new_inventory->$key !== $old_inventory[$key]){
                $properties['old'][$key] = $old_inventory[$key];
                $properties['attributes'][$key] = $new_inventory->$key;
            }
        }
        return $properties;
    }

    public function updateInventory($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $mig_id = $data['mig_id'];
        $notes = $data['notes'];
        $location = $data['location'];
        $check_inventory = Inventory::select('id', 'mig_id')->where('mig_id', $mig_id)->first();
        if($check_inventory && $check_inventory->id !== $id) return ["success" => false, "message" => "MIG ID Sudah Terdaftar", "status" => 400];
        try{
            $inventory = Inventory::with(['additionalAttributes', 'quantities'])->find($id);
            if($inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
            $old_inventory = [];
            foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;

            $inventory->vendor_id = $data['vendor_id'];
            $inventory->location = $location;
            $inventory->deskripsi = $data['deskripsi'];
            $inventory->manufacturer_id = $data['manufacturer_id'];
            $inventory->mig_id = $mig_id;
            $inventory->serial_number = $data['serial_number'];
            $inventory->save();
            
            if($inventory->is_consumable){
                $search = $inventory->quantities->search(function ($item) use ($location) {
                    return $item->id == $location;
                });
                $quantity = $inventory->quantities[$search]->quantity;
                $inventory->quantities()->detach($old_inventory['location']);
                $inventory->quantities()->attach((int)$location, ['quantity' => $quantity]);
            }

            foreach($inventory->additionalAttributes as $temp_inventory_value){
                $old_inventory[$temp_inventory_value->name] = $temp_inventory_value->value;
            }

            $new_inventory_values = $data['inventory_values'];
            foreach($new_inventory_values as $inventory_value){
                $model_inventory_column = ModelInventoryColumn::select('id', 'name')->find($inventory_value['id']);
                $check = $inventory->additionalAttributes()->updateExistingPivot($inventory_value['id'], ['value' => $inventory_value['value']]);
                if($check)$inventory[$model_inventory_column->name] = $inventory_value['value'];
            }
            $properties = $this->checkUpdateProperties($old_inventory, $inventory);
            if($properties){
                $causer_id = auth()->user()->id; 
                $logService = new LogService;
                $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
            }
            return ["success" => true, "message" => "Inventory Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function setStatusInventoryPartReplacements($inventory, $causer_id, $status_usage, $status_condition, $location, $replacement){
        $old_inventory = [];
        foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;

        $inventory->status_usage = $status_usage;
        $inventory->save();

        $logService = new LogService;
        if($replacement) $notes = "Became Replacements with Its Parent";
        else $notes = "Replaced Along with Its Parent";
        $properties = $this->checkUpdateProperties($old_inventory, $inventory);
        if($properties){
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }
        
        $inventories = $inventory->inventoryPart;
        if(count($inventories)){
            foreach($inventories as $temp_inventory){
                $this->setStatusInventoryPartReplacements($temp_inventory, $causer_id, $status_usage, $status_condition, $location, $replacement);
            }
        }
    }

    public function replaceInventoryPart($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $replacement_id = $data['replacement_id'];
        $notes = $data['notes'];
        $causer_id = auth()->user()->id;
        $logService = new LogService;
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Id Inventori yang akan Diganti Tidak Ditemukan", "status" => 400];
            $old_inventory = [];
            foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;
            
            $check_parent_inventory = $inventory->inventoryParent;
            if(!count($check_parent_inventory)) return ["success" => false, "message" => "Inventori yang akan Diganti Tidak Termasuk dari Part Inventori", "status" => 400];
            $parent_inventory = $check_parent_inventory[0];
            $inventory_replacement_location = $parent_inventory->location;
            $inventory_replacement_status_usage = $parent_inventory->status_usage;
            
            $old_inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');
            $inventory_replacement = Inventory::find($replacement_id);
            if($inventory_replacement === false) return ["success" => false, "message" => "Id Inventori Pengganti Tidak Ditemukan", "status" => 400];
            $old_inventory_replacement = [];
            foreach($inventory_replacement->getAttributes() as $key => $value) $old_inventory_replacement[$key] = $value;

            $origin_location = auth()->user()->company_id;
            $temp_status_usage = $inventory->status_usage;
            $inventory->status_usage = 3;
            $inventory->status_condition = 2;
            $inventory->location = $origin_location;
            $inventory->save();
            $properties = $this->checkUpdateProperties($old_inventory, $inventory);
            if($properties){
                $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
            }
            $parent_inventory = $inventory->inventoryParent[0];
            $old_inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');
            
            if(count($inventory->inventoryPart)){
                foreach($inventory->inventoryPart as $temp_inventory){
                    $this->setStatusInventoryPartReplacements($temp_inventory, $causer_id, $inventory->status_usage, $inventory->status_condition, $origin_location, false);
                }
            }
            
            $inventory_replacement->status_usage = $inventory_replacement_status_usage;
            $inventory_replacement->location = $inventory_replacement_location;
            $inventory_replacement->save();
            $properties = $this->checkUpdateProperties($old_inventory_replacement, $inventory_replacement);
            if($properties){
                $log_notes = "Replacement of inventory with id ".$id;
                $logService->updateLogInventory($inventory_replacement->id, $causer_id, $properties, $log_notes);
            }
            
            $check_parent_inventory_replacement = $inventory_replacement->inventoryParent;
            if(!count($check_parent_inventory_replacement)){
                $parent_inventory->inventoryParts()->detach($id);
                $properties = [];
                $properties['old'] = [
                    'parent_id' => $parent_inventory->id,
                    'child_id' => $id
                ];
                $logService->deleteLogInventoryPivot($id, $causer_id, $properties, $notes);

                $parent_inventory->inventoryParts()->attach($replacement_id);
                $properties = [];
                $properties['attributes'] = [
                    'parent_id' => $parent_inventory->id,
                    'child_id' => $replacement_id
                ];
                $log_notes = "Replacement of inventory with id ".$id;
                $logService->createLogInventoryPivot($replacement_id, $causer_id, $properties, $log_notes);
            } else {
                $parent_inventory_replacement = $check_parent_inventory_replacement[0];
                $old_replacement_parent_list = $parent_inventory_replacement->inventoryPart->pluck('id');

                $properties = [];
                $properties['old'] = ['parent_id' => $parent_inventory->id];
                $properties['attributes'] = ['parent_id' => $parent_inventory_replacement->id];
                $logService->updateLogInventoryPivot($id, $causer_id, $properties, $notes);
                $parent_inventory->inventoryParts()->detach($id);
                $parent_inventory_replacement->inventoryParts()->attach($id);

                $properties = [];
                $properties['old'] = ['parent_id' => $parent_inventory_replacement->id];
                $properties['attributes'] = ['parent_id' => $parent_inventory->id];
                $log_notes = "Replacement of inventory with id ".$id;
                $logService->updateLogInventoryPivot($replacement_id, $causer_id, $properties, $log_notes);
                $parent_inventory_replacement->inventoryParts()->detach($replacement_id);
                $parent_inventory->inventoryParts()->attach($replacement_id);
            
                $new_parent = Inventory::with('inventoryPart')->select('id')->find($parent_inventory_replacement->id);
                $replacement_parent_list = $new_parent->inventoryPart->pluck('id');// return ["success" => true, "message" => [$replacement_parent_list, $old_replacement_parent_list], "status" => 200];
                
                $properties = [];
                $properties['old']['list_parts'] = $old_replacement_parent_list;
                $properties['attributes']['list_parts'] = $replacement_parent_list;
                $inverse_notes = "Part Used as Replacement";
                $logService->updateLogInventoryPivotParts($parent_inventory_replacement->id, $causer_id, $properties, $inverse_notes);
            }
            
            if(count($inventory_replacement->inventoryPart)){
                foreach($inventory_replacement->inventoryPart as $temp_inventory){
                    $this->setStatusInventoryPartReplacements($temp_inventory, $causer_id, $inventory_replacement->status_usage, $inventory_replacement->status_condition, $inventory_replacement_location, true);
                }
            }
            
            $new_parent = Inventory::with('inventoryPart')->select('id')->find($parent_inventory->id);
            $inventory_parent_list = $new_parent->inventoryPart->pluck('id');
            
            $properties = [];
            $properties['old']['list_parts'] = $old_inventory_parent_list;
            $properties['attributes']['list_parts'] = $inventory_parent_list;
            // $notes = "Part Replaced";
            $logService->updateLogInventoryPivotParts($parent_inventory->id, $causer_id, $properties, $notes);
            
            return ["success" => true, "message" => "Berhasil Melakukan Replacement Part Inventory", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function checkParent($id, $check_id){
        $inventory = Inventory::with('inventoryParent')->select('id')->find($id);
        if(count($inventory->inventoryParent)){
            if($inventory->inventoryParent[0]->id === $check_id) return true;
            return $this->checkParent($inventory->inventoryParent[0]->id, $check_id);
        }
        return false;
    }

    public function removeChildInventoryPart($inventory, $causer_id, $status = null){
        $old_inventory = [];
        foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;

        $inventory->status_condition = 3;
        $inventory->save();

        $logService = new LogService;
        $properties = $this->checkUpdateProperties($old_inventory, $inventory);
        if($properties){
            if($status === "delete inventory") $notes = "Parent Has Been Deleted";
            else $notes = "Removed as Parts with Its Parent";
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }
        
        if(count($inventory->inventoryPart)){
            foreach($inventory->inventoryPart as $temp_inventory){
                $this->removeChildInventoryPart($temp_inventory, $causer_id, $status);
            }
        }
    }

    public function removeInventoryPart($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $inventory_part_id = $data['inventory_part_id'];
        $causer_id = auth()->user()->id;
        try{
            $inventory = Inventory::with('inventoryPart', 'inventoryParent')->find($inventory_part_id);
            if(!count($inventory->inventoryParent) || $inventory->inventoryParent[0]->id != $id) return ["success" => false, "message" => "Id Part Tidak Termasuk dari Part yang Dimiliki Inventory Ini", "status" => 400];
            
            $old_inventory = [];
            foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;

            $inventory->status_condition = 3;
            $inventory->save();

            $parent_inventory = Inventory::with(['inventoryPart', 'inventoryParts'])->select('id')->find($id);
            $old_inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');

            $logService = new LogService;
            $properties = $this->checkUpdateProperties($old_inventory, $inventory);
            if($properties){
                $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
            }

            $parent_inventory->inventoryParts()->detach($inventory_part_id);
            $properties = [];
            $properties['old'] = [
                'parent_id' => $id,
                'child_id' => $inventory_part_id
            ];
            $logService->deleteLogInventoryPivot($inventory_part_id, $causer_id, $properties, $notes);

            if(count($inventory->inventoryPart)){
                foreach($inventory->inventoryPart as $temp_inventory){
                    $this->removeChildInventoryPart($temp_inventory, $causer_id);
                }
            }
            
            $parent_inventory = Inventory::with('inventoryPart')->select('id')->find($id);
            $inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');

            $properties = [];
            $properties['old']['list_parts'] = $old_inventory_parent_list;
            $properties['attributes']['list_parts'] = $inventory_parent_list;
            // $notes = "Part Removed";
            $logService->updateLogInventoryPivotParts($id, $causer_id, $properties, $notes);
            
            return ["success" => true, "message" => "Berhasil Menghapus Part Inventory", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addChildInventoryPart($inventory, $causer_id, $status_usage, $location){
        $properties['old'] = ['status_usage' => $inventory->status_usage];
        $inventory->status_usage = $status_usage;
        $inventory->location = $location;
        $inventory->save();
        $properties['attributes'] = ['status_usage' => $status_usage];

        if($properties['attributes'] !== $properties['old']){
            $logService = new LogService;
            $notes = "Added as Parts with Its Parent";
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }

        if(count($inventory->inventoryPart)){
            foreach($inventory->inventoryPart as $temp_inventory){
                $this->addChildInventoryPart($temp_inventory, $causer_id, $status_usage);
            }
        }
    }

    public function addInventoryParts($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $inventory_part_ids = $data['inventory_part_ids'];
        $causer_id = auth()->user()->id;
        try{
            if(count($inventory_part_ids)){
                $parent_inventory = Inventory::with('inventoryPart')->select('id', 'status_usage', 'location', 'model_id')->find($id);
                $old_inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');
                // Check kesamaan part yang akan ditambahkan dengan induknya
                foreach($inventory_part_ids as $inventory_part_id){
                    if($inventory_part_id == $id) return ["success" => false, "message" => "Id Inventory Sama Dengan Id Parent", "status" => 400];
                    $inventory = Inventory::with('inventoryParent', 'inventoryPart')->find($inventory_part_id);
                    if($inventory === null) return ["success" => false, "message" => "Inventory Id $inventory_part_id Tidak Terdaftar", "status" => 400];
                    $check_parent = $this->checkParent($inventory_part_id, $id);
                    if($check_parent) return ["success" => false, "message" => "Inventory Id $inventory_part_id Termasuk Dari Part Inventory Id $id", "status" => 400];
                }

                // Check jumlah kesamaan tipe asset part yang dimiliki dengan yang akan ditambah dibandingkan dengan Template Modelnya
                $list_count_inventory_part = DB::table('inventory_inventory_pivots')
                     ->select(DB::raw('asset_id, count(*) as quantity'))
                    //  ->select(DB::raw('model_id, count(*) as quantity'))
                     ->join('inventories', 'inventory_inventory_pivots.child_id', '=', 'inventories.id')
                     ->join('model_inventories', 'inventories.model_id', '=', 'model_inventories.id')
                     ->where('parent_id', '=', $id)
                     ->groupBy('asset_id')
                     ->get();

                $list_count_asset_part = DB::table('model_model_pivots')
                    ->join('model_inventories', 'model_model_pivots.child_id', '=', 'model_inventories.id')
                    ->select(DB::raw('asset_id, sum(quantity) as quantity'))
                    // ->select('child_id as model_id','quantity', 'model_inventories.asset_id')
                    ->where('parent_id', '=', $parent_inventory->model_id)
                    ->groupBy('asset_id')
                    ->get();

                foreach($list_count_asset_part as $part) $part->quantity = (int)$part->quantity;
                // return ["success" => true, "message" => [$list_count_inventory_part, $list_count_asset_part], "status" => 200];
                $list_add_inventories = Inventory::select('inventories.id', 'model_id', 'asset_id')->join('model_inventories', 'inventories.model_id', '=', 'model_inventories.id')->find($inventory_part_ids);
                foreach($list_add_inventories as $check_inventory){
                    $check_asset = $check_inventory->asset_id;
                    $search_asset = $list_count_asset_part->search(function ($item) use ($check_asset) {
                        return $item->asset_id === $check_asset;
                    });

                    if($search_asset !== false){
                        $search_inventory = $list_count_inventory_part->search(function ($item) use ($check_asset) {
                            return $item->asset_id === $check_asset;
                        });
                        if($search_inventory !== false){
                            // Membandingkan jumlah yang dibutuhkan pada template asset dengan part yang dimiliki dari induk inventory
                            if($list_count_inventory_part[$search_inventory]->quantity >= $list_count_asset_part[$search_asset]->quantity){
                                $inventory_id = $check_inventory->id;
                                return ["success" => false, "message" => "Inventory dengan id $inventory_id tidak bisa ditambahkan sebagai part karena induknya memiliki part dengan asset id yang sama dengan jumlah maksimal", "status" => 400];
                            } else $list_count_inventory_part[$search_inventory]->quantity += 1;
                        } else {
                            $list_count_inventory_part->push((object)[
                                "asset_id" => $check_asset,
                                "quantity" => 1
                            ]);
                        }

                    } else {
                        $inventory_id = $check_inventory->id;
                        $asset_id = $parent_inventory->asset_id;
                        return ["success" => false, "message" => "Inventory dengan id $inventory_id memiliki asset yang berbeda dengan asset dari part induk yang akan digabungkan", "status" => 400];
                    } 
                }

                foreach($inventory_part_ids as $inventory_part_id){
                    $inventory = Inventory::with('inventoryParent', 'inventoryPart')->find($inventory_part_id);
                    // if($inventory === null) return ["success" => false, "message" => "Id Inventory Tidak Terdaftar", "status" => 400];
                    // if($inventory->status_usage === 1)return ["success" => false, "message" => "Inventory Sedang Digunakan", "status" => 400];
                    
                    $old_inventory = [];
                    $status_usage = $parent_inventory->status_usage;
                    $location = $parent_inventory->location;
                    foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;
                    $inventory->status_usage = $status_usage;
                    $inventory->location = $location;
                    $inventory->save();
                    
                    $logService = new LogService;
                    $properties = $this->checkUpdateProperties($old_inventory, $inventory);
                    if($properties){
                        $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
                    }
                    
                    $parent_inventory->inventoryPart()->attach($inventory_part_id);
                    $check_parent_inventory_part = $inventory->inventoryParent;
                    $properties = [];
                    if(count($check_parent_inventory_part)){
                        $parent_inventory_part = $check_parent_inventory_part[0];
                        $parent_inventory_part->inventoryPart()->detach($inventory_part_id);
                        $properties['old'] = ['parent_id' => $parent_inventory_part->id];
                        $properties['attributes'] = ['parent_id' => $id];
                        $logService->updateLogInventoryPivot($inventory_part_id, $causer_id, $properties, $notes);
                    } else {
                        $properties['attributes'] = [
                            'parent_id' => $id,
                            'child_id' => $inventory_part_id
                        ];
                        $logService->createLogInventoryPivot($inventory_part_id, $causer_id, $properties, $notes);
                    }

                    if(count($inventory->inventoryPart)){
                        foreach($inventory->inventoryPart as $temp_inventory){
                            $this->addChildInventoryPart($temp_inventory, $causer_id, $status_usage, $location);
                        }
                    }
                }

                $parent_inventory = Inventory::with('inventoryPart')->select('id')->find($id);
                $inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');
                
                $properties = [];
                $properties['old']['list_parts'] = $old_inventory_parent_list;
                $properties['attributes']['list_parts'] = $inventory_parent_list;
                // $notes = "Parts Added";
                $logService->updateLogInventoryPivotParts($id, $causer_id, $properties, $notes);
                
                return ["success" => true, "message" => "Berhasil Menambah Part Inventory", "status" => 200];
            } else {
                return ["success" => false, "message" => "Id Part yang Ingin Ditambahkan Kosong", "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteInventory($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $inventory = Inventory::with('additionalAttributes','inventoryPart')->find($id);
        $causer_id = auth()->user()->id;
        if($inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            foreach($inventory->additionalAttributes as $temp_inventory_value){
                $inventory[$temp_inventory_value->name] = $temp_inventory_value->value;
            }
            $inventory->delete();
            $inventory->inventoryPart()->detach();
            
            $inventory->makeHidden('additionalAttributes','inventoryPart');
            $logService = new LogService;
            $properties['old'] = $inventory;
            $logService->deleteLogInventory($id, $causer_id, $properties, $notes);

            // Will be Deleted from Its Parent Without Trace
            // $check_parent_inventory = $inventory->inventoryParent;
            // if(!count($check_parent_inventory)) return ["success" => false, "message" => "Inventori yang akan Diganti Tidak Termasuk dari Part Inventori", "status" => 400];
            // $parent_inventory = $check_parent_inventory[0];
            // $parent_inventory->inventoryPart()->detach($id);
            // $properties['old'] = [
            //     'parent_id' => $parent_inventory->id,
            //     'child_id' => $id
            // ];
            // $logService->deleteLogInventoryPivot($id, $causer_id, $properties, $notes);

            if(count($inventory->inventoryPart)){
                foreach($inventory->inventoryPart as $temp_inventory){
                    $properties['old'] = [
                        'parent_id' => $id,
                        'child_id' => $temp_inventory->id
                    ];
                    $logService->deleteLogInventoryPivot($temp_inventory->id, $causer_id, $properties, $notes);
                    $this->removeChildInventoryPart($temp_inventory, $causer_id, "delete inventory");
                }
            }
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeStatusCondition($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $status_condition = $data['status_condition'];
        $causer_id = auth()->user()->id;
        try{
            if($status_condition < 1 || $status_condition > 3){
                return ["success" => false, "message" => "Status Usage Tidak Tepat", "status" => 400];
            }
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Inventory Tidak Ditemukan", "status" => 400];
            
            $properties['old'] = ['status_condition' => $inventory->status_condition];
            $inventory->status_condition = $status_condition;
            $inventory->save();
            $properties['attributes'] = ['status_condition' => $status_condition];
            
            if($properties['attributes'] !== $properties['old']){
                $logService = new LogService;
                $logService->updateLogInventory($id, $causer_id, $properties, $notes);
            }
            return ["success" => true, "message" => "Status Kondisi Inventory Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeStatusUsage($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $status_usage = $data['status_usage'];
            if($status_usage < 1 || $status_usage > 3){
                return ["success" => false, "message" => "Status Usage Tidak Tepat", "status" => 400];
            }
            $id = $data['id'];
            $notes = $data['notes'];
            $relationship_type_id = $data['relationship_type_id'];
            $connected_id = $data['connected_id'];
            $detail_connected_id = $data['detail_connected_id'];
            if($status_usage === 1){
                if($relationship_type_id === null) return ["success" => false, "message" => "Relationship Type Belum Terisi", "status" => 400];
                if($relationship_type_id > -1 || $relationship_type_id < -3) return ["success" => false, "message" => "Relationship Type Id Tidak Tepat", "status" => 400];
                if($connected_id === null) return ["success" => false, "message" => "Connected Id Belum Terisi", "status" => 400];
            }
            
            $inventory = Inventory::with('modelInventory.asset')->find($id);
            if($inventory === null) return ["success" => false, "message" => "Inventory Tidak Ditemukan", "status" => 400];
            if($inventory->modelInventory->id === 0) return ["success" => false, "message" => "Tipe Model pada Inventory Tidak Ditemukan", "status" => 400];
            if($inventory->modelInventory->asset->id === 0) return ["success" => false, "message" => "Tipe Aset pada Tipe Model pada Inventory Tidak Ditemukan", "status" => 400];
            
            $causer_id = auth()->user()->id; 
            $properties['old'] = ['status_usage' => $inventory->status_usage];
            $inventory->status_usage = $status_usage;
            $inventory->save();
            $properties['attributes'] = ['status_usage' => $status_usage];
            
            $logService = new LogService;
            if($properties['attributes'] !== $properties['old']){
                $logService->updateLogInventory($id, $causer_id, $properties, $notes);
            }

            if($status_usage !== 1){
                //Delete Relationship except Inventory type (4)
                if(count($inventory->inventoryRelationshipsWithoutInventory)){
                    foreach($inventory->inventoryRelationshipsWithoutInventory as $relationship_inventory){
                        $relationship_inventory->delete();
                        
                        $properties['old'] = $relationship_inventory;
                        $notes = "Changed Status Item";
                        $logService->deleteLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $notes);
                    }
                }
                return ["success" => true, "message" => "Status Pemakaian Inventory Berhasil Diubah", "status" => 200];
            }

            $relationship = Relationship::where('inverse_relationship_type', "Digunakan Oleh")->firstOrCreate([
                'relationship_type' => 'Menggunakan',
                'inverse_relationship_type' => 'Digunakan Oleh',
                'description' => null,
            ]);
            
            $relationship_inventory = new RelationshipInventory;
            $relationship_inventory->relationship_id = $relationship->id;
            $relationship_inventory->type_id = $relationship_type_id;
            $relationship_inventory->subject_id = $inventory->id;
            $relationship_inventory->connected_id = $connected_id;
            $relationship_inventory->is_inverse = false;
            $relationship_inventory->save();

            $properties = [];
            $properties['attributes'] = $relationship_inventory;
            $logService->createLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $notes);
            
            return ["success" => true, "message" => "Status Pemakaian Inventory Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function importInventories($request, $route_name)
    {
        // $excel = Excel::import(new InventoriesImport, $request->file('file'));
        try {
            Excel::import(new InventoriesImport, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $raw_failures = $e->failures();
            
            $failures = [];
            foreach ($raw_failures as $failure) {
                $temp['no'] = $failure->row()-1; // row that went wrong
                // $temp['attribute'] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $temp['errors'] = $failure->errors(); // Actual error messages from Laravel validator
                // $temp['values'] = $failure->values(); // The values of the row that has failed.
                $failures[] = $temp;
            }
            return ["success" => true, "message" => "Import Gagal", "failures" => $failures, "status" => 200];
        }
        return ["success" => true, "message" => "Inventory Berhasil Diimport", "status" => 200];
    }

    // Manufacturers

    public function getManufacturers($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $manufacturers = Manufacturer::get();
            if($manufacturers->isEmpty()) return ["success" => false, "message" => "Manufacturer Belum dibuat", "data" => [], "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $manufacturers, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addManufacturer($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $manufacturer = new Manufacturer;
        $manufacturer->name = $data['name'];
        try{
            $manufacturer->save();
            return ["success" => true, "message" => "Manufacturer berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateManufacturer($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $manufacturer = Manufacturer::find($id);
        if($manufacturer === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $manufacturer->name = $data['name'];
        try{
            $manufacturer->save();
            return ["success" => true, "message" => "Manufacturer berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteManufacturer($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $manufacturer = Manufacturer::find($id);
        if($manufacturer === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $manufacturer->delete();
            return ["success" => true, "message" => "Manufacturer berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Vendor

    public function getVendors(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $vendors = Vendor::all();
            if($vendors->isEmpty()) return ["success" => false, "message" => "Vendor Account Belum Terdaftar", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $vendors, "status" => 200];
            
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addVendor(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $vendor = new Vendor;
        $vendor->name = $request->get('name');
        $vendor->singkatan_nama = $request->get('singkatan_nama');
        $vendor->npwp = $request->get('npwp');
        $vendor->pic = $request->get('pic');
        $vendor->jabatan_pic = $request->get('jabatan_pic');
        $vendor->alamat = $request->get('alamat');
        $vendor->provinsi = $request->get('provinsi');
        $vendor->kab_kota = $request->get('kab_kota');
        $vendor->kode_pos = $request->get('kode_pos');
        $vendor->telepon = $request->get('telepon');
        $vendor->fax = $request->get('fax');
        $vendor->email = $request->get('email');
        $vendor->website = $request->get('website');
        try{
            $vendor->save();
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateVendor(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $vendor = Vendor::find($id);
        if($vendor === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $vendor->name = $request->get('name');
        $vendor->singkatan_nama = $request->get('singkatan_nama');
        $vendor->npwp = $request->get('npwp');
        $vendor->pic = $request->get('pic');
        $vendor->jabatan_pic = $request->get('jabatan_pic');
        $vendor->alamat = $request->get('alamat');
        $vendor->provinsi = $request->get('provinsi');
        $vendor->kab_kota = $request->get('kab_kota');
        $vendor->kode_pos = $request->get('kode_pos');
        $vendor->telepon = $request->get('telepon');
        $vendor->fax = $request->get('fax');
        $vendor->email = $request->get('email');
        $vendor->website = $request->get('website');
        try{
            $vendor->save();
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteVendor(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $vendor = Vendor::find($id);
        if($vendor === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $vendor->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    // Relationship
    // Relationship Type

    public function getRelationships($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationships = Relationship::withCount('relationshipInventories')->get();
            if($relationships->isEmpty()) return ["success" => false, "message" => "Relationship Belum dibuat", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationships, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRelationship($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationship = new Relationship;
            $relationship->relationship_type = $data['relationship_type'];
            $relationship->inverse_relationship_type = $data['inverse_relationship_type'];
            $relationship->description = $data['description'];
            $relationship->save();
            return ["success" => true, "message" => "Relationship berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRelationship($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $relationship = Relationship::find($data['id']);
        if($relationship === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        try{
            $relationship->relationship_type = $data['relationship_type'];
            $relationship->inverse_relationship_type = $data['inverse_relationship_type'];
            $relationship->description = $data['description'];
            $relationship->save();
            return ["success" => true, "message" => "Relationship berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRelationship($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $relationship = Relationship::withCount('relationshipInventories')->find($id);
        if($relationship === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if($relationship->relationship_assets_count) return ["success" => false, "message" => "Relationship Masih Digunakan Relationship Inventory", "status" => 400];
        try{
            $relationship->delete();
            return ["success" => true, "message" => "Relationship berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Relationship Inventory

    public function getCompanyRelationshipInventory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $company = Company::find($id);
        if($company === null) return ["success" => false, "message" => "Company Id Tidak Ditemukan", "status" => 400];

        $rows = $request->get('rows', 10);

        if($rows < 1) $rows = 10;
        if($rows > 100) $rows = 10;

        $companyService = new CompanyService;
        $list_company = $companyService->checkCompanyList($company);
        $relationship_inventories = RelationshipInventory::with('relationship:id,relationship_type,inverse_relationship_type', 'inventory')
            ->whereIn('connected_id', $list_company)->where('type_id',-3)->select('id', 'subject_id','relationship_id', 'connected_id', 'is_inverse')->paginate($rows);

        foreach($relationship_inventories as $relationship_inventory){
            $relationship_inventory->location_name = $relationship_inventory->inventory->locationInventory->fullSubNameWParent();
            $relationship_inventory->inventory->makeHidden('locationInventory');
            $relationship_inventory->model_name = $relationship_inventory->inventory->modelInventory->name;
            $relationship_inventory->relationship_name = !$relationship_inventory->is_inverse ? $relationship_inventory->relationship->inverse_relationship_type : $relationship_inventory->relationship->relationship_type;
            
            $temp_subject_id = $relationship_inventory->subject_id;
            $relationship_inventory->subject_id = $relationship_inventory->connected_id;
            $relationship_inventory->connected_id = $temp_subject_id;
            $relationship_inventory->makeHidden('inventory', 'relationship');
            $relationship_inventory->from_inverse = true;
        }
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship_inventories, "status" => 200];
    }

    public function getRelationshipInventory($data, $route_names)
    {
        $type_id = (int)$data['type_id'];
        if($type_id === -1) $route_name = $route_names[1];
        else if($type_id === -2) $route_name = $route_names[2];
        else if($type_id === -3) $route_name = $route_names[3];
        else $route_name = $route_names[0];
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $data['id'];
            $relationship_inventories_from_inverse = RelationshipInventory::with(['relationship', 'inventory'])->where('connected_id', $id)->where('type_id', $type_id)->get();
            if($type_id === -4){
                $relationship_inventories_not_from_inverse = RelationshipInventory::with(['relationship', 'inventory'])->where('subject_id', $id)->get();
                if(count($relationship_inventories_not_from_inverse)){
                    foreach($relationship_inventories_not_from_inverse as $relationship_inventory){
                        $relationship_inventory->relationship_name = $relationship_inventory->is_inverse ? $relationship_inventory->relationship->inverse_relationship_type : $relationship_inventory->relationship->relationship_type;
                        $relationship_inventory->type = $relationship_inventory->type_id === -1 ? "Agent" : ($relationship_inventory->type_id === -2 ? "Requester" : ($relationship_inventory->type_id === -3 ? "Company" : "Inventory Type"));
                        $relationship_inventory->subject_detail_name = $relationship_inventory->inventory->modelInventory->name;

                        if($relationship_inventory->connected_id === null){
                            $relationship_inventory->connected_detail_name = "Detail ID Kosong";
                        } else {
                            if($relationship_inventory->type_id === -1 || $relationship_inventory->type_id === -2){
                                $relationship_inventory->connected_detail_name = $relationship_inventory->user->id && ($relationship_inventory->user->role === $relationship_inventory->type_id * -1) ? $relationship_inventory->user->name : "User Not Found";
                                $relationship_inventory->makeHidden('user');
                            } else if($relationship_inventory->type_id === -3){
                                if($relationship_inventory->company->id){
                                    if($relationship_inventory->company->role === 4) $relationship_inventory->connected_detail_name = $relationship_inventory->company->fullSubNameWParent();
                                    else $relationship_inventory->connected_detail_name = $relationship_inventory->company->fullName(); 
                                } else $relationship_inventory->connected_detail_name = "Company Not Found";
                                $relationship_inventory->makeHidden('company');
                            } else {
                                $relationship_inventory->connected_detail_name = $relationship_inventory->inventoryConnected->modelInventory->name;
                                $relationship_inventory->makeHidden('inventoryConnected');
                            }
                        }
                        $relationship_inventory->from_inverse = false;
                        $relationship_inventory->makeHidden('inventory', 'relationship');
                    }
                }
            } else {
                $relationship_inventories_not_from_inverse = [];
            }
            
            if(count($relationship_inventories_from_inverse)){

                foreach($relationship_inventories_from_inverse as $relationship_inventory){
                    $relationship_inventory->relationship_name = $relationship_inventory->is_inverse ? $relationship_inventory->relationship->inverse_relationship_type : $relationship_inventory->relationship->relationship_type;
                    $relationship_inventory->type = $relationship_inventory->type_id === -1 ? "Agent" : ($relationship_inventory->type_id === -2 ? "Requester" : ($relationship_inventory->type_id === -3 ? "Company" : "Inventory Type"));
                    $relationship_inventory->connected_detail_name = $relationship_inventory->inventory->modelInventory->name;
                    
                    if($relationship_inventory->connected_id === null){
                        $relationship_inventory->subject_detail_name = "Detail ID Kosong";
                    } else {
                        if($relationship_inventory->type_id === -1 || $relationship_inventory->type_id === -2){
                            $relationship_inventory->subject_detail_name = $relationship_inventory->user->id && ($relationship_inventory->user->role === $relationship_inventory->type_id * -1) ? $relationship_inventory->user->name : "User Not Found";
                            $relationship_inventory->makeHidden('user');
                        } else if($relationship_inventory->type_id === -3){
                            if($relationship_inventory->company->id){
                                if($relationship_inventory->company->role === 4) $relationship_inventory->subject_detail_name = $relationship_inventory->company->fullSubNameWParent();
                                else $relationship_inventory->subject_detail_name = $relationship_inventory->company->fullName(); 
                            } else $relationship_inventory->subject_detail_name = "Company Not Found";
                            $relationship_inventory->makeHidden('company');
                        } else {
                            $relationship_inventory->subject_detail_name = $relationship_inventory->inventoryConnected->modelInventory->name;
                            $relationship_inventory->makeHidden('inventoryConnected');
                        }
                    }
                    $temp_subject_id = $relationship_inventory->subject_id;
                    $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                    $relationship_inventory->connected_id = $temp_subject_id;
                    $relationship_inventory->makeHidden('inventory', 'relationship');
                    $relationship_inventory->from_inverse = true;
                }
            }

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["from_inverse" => $relationship_inventories_from_inverse, "not_from_inverse" => $relationship_inventories_not_from_inverse], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRelationshipInventoryRelation($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationships = Relationship::get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationships, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRelationshipInventoryDetailList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $type_id = (int)$request->get('type_id', null);
        try{
            if($type_id === -1 || $type_id === -2){  
                $name = $request->get('name', null);   
                $role_checker = $type_id * -1;       
                $userService = new UserService;
                $users = $userService->getUserListRoles($role_checker, $name);
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $users, "status" => 200];
            } else if($type_id === -3){
                $this->companyService = new CompanyService;
                $companies = $this->companyService->getCompanyTreeSelect(1, 'clientChild');
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
            } else if($type_id === -4){
                $rows = $request->get('rows', 10);
                $asset_id = $request->get('asset_id', null);
                $model_id = $request->get('model_id', null);

                if($rows < 1) $rows = 10;
                if($rows > 100) $rows = 10;
                
                $inventories = Inventory::with('modelInventory.asset:id,name,code,deleted_at')->select('id','mig_id','serial_number','status_usage','model_id')->where('status_usage', 2);

                if($model_id){
                    $inventories = $inventories->where('model_id', $model_id);
                }
                if($asset_id){
                    $inventories = $inventories->whereHas('modelInventory', function($q) use ($asset_id){
                        $q->where('model_inventories.asset_id', $asset_id);
                    });
                }
                
                $inventories = $inventories->paginate($rows);
                foreach($inventories as $inventory){
                    $inventory->modelInventory->asset->asset_name = $inventory->modelInventory->asset->name;
                    if(strlen($inventory->modelInventory->asset->code) > 3){
                        $parent_model = substr($inventory->modelInventory->asset->code, 0, 3);
                        $parent = Asset::where('code', $parent_model)->first();
                        $parent_name = $parent === null ? "Asset Not Found" : $parent->name;
                        $inventory->modelInventory->asset->asset_name = $parent_name . " / " . $inventory->modelInventory->asset->name;
                    }
                }
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
            } else {
                $this->companyService = new CompanyService;
                $companies = $this->companyService->getCompanyTreeSelect($type_id, 'subChild');
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRelationshipInventories($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $relationship_id = $request->get('relationship_id', null);
        $relationship = Relationship::find($relationship_id);
        if($relationship === null) return ["success" => false, "message" => "Relationship Id Tidak Ditemukan", "status" => 400];
        
        $connected_ids = $request->get('connected_ids', null);
        $type_id = $request->get('type_id', null);
        $subject_id = $request->get('subject_id', null);
        $is_inverse = $request->get('is_inverse', null);
        $from_inverse = $request->get('from_inverse', false);
        $notes = $request->get('notes', null);
        try{
            if($from_inverse){
                foreach($connected_ids as $connected_id){
                    $relationship_inventory = new RelationshipInventory;
                    $relationship_inventory->relationship_id = $relationship_id;
                    $relationship_inventory->type_id = $type_id;
                    $relationship_inventory->subject_id = $connected_id;
                    $relationship_inventory->is_inverse = $is_inverse;
                    $relationship_inventory->connected_id = $subject_id;
                    $relationship_inventory->save();

                    if($type_id === -3) $notes = "Created From Company Management";
                    else $notes = "Created From User Management";
                    
                    $causer_id = auth()->user()->id; 
                    $logService = new LogService;
                    $properties['attributes'] = $relationship_inventory;
                    $logService->createLogInventoryRelationship($connected_id, $causer_id, $properties, $notes);
                }
            } else {
                foreach($connected_ids as $connected_id){
                    $relationship_inventory = new RelationshipInventory;
                    $relationship_inventory->relationship_id = $relationship_id;
                    $relationship_inventory->type_id = $type_id;
                    $relationship_inventory->subject_id = $subject_id;
                    $relationship_inventory->is_inverse = $is_inverse;
                    $relationship_inventory->connected_id = $connected_id;
                    $relationship_inventory->save();

                    $causer_id = auth()->user()->id; 
                    $logService = new LogService;
                    $properties['attributes'] = $relationship_inventory;
                    $logService->createLogInventoryRelationship($subject_id, $causer_id, $properties, $notes);

                    if($type_id === -4){
                        $inverse_notes = "Created from Inverse";
                        $temp_subject_id = $relationship_inventory->subject_id;
                        $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                        $relationship_inventory->connected_id = $temp_subject_id;
                        $relationship_inventory->is_inverse = !$relationship_inventory->is_inverse;
                        $properties['attributes'] = $relationship_inventory;
                        $logService->createLogInventoryRelationship($connected_id, $causer_id, $properties, $inverse_notes);
                    }
                }
            }
            return ["success" => true, "message" => "Relationship Inventory berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRelationshipInventory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $from_inverse = $request->get('from_inverse', null);
        $relationship_id = $request->get('relationship_id', null);
        $notes = $request->get('notes', null);
        $connected_id = $request->get('connected_id', null);
        $is_inverse = $request->get('is_inverse', null);
        
        $relationship_inventory = RelationshipInventory::find($id);
        if($relationship_inventory === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        $relationship = Relationship::find($relationship_id);
        if($relationship === null) return ["success" => false, "message" => "Relationship Id Tidak Ditemukan", "status" => 400];
        
        $causer_id = auth()->user()->id; 
        $old_relationship_inventory = [];
        foreach($relationship_inventory->getAttributes() as $key => $value) $old_relationship_inventory[$key] = $value;
        
        $relationship_inventory->relationship_id = $relationship_id;

        $logService = new LogService;
        if($from_inverse){
            if($connected_id !== $relationship_inventory->subject_id){
                $properties['old'] = $old_relationship_inventory;
                $temp_notes = "Item Has Been Changed Into Item with Id ". $connected_id;
                $logService->deleteLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $temp_notes);
                $relationship_inventory->subject_id = $connected_id;
                $relationship_inventory->is_inverse = !$is_inverse;
                $new_properties['attributes'] = $relationship_inventory;
                $logService->createLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $new_properties, $notes);
            } else {
                $relationship_inventory->subject_id = $connected_id;
                $relationship_inventory->is_inverse = !$is_inverse;
                $properties['old'] = $old_relationship_inventory;
                $properties['attributes'] = $relationship_inventory;
                $logService->updateLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $notes);
            }
        } else {
            $relationship_inventory->connected_id = $connected_id;
            $relationship_inventory->is_inverse = $is_inverse;
            $properties['old'] = $old_relationship_inventory;
            $properties['attributes'] = $relationship_inventory;
            $logService->updateLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $notes);
        } 
        try{
            $relationship_inventory->save();

            // Inverse Logs
            if($relationship_inventory->type_id === -4){
                if($from_inverse){
                    $properties = [];
                    $temp_subject_id = $old_relationship_inventory['subject_id'];
                    $old_relationship_inventory['subject_id'] = $old_relationship_inventory['connected_id'];
                    $old_relationship_inventory['connected_id'] = $temp_subject_id;
                    $old_relationship_inventory['is_inverse'] = !$old_relationship_inventory['is_inverse'];
                    $properties['old'] = $old_relationship_inventory;
                    
                    $temp_subject_id = $relationship_inventory->subject_id;
                    $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                    $relationship_inventory->connected_id = $temp_subject_id;
                    $relationship_inventory->is_inverse = !$relationship_inventory->is_inverse;
                    $properties['attributes'] = $relationship_inventory;
    
                    $inverse_notes = "Updated when inverse made some updates";
                    // return ["success" => true, "message" => $relationship_inventory->subject_id, "status" => 200];
                    $logService->updateLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $inverse_notes);
                } else {
                    if($connected_id !== $old_relationship_inventory['connected_id']){
                        $properties = [];
                        $inverse_notes = "Deleted when inverse made some updates";
                        $temp_subject_id = $old_relationship_inventory['subject_id'];
                        $old_relationship_inventory['subject_id'] = $old_relationship_inventory['connected_id'];
                        $old_relationship_inventory['connected_id'] = $temp_subject_id;
                        $old_relationship_inventory['is_inverse'] = !$old_relationship_inventory['is_inverse'];
                        $properties['old'] = $old_relationship_inventory;
                        $logService->deleteLogInventoryRelationship($old_relationship_inventory['subject_id'], $causer_id, $properties, $inverse_notes);
        
                        $properties = [];
                        $inverse_notes = "Created when inverse made some updates";
                        $temp_subject_id = $relationship_inventory->subject_id;
                        $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                        $relationship_inventory->connected_id = $temp_subject_id;
                        $relationship_inventory->is_inverse = !$relationship_inventory->is_inverse;
                        $properties['attributes'] = $relationship_inventory;
                        $logService->createLogInventoryRelationship($relationship_inventory->connected_id, $causer_id, $properties, $inverse_notes);
                    } else {
                        $properties = [];
                        $inverse_notes = "Updated when inverse made some updates";
                        $temp_subject_id = $old_relationship_inventory['subject_id'];
                        $old_relationship_inventory['subject_id'] = $old_relationship_inventory['connected_id'];
                        $old_relationship_inventory['connected_id'] = $temp_subject_id;
                        $old_relationship_inventory['is_inverse'] = !$old_relationship_inventory['is_inverse'];
                        $properties['old'] = $old_relationship_inventory;
        
                        $temp_subject_id = $relationship_inventory->subject_id;
                        $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                        $relationship_inventory->connected_id = $temp_subject_id;
                        $relationship_inventory->is_inverse = !$relationship_inventory->is_inverse;
                        $properties['attributes'] = $relationship_inventory;
                        $logService->updateLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $inverse_notes);
                    }
                } 
            }
            

            
            return ["success" => true, "message" => "Relationship Inventory berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRelationshipInventory($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $id = $data['id'];
        $notes = $data['notes'];
        $relationship_inventory = RelationshipInventory::find($id);
        if($relationship_inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 200];
        try{
            $relationship_inventory->delete();
            $causer_id = auth()->user()->id; 
            $properties['old'] = $relationship_inventory;
            $logService = new LogService;
            $logService->deleteLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $notes);
            if($relationship_inventory->type_id === -4){
                $inverse_notes = "Deleted from Inverse";
                $temp_subject_id = $relationship_inventory->subject_id;
                $relationship_inventory->subject_id = $relationship_inventory->connected_id;
                $relationship_inventory->connected_id = $temp_subject_id;
                $relationship_inventory->is_inverse = !$relationship_inventory->is_inverse;
                $properties['old'] = $relationship_inventory;
                $logService->deleteLogInventoryRelationship($relationship_inventory->subject_id, $causer_id, $properties, $inverse_notes);
            }
            return ["success" => true, "message" => "Relationship Inventory berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}