<?php 

namespace App\Services;
use App\Services\CheckRouteService;
use App\Services\CompanyService;
use App\Services\UserService;
use App\Asset;
use App\AssetColumn;
use App\Company;
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
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class AssetService{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
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
        $assets = Asset::where('code', 'not like', "%.%")->orderBy('code')->get();
        if($assets->isEmpty()) return $assets;
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
        return $new_assets;
    }

    public function getAssets($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $asset = Asset::find($id);
            if($asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $asset_columns = AssetColumn::where('asset_id', $id)->get();
            $asset->asset_columns = $asset_columns->makeHidden('deleted_at');
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
                    return $parent.".00".$new_string;
                } else if($new_number < 100) {
                    return $parent.".0".$new_string;
                } else {
                    return $parent.".".$new_string;
                }
            } else {
                return $parent.".001";
            }
        } else {
            $assets = Asset::where('code', 'not like', "%.%")->orderBy('code', 'desc')->get();
            if(count($assets)){
                $new_number = (int)$assets->first()->code + 1;
                $new_string = (string)$new_number;
                if($new_number < 10) {
                    return "00".$new_string;
                } else if($new_number < 100) {
                    return "0".$new_string;
                } else {
                    return $new_string;
                }
            } else {
                return "001";
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
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $asset = new Asset;
        $asset->name = $data['name'];
        $asset->description = $data['description'];
        $asset->required_sn = $data['required_sn'];
        $parent = $data['parent'];
        try{
            $asset->code = $this->registerCodeAsset($parent);
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
        if($parent === $check_old_code) return ["success" => false, "message" => "Code Parent Sama dengan Code Asset yang Ingin Diubah", "status" => 400];
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
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $name = $data['name'];
        $parent = $data['parent'];
        $description = $data['description'];
        $required_sn = $data['required_sn'];
        $action = $data['action'];
        $is_deleted = $data['is_deleted'];
        $add_columns = $data['add_columns'];
        $update_columns = $data['update_columns'];
        $delete_column_ids = $data['delete_column_ids'];
        try{
            $asset = Asset::find($id);
            if($asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $check_old_code = $asset->code;
            $check_old_code_length = strlen($check_old_code);
            $check_code = $this->registerUpdateCodeAsset($parent, $check_old_code, $check_old_code_length);
            if(!$check_code["success"]) return $check_code;
            $asset->code = $check_code['new_code'];
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
        // return $assets;
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
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $new_parent = $data['new_parent'];
        $new_model_asset_id = $data['new_model_asset_id'];
        $check_new_parent = $this->checkCodeString($new_parent);
        if(!$check_new_parent["success"]) return $check_new_parent;
        
        $core_asset = Asset::find($id);
        if($core_asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 200];
        $old_code = $core_asset->code;
        try{
            // return ["success" => false, "message" => $this->actionChildDelete($old_code, $new_parent, $new_model_asset_id, $id), "status" => 200];
            $this->actionChildDelete($old_code, $new_parent, $new_model_asset_id, $id);
            $core_asset->delete();
            $this->deleteAssetColumn($id);
            

            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    // Models

    public function getChildModel($model_part, $models, $pivots, $model_columns, $assets){
        $search = array_search($model_part['child_id'], array_column($models, 'id'));
        if($search !== false){
            $model = $models[$search];
            $model_part['name'] = $model['name'];
            $model_parts = [];

            foreach($pivots as $pivot){
                if($pivot['parent_id'] === $model_part['child_id']){
                    $model_parts[] = $this->getChildModel($pivot, $models, $pivots, $model_columns, $assets);
                }
            }
            $temp_model_columns = [];
            foreach($model_columns as $model_column){
                if($model_column['model_id'] === $model_part['child_id']){
                    $temp_model_columns[] = $model_column;
                }
            }

            $search_pivot = array_search($model['asset_id'], array_column($assets, 'id'));
            if($search_pivot !== false) $asset_name = $assets[$search_pivot]['name'];

            $model_part['asset_name'] = $asset_name ? $asset_name : "Asset Tidak Ditemukan";
            $model_part['model_column'] = $temp_model_columns;
            $model_part['model_parts'] = $model_parts;
            return $model_part;
        } else {
            $template = ['id' => 0, "parent_id" => $model_part['parent_id'], "child_id" => $model_part['child_id'], "quantity" => 0, "deleted_at" => null, "name" => "Model Tidak Ditemukan", "model_column" => [], "model_parts" => []];
            return $template;   
        }
    }

    public function getModels($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $models = ModelInventory::get();
        if($models->isEmpty()) return ["success" => true, "message" => "Model Belum Terisi", "data" => [], "status" => 400];
        $inventories = Inventory::select('model_id')->get();
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
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $models, "status" => 200];
    }

    public function getModel($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $model = ModelInventory::find($id);
            if($model === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
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
            $model->manufacturer = Manufacturer::withTrashed()->find($model->manufacturer_id);
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
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $model, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getModelRelations($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
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

    public function createModelParts($model_parts, $id)
    {
        if(count($model_parts)){
            foreach($model_parts as $model_part){
                $new_model_part = new ModelModelPivot;
                $new_model_part->parent_id = $id;
                $new_model_part->child_id = $model_part['id'];
                $new_model_part->quantity = $model_part['quantity'];
                $new_model_part->save();
            }
        }
    }

    public function addModel($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $name = $data['name'];
        $check_name = ModelInventory::where('name', $name)->first();
        if($check_name !== null) return ["success" => false, "message" => "Nama Model Telah Terdaftar", "status" => 200];
        $model = new ModelInventory;
        $model->asset_id = $data['asset_id'];
        $model->name = $name;
        $model->description = $data['description'];
        $model->manufacturer_id = $data['manufacturer_id'];
        $model->required_sn = $data['required_sn'];
        try{
            $model->save();
            $model_columns = $data['model_columns'];
            $this->createModelColumns($model_columns, $model->id);
            $model_parts = $data['model_parts'];
            $this->createModelParts($model_parts, $model->id);
            
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

    public function deleteModelParts($delete_model_ids, $id)
    {
        $model_pivots = ModelModelPivot::where('parent_id', $id)->get();
        if(count($delete_model_ids)){
            foreach($delete_model_ids as $delete_model_id){
                $deleted_model = $model_pivots->where('child_id', $delete_model_id)->first();
                if($deleted_model !== null) $deleted_model->delete();
            }
        }
    }

    public function updateModel($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $model = ModelInventory::find($id);
        if($model === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status"=> 400];
        $model->asset_id = $data['asset_id'];
        $model->name = $data['name'];
        $model->description = $data['description'];
        $model->manufacturer_id = $data['manufacturer_id'];
        $model->required_sn = $data['required_sn'];
        try{
            $delete_column_ids = $data['delete_column_ids'];
            $update_columns = $data['update_columns'];
            $check_update_delete_model_column = $this->updateDeleteModelColumn($id, $update_columns, $delete_column_ids);
            if(!$check_update_delete_model_column['success']) return $check_update_delete_model_column;

            $add_columns = $data['add_columns'];
            $this->createModelColumns($add_columns, $id);
            
            $delete_model_ids = $data['delete_model_ids'];
            $this->deleteModelParts($delete_model_ids, $id);
            
            $add_models = $data['add_models'];
            $this->createModelParts($add_models, $id);

            $model->save();
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteComponentModel($id)
    {
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
    }

    public function deleteModel($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model = ModelInventory::find($id);
        if($model === null) return ["success" => false, "message" => "Id Model Tidak Ditemukan", "status" => 400];
        try{
            $this->deleteComponentModel($id);
            $model->delete();
            return ["success" => true, "message" => "Data Berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventoryRelations($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $models = ModelInventory::withTrashed()->select('id','name','asset_id','deleted_at')->get();
            $manufacturers = Manufacturer::withTrashed()->select('id','name','deleted_at')->get();
            $assets = Asset::withTrashed()->select('id','name','deleted_at')->get();
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
            $companies = Company::select('company_id AS id', 'company_name AS name')->where('role', '<>', 2)->get();
            
            $this->companyService = new CompanyService;
            $tree_companies = $this->companyService->getCompanyTreeSelect(auth()->user()->company_id)['data'];
            
            $data = (object)['models' => $models, 'assets' => $assets, 'vendors' => $vendors, 'manufacturers' => $manufacturers, 'status_condition' => $status_condition, 'status_usage' => $status_usage, 'companies' => $companies, 'tree_companies' => $tree_companies];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventories($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $assets = Asset::withTrashed()->select('id','name','deleted_at')->get();
            $models = ModelInventory::withTrashed()->select('id','name', 'asset_id','deleted_at')->get();
            $inventories = Inventory::select('id', 'model_id', 'status_condition', 'status_usage', 'inventory_name')->get();
            foreach($inventories as $inventory){
                $model = $models->where('id', $inventory->model_id)->first();
                if($model === null){
                    $inventory->model_name = "Id Model Tidak Ditemukan";
                    $inventory->model_deleted_at = "Id Model Tidak Ditemukan";
                    $inventory->asset_name = "Id Model Tidak Ditemukan";
                    $inventory->asset_deleted_at = "Id Model Tidak Ditemukan";
                } else {
                    $inventory->model_name = $model->name;
                    $inventory->model_deleted_at = $model->deleted_at;
                    $asset = $assets->where('id', $model->asset_id)->first();
                    if($asset === null) $inventory->asset_name = "Id Aset Tidak Ditemukan";
                    else {
                        $inventory->asset_name = $asset->name;
                        $inventory->asset_deleted_at = $asset->deleted_at;
                    } 
                } 
               
            }
            if($inventories->isEmpty()) return ["success" => true, "message" => "Data Inventory Belum Terisi", "data" => [], "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventoryParts($pivot, $pivots, $inventories, $models, $assets){
        $search = array_search($pivot['child_id'], array_column($inventories, 'id'));
        if($search !== false){
            $inventory = $inventories[$search];
            $search_model = array_search($inventory['model_id'], array_column($models, 'id'));
            if($search_model !== false){
                $temp_model = $models[$search_model];
                $search_asset = array_search($temp_model['asset_id'], array_column($assets, 'id'));
                if($search_asset !== false){
                    $temp_asset = $models[$search_asset];
                } else {
                    $temp_asset['name'] = "Asset Tidak Ditemukan";
                    $temp_asset['id'] = 0;
                    $temp_asset['deleted_at'] = null;
                }
            } else {
                $temp_model['name'] = "Model Tidak Ditemukan";
                $temp_model['id'] = 0;
                $temp_model['deleted_at'] = null;
                $temp_asset['name'] = "Model Tidak Ditemukan";
                $temp_asset['id'] = 0;
                $temp_asset['deleted_at'] = null;
            }
            $inventory_parts = [];
            foreach($pivots as $p){
                if($p['parent_id'] === $pivot['child_id']){
                    $inventory_parts[] = $this->getInventoryParts($p, $pivots, $inventories, $models, $assets);
                }
            }
            $data = [
                'id' => $inventory['id'],
                'inventory_name' => $inventory['inventory_name'],
                'status_usage' => $inventory['status_usage'],
                'mig_id' => $inventory['mig_id'],
                'model_id' => $temp_model['id'],
                'model_name' => $temp_model['name'],
                'model_deleted_at' => $temp_model['deleted_at'],
                'asset_id' => $temp_asset['id'],
                'asset_name' => $temp_asset['name'],
                'asset_deleted_at' => $temp_asset['deleted_at'],
                'inventory_parts' => $inventory_parts
            ];
        } else {
            $data = [
                'id' => 0,
                'inventory_name' => "Inventory Tidak Ditemukan",
                'status_usage' => "Inventory Tidak Ditemukan",
                'mig_id' => "Inventory Tidak Ditemukan",
                'model_id' => "Inventory Tidak Ditemukan",
                'model_name' => "Inventory Tidak Ditemukan",
                'model_deleted_at' => null,
                'asset' => "Inventory Tidak Ditemukan",
                'asset_id' => 0,
                'asset_name' => "Inventory Tidak Ditemukan",
                'asset_deleted_at' => null,
                'inventory_parts' => []
            ];
        }
        return $data;
    }

    public function getInventory($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $model = ModelInventory::withTrashed()->find($inventory->model_id);
            if($model === null){
                $inventory->model_name = "Model Tidak Ditemukan";
                $inventory->asset_name = "Model Tidak Ditemukan";
            } else {
                $asset = Asset::withTrashed()->find($model->asset_id);
                if($asset === null) $inventory->asset_name = "Asset Tidak Ditemukan";
                else { 
                    $inventory->asset_name = $asset->name;
                    $inventory->asset_deleted_at = $asset->deleted_at;
                }
                $inventory->model_name = $model->name;
                $inventory->model_deleted_at = $asset->deleted_at;
            }
            $this->companyService = new CompanyService;
            $inventory->location_name = $this->companyService->findCompany($inventory->location);
            
            $all_inventory_values = InventoryValue::get();
            $model_inventory_columns = ModelInventoryColumn::get();
            $temp_values = $all_inventory_values->where('inventory_id',$id);
            
            $inventory_values = [];
            foreach($temp_values as $temp_value){
                $model_inventory_column = $model_inventory_columns->where('id', $temp_value->model_inventory_column_id)->first();
                if($model_inventory_column === null){
                    $temp_value->name = "Inventory Column Name not Found";
                    $temp_value->data_type = "Inventory Column Name not Found";
                    $temp_value->required = "Inventory Column Name not Found";
                    array_push($inventory_values, $temp_value);
                    continue;
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
                $all_inventories = Inventory::select('id', 'inventory_name', 'status_usage', 'mig_id','model_id')->get()->toArray();
                $models = ModelInventory::withTrashed()->select('id', 'name','asset_id','deleted_at')->get()->toArray();
                $assets = Asset::withTrashed()->select('id', 'name','deleted_at')->get()->toArray();
                $pivots = $pivots->toArray();
                $inventory_parts = [];
                if(count($core_pivots)){
                    foreach($core_pivots as $pivot){
                        $inventory_parts[] = $this->getInventoryParts($pivot, $pivots, $all_inventories, $models, $assets);
                    }
                }
                $inventory->inventory_parts = $inventory_parts;
            } else {
                $inventory->inventory_parts = [];
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $inventory, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getInventoryAddable($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $inventories = Inventory::select('id','inventory_name', 'model_id', 'status_usage','mig_id')->get();
        $models = ModelInventory::withTrashed()->select('id','name','asset_id','deleted_at')->get();
        $assets = Asset::withTrashed()->select('id','name','deleted_at')->get();
        $pivots = InventoryInventoryPivot::get();
        $inventories_array = $inventories->toArray();
        $models_array = $models->toArray();
        $assets_array = $assets->toArray();
        $pivots_array = $pivots->toArray();
        $inventory_in_stock = $inventories->where('status_usage', 2);
        $data = [];
        foreach($inventory_in_stock as $inventory){
            $pivot = $pivots->where('child_id', $inventory->id)->first();
            if($pivot) continue;
            $model = $models->find($inventory->model_id);
            if($model === null){
                $inventory->asset_id = 0;
                $inventory->asset_name = "Model Tidak Ditemukan";
            } else{
                $inventory->model_name = $model->name;
                $inventory->asset_id = $model->asset_id;
                $asset = $assets->where('id', $model->asset_id)->first();
                if($asset === null){
                    $inventory->asset_name = "Asset Tidak Ditemukan";
                } else {
                    $inventory->asset_name = $asset->name;
                }
            } 
            $core_pivots = $pivots->where('parent_id', $inventory->id);
            if(count($pivots)){
                $inventory_parts = [];
                if(count($core_pivots)){
                    foreach($core_pivots as $pivot){
                        $inventory_parts[] = $this->getInventoryParts($pivot, $pivots_array, $inventories_array, $models_array, $assets_array);
                    }
                }
                $inventory->inventory_parts = $inventory_parts;
            } else {
                $inventory->inventory_parts = [];
            }
            $data[] = $inventory;
        }
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200]; 
    }

    public function getInventoryReplacements($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $inventories = Inventory::select('id','inventory_name', 'model_id', 'status_usage','mig_id')->get();
            $models = ModelInventory::withTrashed()->select('id','name','asset_id', 'deleted_at')->get();
            $assets = Asset::withTrashed()->select('id','name', 'deleted_at')->get();
            $pivots = InventoryInventoryPivot::get();
            foreach($inventories as $inventory){
                $model = $models->find($inventory->model_id);
                if($model === null){
                    $inventory->asset_id = 0;
                    $inventory->asset_name = "Model Tidak Ditemukan";
                } else{
                    $inventory->model_name = $model->name;
                    $inventory->asset_id = $model->asset_id;
                    $asset = $assets->where('id', $model->asset_id)->first();
                    if($asset === null){
                        $inventory->asset_name = "Asset Tidak Ditemukan";
                    } else {
                        $inventory->asset_name = $asset->name;
                    }
                } 
            }
            $inventory_replacements = $inventories->where('asset_id', $id)->where('status_usage', 2);
            $pivots_array = $pivots->toArray();
            $inventories_array = $inventories->toArray();
            $models = $models->toArray();
            $assets = $assets->toArray();
            $datas = [];
            if(count($inventory_replacements)){
                foreach($inventory_replacements as $inventory_replacement){
                    $core_pivots = $pivots->where('parent_id', $inventory_replacement->id);
                    $check_parent = $pivots->where('child_id', $inventory_replacement->id)->first();
                    if(count($pivots)){
                        $inventory_parts = [];
                        if(count($core_pivots)){
                            foreach($core_pivots as $pivot){
                                $inventory_parts[] = $this->getInventoryParts($pivot, $pivots_array, $inventories_array, $models, $assets);
                            }
                        }
                        $inventory_replacement->inventory_parts = $inventory_parts;
                    } else {
                        $inventory_replacement->inventory_parts = [];
                    }
                    if($check_parent === null){
                        $inventory_replacement->parent_id = null;
                        $inventory_replacement->parent_name = "Tidak Memiliki Parent";
                    } 
                    else{
                        $parent_id = $check_parent->parent_id;
                        $inventory_replacement->parent_id = $parent_id;
                        $parent = $inventories->find($parent_id);
                        if($parent === null) $inventory_replacement->name = "Inventory Parent Tidak Ditemukan";
                        $inventory_replacement->parent_name = $parent->inventory_name;
                    } 
                    $datas[] = $inventory_replacement;
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $datas, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getChangeStatusUsageDetailList($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = (int)$id;
        try{
            if($id < -3) return ["success" => false, "message" => "Tipe Id Tidak Tepat", "status" => 400];
            if($id === -1 || $id === -2){     
                $role_checker = $id * -1;
                $userService = new UserService;
                $users = $userService->getUserList($role_checker, auth()->user()->company_id, true);
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $users, "status" => 200];
            } else if($id === -3){
                $this->companyService = new CompanyService;
                $client_company_list = $this->companyService->getClientCompanyList()['data'];
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $client_company_list, "status" => 200];
            } else {
                $this->companyService = new CompanyService;
                $client_company_list = $this->companyService->getLocations($id, true)['data'];
                
                $front_end_data = [$client_company_list];
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $front_end_data, "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function saveInventoryParts($inventory, $location, $parent_id, $causer_id)
    {
        $new_inventory = new Inventory;
        $new_inventory->model_id = $inventory['model_id'];
        $new_inventory->vendor_id = $inventory['vendor_id'];
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
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $causer_id;
            // $last_activity->causer_type = "Created as part of inventory with id ".$parent_id;
            // $last_activity->save();
            $pivot = new InventoryInventoryPivot;
            $pivot->parent_id = $parent_id;
            $pivot->child_id = $new_inventory->id;
            $pivot->save();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $causer_id;
            // $last_activity->save();

            if(count($inventory_values)){
                foreach($inventory_values as $inventory_value){
                    $model = new InventoryValue;
                    $model->inventory_id = $new_inventory->id;
                    $model->model_inventory_column_id = $inventory_value['model_inventory_column_id'];
                    $model->value = $inventory_value['value'];
                    $model->save();
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $causer_id;
                    // $last_activity->save();
                }
            } 
            if(count($inventory_parts)){
                foreach($inventory_parts as $inventory_part){
                    $this->saveInventoryParts($inventory_part, $location, $new_inventory->id, $causer_id);
                }
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function checkMigIdInventory($inventories, $inventory_part)
    {
        foreach($inventories as $inventory){
            if($inventory['mig_id'] === $inventory_part['mig_id']){
                return ["success" => false, "mig_id" => $inventory_part['mig_id']];
            }
        }
        return ["success" => true];
    }

    public function addInventory($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $mig_id = $data['mig_id'];
        $inventories = Inventory::select('id', 'inventory_name', 'mig_id')->get();
        $check_inventory = $inventories->where('mig_id', $mig_id)->first();
        if($check_inventory) return ["success" => false, "message" => "MIG ID ".$mig_id." Sudah Terdaftar", "status" => 400];
        $inventory_parts = $data['inventory_parts'];
        if(count($inventory_parts)){
            $inventories = $inventories->toArray();
            foreach($inventory_parts as $inventory_part){
                $result = $this->checkMigIdInventory($inventories, $inventory_part);
                if(!$result['success']){
                    return ["success" => false, "message" => "MIG ID ".$result['mig_id']." Sudah Terdaftar", "status" => 400];
                }
            }
        }
        $inventory = new Inventory;
        $inventory->model_id = $data['model_id'];
        $inventory->vendor_id = $data['vendor_id'];
        $inventory->inventory_name = $data['inventory_name'];
        $inventory->status_condition = $data['status_condition'];
        $inventory->status_usage = $data['status_condition'];
        $inventory->location = $data['location'];
        $inventory->is_exist = $data['is_exist'];
        $inventory->deskripsi = $data['deskripsi'];
        $inventory->manufacturer_id = $data['manufacturer_id'];
        $inventory->mig_id = $mig_id;
        $inventory->serial_number = $data['serial_number'];
        $inventory_values = $data['inventory_values'];
        $notes = $data['notes'];
        try{
            $inventory->save();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = $notes;
            // $last_activity->save();

            foreach($inventory_values as $inventory_value){
                $model = new InventoryValue;
                $model->inventory_id = $inventory->id;
                $model->model_inventory_column_id = $inventory_value['model_inventory_column_id'];
                $model->value = $inventory_value['value'];
                $model->save();
                // $last_activity = Activity::all()->last();
                // $last_activity->causer_id = $check['id'];
                // $last_activity->save();
                
            }
            if(count($inventory_parts)){
                foreach($inventory_parts as $inventory_part){
                    $this->saveInventoryParts($inventory_part, $inventory->location, $inventory->id, auth()->user()->user_id);
                }
            }
            return ["success" => true, "message" => "Inventory Berhasil Ditambah", "id" => $inventory->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateInventory($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $mig_id = $data['mig_id'];
        $notes = $data['notes'];
        $check_inventory = Inventory::where('mig_id', $mig_id)->first();
        if($check_inventory && $check_inventory->id !== $id) return ["success" => false, "message" => "MIG ID Sudah Terdaftar", "status" => 400];
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $inventory->vendor_id = $data['vendor_id'];
            $inventory->inventory_name = $data['inventory_name'];
            // $inventory->status_condition = $inventory->status_condition;
            // $inventory->status_usage = $inventory->status_usage;
            $inventory->location = $data['location'];
            $inventory->is_exist = $data['is_exist'];
            $inventory->deskripsi = $data['deskripsi'];
            $inventory->manufacturer_id = $data['manufacturer_id'];
            $inventory->mig_id = $mig_id;
            $inventory->serial_number = $data['serial_number'];
            $inventory->save();
            // $last_activity = Activity::all()->last();
            // if($last_activity->subject_id === $id){
            //     $last_activity->causer_id = $check['id'];
            //     $last_activity->causer_type = $notes;
            //     $last_activity->save();
            // }
            
            $new_inventory_values = $data['inventory_values'];
            $inventory_values = InventoryValue::get();

            foreach($new_inventory_values as $inventory_value){
                $new_value = $inventory_values->where('id', $inventory_value['id'])->first();
                if($new_value === null) return ["success" => false, "message" => "Id Inventory Value Tidak Ditemukan", "error_id" => $inventory_value['id'], "status" => 400];
                if($new_value->inventory_id !== $id) return ["success" => false, "message" => "Id Inventory Value Bukan Milik Inventory Ini", "error_id" => $inventory_value['id'], "status" => 400];
            }
            foreach($new_inventory_values as $inventory_value){
                $new_value = $inventory_values->where('id', $inventory_value['id'])->first();
                $new_value->value = $inventory_value['value'];
                $new_value->save();
                // $check_activity = Activity::all()->last();;
                // if (array_key_exists('inventory_id', $check_activity->properties['attributes'])) {
                //     if($check_activity->properties['attributes']['inventory_id'] === $id) {
                //         $check_activity->causer_id = $check['id'];
                //         $check_activity->causer_type = $notes;
                //         $check_activity->save();
                //     }
                // }
            }

            return ["success" => true, "message" => "Inventory Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function setStatusInventoryPartReplacements($pivot, $login_id, $status){
        $pivots = InventoryInventoryPivot::get();
        $inventory = Inventory::find($pivot['child_id']);
        $inventory->status_usage = $status;
        $inventory->save();
        // $last_activity = Activity::all()->last();
        // $last_activity->causer_id = $login_id;
        // $last_activity->save();
        $pivot_children = $pivots->where('parent_id', $pivot['child_id']);
        if(count($pivot_children)){
            foreach($pivot_children as $pivot_child){
                $this->setStatusInventoryPartReplacements($pivot_child, $login_id, $status);
            }
        }
    }

    public function replaceInventoryPart($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $replacement_id = $data['replacement_id'];
        $notes = $data['notes'];
        $causer_id = auth()->user()->user_id;
        try{
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Id Inventori yang akan Diganti Tidak Ditemukan", "status" => 400];
            $pivot_old_inventory = InventoryInventoryPivot::where('child_id', $id)->first();
            
            $inventory_replacement = Inventory::find($replacement_id);
            if($inventory_replacement === false) return ["success" => false, "message" => "Id Inventori Pengganti Tidak Ditemukan", "status" => 400];
            
            // if($inventory_replacement->model_id !== $inventory->model_id) return response()->json(["success" => false, "message" => "Model Kedua Inventori Tidak Sama", "status" => 400], 400);
            $pivots = InventoryInventoryPivot::get();
            $temp_status_usage = $inventory->status_usage;
            $inventory->status_usage = $inventory_replacement->status_usage;
            $inventory->save();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = $notes;
            // $last_activity->save();
            $pivot_children = $pivots->where('parent_id', $id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->setStatusInventoryPartReplacements($pivot_child, $causer_id, $inventory->status_usage);
                }
            }
            
            $inventory_replacement->status_usage = $temp_status_usage;
            $inventory_replacement->save();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = "Replacement of inventory with id ".$id;
            // $last_activity->save();
            $pivot_old_replacement = InventoryInventoryPivot::where('child_id', $replacement_id)->first();
            if($pivot_old_replacement === null){
                $remove_old_pivot = $pivots->where('child_id', $id)->first();
                $remove_old_pivot->delete();
                // $last_activity = Activity::all()->last();
                // $last_activity->causer_id = $check['id'];
                // $last_activity->causer_type = $notes;
                // $last_activity->save();
                $new_replacement_pivot = new InventoryInventoryPivot;
                $new_replacement_pivot->parent_id = $pivot_old_inventory->parent_id;
                $new_replacement_pivot->child_id = $replacement_id;
                $new_replacement_pivot->save();
                // $last_activity = Activity::all()->last();
                // $last_activity->causer_id = $check['id'];
                // $last_activity->causer_type = "Replacement of inventory with id ".$id;
                // $last_activity->save();
            } else {
                $parent_old_inventory = $pivot_old_inventory->parent_id;
                $pivot_old_inventory->parent_id = $pivot_old_replacement->parent_id;
                $pivot_old_inventory->save();
                // $last_activity = Activity::all()->last();
                // $last_activity->causer_id = $check['id'];
                // $last_activity->causer_type = $notes;
                // $last_activity->save();
                
                $pivot_old_replacement->parent_id = $parent_old_inventory;
                $pivot_old_replacement->save();
                // $last_activity = Activity::all()->last();
                // $last_activity->causer_id = $check['id'];
                // $last_activity->causer_type = "Replacement of inventory with id ".$id;
                // $last_activity->save();
                
            }
            
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->save();

            $pivot_children = $pivots->where('parent_id', $replacement_id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->setStatusInventoryPartReplacements($pivot_child, $causer_id, $inventory_replacement->status_usage);
                }
            }
            return ["success" => true, "message" => "Berhasil Melakukan Replacement Part Inventory", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
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
        // $last_activity = Activity::all()->last();
        // $last_activity->causer_id = $login_id;
        // $last_activity->save();
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

    public function removeInventoryPart($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $inventory_part_id = $data['inventory_part_id'];
        $causer_id = auth()->user()->user_id;
        try{
            $pivots = InventoryInventoryPivot::get();
            $array_pivots = $pivots->toArray();
            $check_parent = $this->checkParent($inventory_part_id, $id, $array_pivots);
            if($check_parent === false) return ["success" => false, "message" => "Id Part Tidak Termasuk dari Part yang Dimiliki Inventory Ini", "error_id" => $inventory_part_id, "status" => 400];
            
            $inventory = Inventory::find($inventory_part_id);
            $inventory->status_usage = 2;
            $inventory->save();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = $notes;
            // $last_activity->save();
            $remove_pivot = $pivots->where('child_id', $inventory_part_id)->first();
            $remove_pivot->delete();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = $notes;
            // $last_activity->save();
            $pivot_children = $pivots->where('parent_id', $inventory_part_id);
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $this->removeChildInventoryPart($pivot_child, $causer_id);
                    // $pivot_child->delete();
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $check['id'];
                    // $last_activity->save();
                }
            }
            return ["success" => true, "message" => "Berhasil Menghapus Part Inventory", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
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
        // $last_activity = Activity::all()->last();
        // $last_activity->causer_id = $login_id;
        // $last_activity->save();
        $pivot_children = $pivots->where('parent_id', $pivot['child_id']);
        if(count($pivot_children)){
            foreach($pivot_children as $pivot_child){
                $this->addChildInventoryPart($pivot_child, $login_id);
            }
        }
    }

    public function addInventoryParts($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $inventory_part_ids = $data['inventory_part_ids'];
        try{
            if(count($inventory_part_ids)){
                // foreach($inventory_part_ids as $inventory_part_id){
                //     $check_used = $this->checkUsed($inventory_part_id);
                //     if($check_used['exist']) return response()->json(["success" => false, "message" => "Part Id ".$inventory_part_id." sedang digunakan oleh Id ".$check_used['id']], 400);
                // }
                $pivots = InventoryInventoryPivot::get();
                foreach($inventory_part_ids as $inventory_part_id){
                    $inventory = Inventory::find($inventory_part_id);
                    if($inventory === null) return ["success" => false, "message" => "Id Inventory Tidak Terdaftar", "status" => 400];
                    // if($inventory->status_usage === 1)return response()->json(["success" => false, "message" => "Inventory Sedang Digunakan", "status" => 400], 400);
                    $inventory->status_usage = 1;
                    $inventory->save();
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $check['id'];
                    // $last_activity->causer_type = $notes;
                    // $last_activity->save();
                    
                    $pivot = $pivots->where('child_id', $inventory_part_id)->first();
                    if($pivot === null){
                        $pivot = new InventoryInventoryPivot;
                        $pivot->parent_id = $id;
                        $pivot->child_id = $inventory_part_id;
                        $pivot->save();
                    } else {
                        $pivot->parent_id = $id;
                        $pivot->save();
                    }
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $check['id'];
                    // $last_activity->causer_type = $notes;
                    // $last_activity->save();
                    

                    $pivot_children = $pivots->where('parent_id', $inventory_part_id);
                    if(count($pivot_children)){
                        foreach($pivot_children as $pivot_child){
                            $this->addChildInventoryPart($pivot_child, auth()->user()->user_id);
                        }
                    }
                }
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
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $inventory = Inventory::find($id);
        $causer_id = auth()->user()->user_id;
        if($inventory === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            // DB::table('inventories')->where('id', $inventory->id)->update(array('vendor_id' => $log_user_id));
            $inventory->delete();
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = $notes;
            // $last_activity->save();
            $inventory_values = InventoryValue::where('inventory_id', $id)->get();
            if(count($inventory_values)){
                foreach($inventory_values as $inventory_value){
                    $inventory_value->delete();
                    $last_activity = Activity::all()->last();
                    $last_activity->causer_id = $check['id'];
                    $last_activity->save();
                }
            }
            $pivots = InventoryInventoryPivot::get();
            $pivot_children = $pivots->where('parent_id', $id);
            $inventories = Inventory::get();
            if(count($pivot_children)){
                foreach($pivot_children as $pivot_child){
                    $pivot_child->delete();
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $check['id'];
                    // $last_activity->save();
                    $inventory = $inventories->where('id', $pivot_child->child_id)->first();
                    $inventory->status_usage = 2;
                    $inventory->save();
                    // $last_activity = Activity::all()->last();
                    // $last_activity->causer_id = $check['id'];
                    // $last_activity->save();
                    $this->removeChildInventoryPart($pivot_child, $check['id']);
                }
            }
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeStatusCondition($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $notes = $data['notes'];
        $status_condition = $data['status_condition'];
        try{
            if($status_condition < 1 || $status_condition > 3){
                return ["success" => false, "message" => "Status Usage Tidak Tepat", "status" => 400];
            }
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Inventory Tidak Ditemukan", "status" => 400];
            $inventory->status_condition = $status_condition;
            $inventory->save();
            // $last_activity = Activity::all()->last();
            // if($last_activity->subject_id === $id){
            //     $last_activity->causer_id = $check['id'];
            //     $last_activity->causer_type = $notes;
            //     $last_activity->save();
            // }
            return ["success" => true, "message" => "Status Kondisi Inventory Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeStatusUsage($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
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
                if($relationship_type_id < 1 || $relationship_type_id > 3) return ["success" => false, "message" => "Relationship Type Id Tidak Tepat", "status" => 400];
                if($connected_id === null) return ["success" => false, "message" => "Connected Id Belum Terisi", "status" => 400];
            }
            
            $inventory = Inventory::find($id);
            if($inventory === null) return ["success" => false, "message" => "Inventory Tidak Ditemukan", "status" => 400];
            $model = ModelInventory::find($inventory->model_id);
            if($model === null) return ["success" => false, "message" => "Tipe Model pada Inventory Tidak Ditemukan", "status" => 400];
            $asset = Asset::find($model->asset_id);
            if($asset === null) return ["success" => false, "message" => "Tipe Aset pada Tipe Model pada Inventory Tidak Ditemukan", "status" => 400];
            $inventory->status_usage = $status_usage;
            $inventory->save();
            // $last_activity = Activity::all()->last();
            // if($last_activity->subject_id === $id){
            //     $last_activity->causer_id = $check['id'];
            //     $last_activity->causer_type = $notes;
            //     $last_activity->save();
            // }
            if($status_usage !== 1){
                //Delete Relationship except Inventory type (4)
                $relationship_inventories = RelationshipInventory::where('subject_id', $inventory->id)->where('type_id', '<>', 4)->get();
                if(count($relationship_inventories)){
                    foreach($relationship_inventories as $relationship_inventory){
                        $relationship_inventory->delete();
                        // $last_activity = Activity::all()->last();
                        // $last_activity->causer_id = $check['id'];
                        // $last_activity->causer_type = "Ubah Status Pemakaian";
                        // $last_activity->save();
                    }
                }
                return ["success" => true, "message" => "Status Pemakaian Inventory Berhasil Diubah", "status" => 200];
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
            // $last_activity = Activity::all()->last();
            // $last_activity->causer_id = $check['id'];
            // $last_activity->causer_type = $notes;
            // $last_activity->save();
            
            return ["success" => true, "message" => "Status Pemakaian Inventory Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Manufacturers

    public function getManufacturers($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $manufacturers = Manufacturer::get();
            if($manufacturers->isEmpty()) return ["success" => false, "message" => "Manufacturer Belum dibuat", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $manufacturers, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addManufacturer($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
    
    // Relationship
    // Relationship Type

    public function getRelationships($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationships = Relationship::get();
            if($relationships->isEmpty()) return ["success" => false, "message" => "Relationship Belum dibuat", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationships, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRelationship($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationship = Relationship::find($id);
            if($relationship === null) return ["success" => false, "message" => "Relationship Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRelationship($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $relationship = Relationship::find($id);
        if($relationship === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        $asset = RelationshipAsset::where('relationship_id', $relationship->id)->first();
        if($asset) return ["success" => false, "message" => "Relationship Masih Digunakan Asset", "status" => 400];
        try{
            $relationship->delete();
            return ["success" => true, "message" => "Relationship berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Relationship Asset

    public function getRelationshipAssets($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationship_assets = RelationshipAsset::get();
            if($relationship_assets->isEmpty()) return ["success" => false, "message" => "Relationship Asset Belum dibuat", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $relationship_assets, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRelationshipAsset($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $data['id'];
            $type_id = (int)$data['type_id'];
            $relationship_assets = RelationshipAsset::get();
            $relationships = Relationship::get();
            $assets = Asset::get();
            $relationship_assets_from_inverse = $relationship_assets->where('connected_id', $id)->where('type_id', $type_id);
            $users = [];
            $companies = [];
            if($type_id === -1 || -2){
                $userService = new UserService;
                $users = $userService->getUserList(0, auth()->user()->company_id, true)['data'];
            } 
            if($type_id === -3){
                $companies = Company::select('company_id AS id', 'company_name AS name')->where('role', 2)->get();
            } 
            $data_not_from_invers = [];
            if($type_id === -4){
                $relationship_assets_not_from_inverse = $relationship_assets->where('subject_id', $id);
                $first_type = $relationship_assets_not_from_inverse->where('type_id', -1)->first();
                $second_type = $relationship_assets_not_from_inverse->where('type_id', -2)->first();
                $third_type = $relationship_assets_not_from_inverse->where('type_id', -3)->first();
                if(($first_type !== null || $second_type !== null) && $users === []){
                    $userService = new UserService;
                    $users = $userService->getUserList(0, auth()->user()->company_id, true)['data'];
                }
                if($third_type !== null && $companies === []){
                    $companies = Company::select('company_id', 'company_name')->where('role', 2)->get();
                }
                
                if(count($relationship_assets_not_from_inverse)){
                    foreach($relationship_assets_not_from_inverse as $relationship_asset){
                        $relationship = $relationships->find($relationship_asset->relationship_id);
                        $relationship_subject_detail = $assets->find($relationship_asset->subject_id);
                        if($relationship_subject_detail === null) $relationship_asset->subject_detail_name = "Asset Not Found";
                        else $relationship_asset->subject_detail_name = $relationship_subject_detail->name;
                        if($relationship === null) $relationship_asset->relationship = "Relationship Not Found";
                        else $relationship_asset->relationship = $relationship_asset->is_inverse ? $relationship->inverse_relationship_type : $relationship->relationship_type;
                        $relationship_asset->type = $relationship_asset->type_id === -1 ? "Agent" : ($relationship_asset->type_id === -2 ? "Requester" : ($relationship_asset->type_id === -3 ? "Company" : "Asset Type"));
                        if($relationship_asset->connected_id === null){
                            $relationship_asset->connected_detail_name = "Detail ID Kosong";
                        } else {
                            if($relationship_asset->type_id === -1 || $relationship_asset->type_id === -2){
                                $check_id = $relationship_asset->connected_id;
                                $user = $users->find($check_id);
                                if($user === null || $user->role !== ($relationship_asset->type_id * -1)) $relationship_asset->connected_detail_name = "User Not Found";
                                else $relationship_asset->connected_detail_name = $user->fullname;
                            } else if($relationship_asset->type_id === -3){
                                $check_id = $relationship_asset->connected_id;
                                $company = $companies->find($check_id);
                                if($company === null) $relationship_asset->connected_detail_name = "Company Not Found";
                                else $relationship_asset->connected_detail_name = $company->company_name;
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
                    if($relationship === null) $relationship_asset->relationship = "Relationship Not Found";
                    else $relationship_asset->relationship = $relationship_asset->is_inverse ? $relationship->relationship_type : $relationship->inverse_relationship_type;
                    $relationship_asset->type = $relationship_asset->type_id === -1 ? "Agent" : ($relationship_asset->type_id === -2 ? "Requester" : ($relationship_asset->type_id === -3 ? "Company" : "Asset Type"));
                    
                    if($relationship_asset->connected_id === null){
                        $relationship_asset->connected_detail_name = "Detail ID Kosong";
                    } else {
                        if($relationship_asset->type_id === -1 || $relationship_asset->type_id === -2){
                            $check_id = $relationship_asset->connected_id;
                            $user = $users->find($check_id);
                            if($user === null || $user->role !== $relationship_asset->type_id * -1){
                                $relationship_asset->connected_detail_name = "User Not Found";
                            } 
                            else $relationship_asset->connected_detail_name = $user->fullname;
                        } else if($relationship_asset->type_id === -3){
                            $check_id = $relationship_asset->connected_id;
                            $company = $companies->find($check_id);
                            if($company === null) $relationship_asset->connected_detail_name = "Company Not Found";
                            else $relationship_asset->connected_detail_name = $company->company_name;
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
             
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["from_inverse" => $data_from_invers, "not_from_inverse" => $data_not_from_invers], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRelationshipAssetRelation($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $relationships = Relationship::get();
            $types = [["id" => -1, "name" => "Agent"], ["id" => -2, "name" => "Requester"], ["id" => -3, "name" => "Company"], ["id" => -4, "name" => "Asset"]];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["relationships" => $relationships, "types" => $types], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRelationshipAssetDetailList($type_id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $type = (int)$type_id;
            if($type > -1 || $type < -4) return ["success" => false, "message" => "Tipe Id Tidak Tepat", "status" => 400];
            if($type === -1 || $type === -2){     
                $role_checker = $type * -1 ;       
                $userService = new UserService;
                $users = $userService->getUserList($role_checker, auth()->user()->company_id, true)['data'];
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $users, "status" => 200];
            } else if($type === -3){
                $companies = Company::select('company_id AS id', 'company_name AS name')->where('role', 2)->get();
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
            } else {
                $assets[] = [
                    'id' => null,
                    'title' => "-",
                    'key' => "000",
                    'value' => "000",
                    'children' => []
                ];
                $tree_assets = $this->getTreeAssets();
                foreach($tree_assets as $tree_asset) $assets[] = $tree_asset;
                return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $assets, "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRelationshipAsset($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $type_id = $data['type_id'];
        if($type_id > -1 || $type_id < -4) return ["success" => false, "message" => "Tipe Id Tidak Tepat", "status" => 400];
        try{
            $relationship_asset = new RelationshipAsset;
            $relationship_asset->relationship_id = $data['relationship_id'];
            $relationship_asset->subject_id = $data['subject_id'];
            $relationship_asset->is_inverse = $data['is_inverse'];
            $relationship_asset->type_id = $type_id;
            $relationship_asset->connected_id = $data['connected_id'];
            $relationship_asset->save();
            
            return ["success" => true, "message" => "Relationship Asset berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRelationshipAsset($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            // Commented codes have purpose for update type id if it is needed
            $id = $data['id'];
            $from_inverse = $data['from_inverse'];
            // $type_id = $data['type_id'];
            // if($type_id < 1 || $type_id > 4) return ["success" => false, "message" => "Tipe Id Tidak Tepat", "status" => 400];
            $relationship_asset = RelationshipAsset::find($id);
            if($relationship_asset === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $relationship_asset->relationship_id = $data['relationship_id'];
            if($from_inverse){
                $relationship_asset->subject_id = $data['connected_id'];
                $relationship_asset->is_inverse = !$data['is_inverse'];
            } else {
                $relationship_asset->connected_id = $data['connected_id'];
                $relationship_asset->is_inverse = $data['is_inverse'];
            } 
            // $relationship_asset->type_id = $type_id;
            $relationship_asset->save();
            return ["success" => true, "message" => "Relationship Asset berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRelationshipAsset($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $relationship_asset = RelationshipAsset::find($id);
        if($relationship_asset === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        $relationship_inventory = RelationshipInventory::where('relationship_asset_id', $relationship_asset->id)->first();
        if($relationship_inventory)return ["success" => false, "message" => "Relationship Masih Digunakan Inventory", "status" => 400];
        try{
            $relationship_asset->delete();
            return ["success" => true, "message" => "Relationship Asset berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}