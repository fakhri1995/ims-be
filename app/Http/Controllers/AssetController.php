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
        $route_name = "INVENTORY_ADD";
        
        $data_request = [
            'id' => $request->get('id', null),
            'notes' => $request->get('notes')
        ];
        
        $response = $this->assetService->addInventoryNotes($data_request, $route_name);
        return response()->json($response, $response['status']);
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
        $route_name = "RELATIONSHIP_ADD";
        
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
        $route_name = "RELATIONSHIP_UPDATE";
        
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
        $route_name = "RELATIONSHIP_INVENTORIES_GET";

        $response = $this->assetService->addRelationshipAsset($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipInventory(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_GET";
        $data_request = [
            'id' => $request->get('id', null),
            'type_id' => $request->get('type_id')
        ];

        $response = $this->assetService->getRelationshipInventory($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipInventoryRelation(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_ADD";
        $id = $request->get('asset_id', null);

        $response = $this->assetService->getRelationshipInventoryRelation($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipInventoryDetailList(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_ADD";
        $relationship_asset_id = $request->get('relationship_asset_id');

        $response = $this->assetService->getRelationshipInventoryDetailList($relationship_asset_id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRelationshipInventories(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_ADD";
        $data_request = [
            'subject_id' => $request->get('subject_id'),
            'relationship_asset_id' => $request->get('relationship_asset_id'),
            'is_inverse' => $request->get('is_inverse'),
            'connected_ids' => $request->get('connected_ids', []),
            'notes' => $request->get('notes', null)
        ];

        $response = $this->assetService->addRelationshipInventories($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRelationshipInventory(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_UPDATE";
        $data_request = [
            'id' => $request->get('id'),
            'subject_id' => $request->get('subject_id'),
            'relationship_asset_id' => $request->get('relationship_asset_id'),
            'is_inverse' => $request->get('is_inverse'),
            'connected_id' => $request->get('connected_id'),
            'from_inverse' => $request->get('from_inverse'),
            'notes' => $request->get('notes', null)
        ];

        $response = $this->assetService->updateRelationshipInventory($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRelationshipInventory(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_DELETE";
        $id = $request->get('id');

        $response = $this->assetService->deleteRelationshipInventory($id, $route_name);
        return response()->json($response, $response['status']);
    }
}