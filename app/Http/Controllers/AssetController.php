<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AssetService;

class AssetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        // $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
        $this->assetService = new AssetService;
    }

    // Asset 
    public function getAssets(Request $request)
    {
        $route_name = "ASSETS_GET";

        $response = $this->assetService->getAssets($route_name);
        return response()->json($response, $response['status']);
    }

    public function getAsset(Request $request)
    {
        $route_name = "ASSET_GET";
        
        $id = $request->get('id', null);
        $response = $this->assetService->getAsset($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAsset(Request $request)
    {
        $route_name = "ASSET_ADD";
        
        $data_request = [
            'name' => $request->get('name'),
            'parent' => $request->get('parent'),
            'description' => $request->get('description'),
            'required_sn' => $request->get('required_sn'),
            'asset_columns' => $request->get('asset_columns', [])
        ];
        
        $response = $this->assetService->addAsset($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAsset(Request $request)
    {
        $route_name = "ASSET_UPDATE";

        $data_request = [
            'id' => $request->get('id', null),
            'name' => $request->get('name'),
            'parent' => $request->get('parent', null),
            'description' => $request->get('description'),
            'required_sn' => $request->get('required_sn'),
            'action' => $request->get('action', false),
            'is_deleted' => $request->get('is_deleted'),
            'add_columns' => $request->get('add_columns', []),
            'update_columns' => $request->get('update_columns', []),
            'delete_column_ids' => $request->get('delete_column_ids', [])
        ];
        
        $response = $this->assetService->updateAsset($data_request, $route_name);
        return response()->json($response, $response['status']);
    }    
    
    public function deleteAsset(Request $request)
    {
        $route_name = "ASSET_DELETE";
        $data_request = [
            'id' => $request->get('id', null),
            'new_parent' => $request->get('new_parent', null),
            'new_model_asset_id' => $request->get('new_model_asset_id', null)
        ];
        
        $response = $this->assetService->deleteAsset($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Model

    public function getModels(Request $request)
    {
        $route_name = "MODELS_GET";

        $response = $this->assetService->getModels($route_name);
        return response()->json($response, $response['status']);
    }

    public function getModel(Request $request)
    {
        $route_name = "MODEL_GET";
        
        $id = $request->get('id', null);
        $response = $this->assetService->getModel($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getModelRelations(Request $request){
        $route_name = "MODEL_ADD";

        $response = $this->assetService->getModelRelations($route_name);
        return response()->json($response, $response['status']);
    }

    public function addModel(Request $request)
    {
        $route_name = "MODEL_ADD";
        
        $data_request = [
            'name' => $request->get('name'),
            'asset_id' => $request->get('asset_id'),
            'description' => $request->get('description'),
            'manufacturer_id' => $request->get('manufacturer_id'),
            'required_sn' => $request->get('required_sn'),
            'model_columns' => $request->get('model_columns',[]),
            'model_parts' => $request->get('model_parts',[])
        ];
        
        $response = $this->assetService->addModel($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateModel(Request $request)
    {
        $route_name = "MODEL_UPDATE";

        $data_request = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'asset_id' => $request->get('asset_id'),
            'description' => $request->get('description'),
            'manufacturer_id' => $request->get('manufacturer_id'),
            'required_sn' => $request->get('required_sn'),
            'delete_column_ids' => $request->get('delete_column_ids',[]),
            'update_columns' => $request->get('update_columns',[]),
            'add_columns' => $request->get('add_columns',[]),
            'delete_model_ids' => $request->get('delete_model_ids',[]),
            'add_models' => $request->get('add_models',[])
        ];
        
        $response = $this->assetService->updateModel($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteModel(Request $request)
    {
        $route_name = "ASSET_DELETE";
        $id = $request->get('id', null);
        
        $response = $this->assetService->deleteModel($id, $route_name);
        return response()->json($response, $response['status']);
    }


    // Inventory
    public function getInventoryRelations(Request $request)
    {
        $route_name = "INVENTORY_ADD";

        $response = $this->assetService->getInventoryRelations($route_name);
        return response()->json($response, $response['status']);
    }

    public function getInventories(Request $request)
    {
        $route_name = "INVENTORIES_GET";

        $response = $this->assetService->getInventories($route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getInventory(Request $request)
    {
        $route_name = "INVENTORY_GET";
        
        $id = $request->get('id', null);
        $response = $this->assetService->getInventory($id, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getInventoryAddable(Request $request)
    {
        $route_name = "INVENTORY_ADDABLE_GET";

        $response = $this->assetService->getInventoryAddable($route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getInventoryReplacements(Request $request)
    {
        $route_name = "INVENTORY_REPLACEMENTS_GET";

        $id = $request->get('id', null);    
        $response = $this->assetService->getInventoryReplacements($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getChangeStatusUsageDetailList(Request $request)
    {
        $route_name = "STATUS_USAGE_UPDATE";

        $id = $request->get('id', null);    
        $response = $this->assetService->getChangeStatusUsageDetailList($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addInventory(Request $request)
    {
        $route_name = "ASSET_ADD";
        
        $data_request = [
            'model_id' => $request->get('model_id'),
            'vendor_id' => $request->get('vendor_id'),
            'inventory_name' => $request->get('inventory_name'),
            'status_condition' => $request->get('status_condition'),
            'status_usage' => $request->get('status_usage'),
            'serial_number' => $request->get('serial_number'),
            'location' => $request->get('location'),
            'is_exist' => $request->get('is_exist'),
            'deskripsi' => $request->get('deskripsi'),
            'manufacturer_id' => $request->get('manufacturer_id'),
            'mig_id' => $request->get('mig_id'),
            'notes' => $request->get('notes'),
            'inventory_values' => $request->get('inventory_values',[]),
            'inventory_parts' => $request->get('inventory_parts',[])
        ];
        
        $response = $this->assetService->addInventory($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateInventory(Request $request)
    {
        $route_name = "ASSET_UPDATE";
        
        $data_request = [
            'id' => $request->get('id'),
            'vendor_id' => $request->get('vendor_id'),
            'inventory_name' => $request->get('inventory_name'),
            'location' => $request->get('location'),
            'is_exist' => $request->get('is_exist', true),
            'deskripsi' => $request->get('deskripsi'),
            'manufacturer_id' => $request->get('manufacturer_id'),
            'mig_id' => $request->get('mig_id'),
            'serial_number' => $request->get('serial_number'),
            'notes' => $request->get('notes', null),
            'inventory_values' => $request->get('inventory_values',[])
        ];
        
        $response = $this->assetService->updateInventory($data_request, $route_name);
        return response()->json($response, $response['status']);
    }    

    public function replaceInventoryPart(Request $request){
        $route_name = "INVENTORY_PART_REPLACE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'replacement_id' => $request->get('replacement_id', null),
            'notes' => $request->get('notes', null),
        ];
        
        $response = $this->assetService->replaceInventoryPart($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function removeInventoryPart(Request $request){
        $route_name = "INVENTORY_PART_REPLACE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'inventory_part_id' => $request->get('inventory_part_id', null),
            'notes' => $request->get('notes', null),
        ];
        
        $response = $this->assetService->removeInventoryPart($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addInventoryParts(Request $request)
    {
        $route_name = "INVENTORY_PARTS_ADD";
        
        $data_request = [
            'id' => $request->get('id', null),
            'inventory_part_ids' => $request->get('inventory_part_ids', []),
            'notes' => $request->get('notes', null),
        ];
        
        $response = $this->assetService->addInventoryParts($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteInventory(Request $request)
    {
        $route_name = "INVENTORY_DELETE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'notes' => $request->get('notes', null)
        ];
        
        $response = $this->assetService->deleteInventory($data_request, $route_name);
        return response()->json($response, $response['status']);
        
    }

    // Change Status Inventory

    public function changeStatusCondition(Request $request){
        $route_name = "INVENTORY_STATUS_CONDITION_UPDATE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'status_condition' => $request->get('status_condition', null),
            'notes' => $request->get('notes', null),
        ];
        
        $response = $this->assetService->changeStatusCondition($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeStatusUsage(Request $request)
    {
        $route_name = "INVENTORY_STATUS_CONDITION_USAGE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'status_usage' => $request->get('status_usage', null),
            'notes' => $request->get('notes', null),
            'relationship_type_id' => $request->get('relationship_type_id', null),
            'connected_id' => $request->get('connected_id', null),
            'detail_connected_id' => $request->get('detail_connected_id', null)
        ];
        
        $response = $this->assetService->changeStatusUsage($data_request, $route_name);
        return response()->json($response, $response['status']);
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
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Manufacturer

    public function getManufacturers(Request $request)
    {
        $route_name = "MANUFACTURERS_GET";

        $response = $this->assetService->getManufacturers($route_name);
        return response()->json($response, $response['status']);
    }

    public function addManufacturer(Request $request)
    {
        $route_name = "MANUFACTURER_ADD";
        
        $data_request = [
            'name' => $request->get('name')
        ];
        
        $response = $this->assetService->addManufacturer($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateManufacturer(Request $request)
    {
        $route_name = "MANUFACTURER_UPDATE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'name' => $request->get('name')
        ];
        
        $response = $this->assetService->updateManufacturer($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteManufacturer(Request $request)
    {
        $route_name = "MANUFACTURER_DELETE";
        $id = $request->get('id', null);

        $response = $this->assetService->deleteManufacturer($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // Relationship
    // Relationship Type

    public function getRelationships(Request $request)
    {
        $route_name = "RELATIONSHIPS_GET";

        $response = $this->assetService->getRelationships($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationship(Request $request)
    {
        $route_name = "RELATIONSHIP_GET";
        $id = $request->get('id', null);

        $response = $this->assetService->getRelationship($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRelationship(Request $request)
    {
        $route_name = "MANUFACTURER_UPDATE";
        
        $data_request = [
            'relationship_type' => $request->get('relationship_type'),
            'inverse_relationship_type' => $request->get('inverse_relationship_type'),
            'description' => $request->get('description', null)
        ];
        
        $response = $this->assetService->addRelationship($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRelationship(Request $request)
    {
        $route_name = "MANUFACTURER_UPDATE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'relationship_type' => $request->get('relationship_type'),
            'inverse_relationship_type' => $request->get('inverse_relationship_type'),
            'description' => $request->get('description', null)
        ];
        
        $response = $this->assetService->updateRelationship($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRelationship(Request $request)
    {
        $route_name = "RELATIONSHIP_DELETE";
        $id = $request->get('id', null);

        $response = $this->assetService->deleteRelationship($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // Relationship Asset

    public function getRelationshipAssets(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSETS_GET";

        $response = $this->assetService->getRelationshipAssets($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipAsset(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSET_GET";
        $data_request = [
            'id' => $request->get('id', null),
            'type_id' => $request->get('type_id')
        ];

        $response = $this->assetService->getRelationshipAsset($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipAssetRelation(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSET_GET";

        $response = $this->assetService->getRelationshipAssetRelation($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipAssetDetailList(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSET_ADD";
        $type_id = $request->get('type_id', null);

        $response = $this->assetService->getRelationshipAssetDetailList($type_id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRelationshipAsset(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSET_ADD";
        $data_request = [
            'subject_id' => $request->get('subject_id'),
            'type_id' => $request->get('type_id'),
            'relationship_id' => $request->get('relationship_id'),
            'is_inverse' => $request->get('is_inverse'),
            'connected_id' => $request->get('connected_id')
        ];

        $response = $this->assetService->addRelationshipAsset($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRelationshipAsset(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSET_UPDATE";
        $data_request = [
            'id' => $request->get('id', null),
            // 'type_id' => $request->get('type_id'),
            'relationship_id' => $request->get('relationship_id'),
            'is_inverse' => $request->get('is_inverse'),
            'from_inverse' => $request->get('from_inverse'),
            'connected_id' => $request->get('connected_id')
        ];

        $response = $this->assetService->updateRelationshipAsset($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRelationshipAsset(Request $request)
    {
        $route_name = "RELATIONSHIP_ASSET_DELETE";
        $id = $request->get('id', null);

        $response = $this->assetService->deleteRelationshipAsset($id, $route_name);
        return response()->json($response, $response['status']);
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
            return ["success" => false, "message" => $err, "status" => 400];
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
            return ["success" => false, "message" => $err, "status" => 400];
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
            return ["success" => false, "message" => $err, "status" => 400];
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
            return ["success" => false, "message" => $err, "status" => 400];
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
            return ["success" => false, "message" => $err, "status" => 400];
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
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}