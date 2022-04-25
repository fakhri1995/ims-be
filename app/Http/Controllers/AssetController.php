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
            'parent' => $request->get('parent', null),
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

    public function getFilterModels(Request $request)
    {
        $route_name = "MODELS_GET";

        $response = $this->assetService->getFilterModels($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getModels(Request $request)
    {
        $route_name = "MODELS_GET";

        $response = $this->assetService->getModels($request, $route_name);
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
        $route_name = "MODEL_GET";

        $response = $this->assetService->getModelRelations($route_name);
        return response()->json($response, $response['status']);
    }

    public function addModel(Request $request)
    {
        $route_name = "MODEL_ADD";
        
        $response = $this->assetService->addModel($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateModel(Request $request)
    {
        $route_name = "MODEL_UPDATE";
        
        $response = $this->assetService->updateModel($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteModel(Request $request)
    {
        $route_name = "MODEL_DELETE";
        $id = $request->get('id', null);
        
        $response = $this->assetService->deleteModel($id, $route_name);
        return response()->json($response, $response['status']);
    }


    // Inventory
    public function getInventoryRelations(Request $request)
    {
        $route_name = "INVENTORY_GET";

        $response = $this->assetService->getInventoryRelations($route_name);
        return response()->json($response, $response['status']);
    }

    public function getFilterInventories(Request $request)
    {
        $route_name = "INVENTORIES_GET";

        $response = $this->assetService->getFilterInventories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getInventories(Request $request)
    {
        $route_name = "INVENTORIES_GET";

        $response = $this->assetService->getInventories($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getCompanyInventories(Request $request)
    {
        $route_name = "COMPANY_INVENTORIES_GET";

        $response = $this->assetService->getCompanyInventories($request, $route_name);
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
        $route_name = "INVENTORY_PARTS_ADD";

        $response = $this->assetService->getInventoryAddable($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getInventoryReplacements(Request $request)
    {
        $route_name = "INVENTORY_PART_REPLACE";

        $response = $this->assetService->getInventoryReplacements($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getChangeStatusUsageDetailList(Request $request)
    {
        $route_name = "INVENTORY_STATUS_USAGE";

        $response = $this->assetService->getChangeStatusUsageDetailList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addInventory(Request $request)
    {
        $route_name = "INVENTORY_ADD";
        
        $response = $this->assetService->addInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateInventory(Request $request)
    {
        $route_name = "INVENTORY_UPDATE";
        
        $response = $this->assetService->updateInventory($request, $route_name);
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
        $route_name = "INVENTORY_PART_REMOVE";
        
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
        $route_name = "INVENTORY_STATUS_CONDITION";
        
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
        $route_name = "INVENTORY_STATUS_USAGE";
        
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
        $route_name = "INVENTORY_NOTES_ADD";
        
        $data_request = [
            'id' => $request->get('id', null),
            'notes' => $request->get('notes')
        ];
        
        $response = $this->assetService->addInventoryNotes($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function importInventories(Request $request){
        $route_name = "INVENTORY_IMPORT";
        
        $response = $this->assetService->importInventories($request, $route_name);
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

    // Vendor

    public function getVendors(Request $request)
    {
        $route_name = "VENDORS_GET";

        $response = $this->assetService->getVendors($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addVendor(Request $request)
    {
        $route_name = "VENDOR_ADD";

        $response = $this->assetService->addVendor($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateVendor(Request $request)
    {
        $route_name = "VENDOR_UPDATE";

        $response = $this->assetService->updateVendor($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteVendor(Request $request)
    {
        $route_name = "VENDOR_DELETE";

        $response = $this->assetService->deleteVendor($request, $route_name);
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

    // Relationship Inventory

    // public function getRelationshipInventories(Request $request)
    // {
    //     $route_name = "RELATIONSHIP_INVENTORIES_GET";

    //     $response = $this->assetService->getRelationshipInventories($route_name);
    //     return response()->json($response, $response['status']);
    // }
    public function getCompanyRelationshipInventory(Request $request)
    {
        $route_name = "COMPANY_RELATIONSHIP_INVENTORIES_GET";
        
        $response = $this->assetService->getCompanyRelationshipInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipInventory(Request $request)
    {
        $route_names = ["RELATIONSHIP_INVENTORY_GET", "AGENT_RELATIONSHIP_INVENTORY_GET" , "REQUESTER_RELATIONSHIP_INVENTORY_GET" , "COMPANY_RELATIONSHIP_INVENTORY_GET"];
        $data_request = [
            'id' => $request->get('id', null),
            'type_id' => $request->get('type_id')
        ];

        $response = $this->assetService->getRelationshipInventory($data_request, $route_names);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipInventoryRelation(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_GET";

        $response = $this->assetService->getRelationshipInventoryRelation($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRelationshipInventoryDetailList(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_GET";

        $response = $this->assetService->getRelationshipInventoryDetailList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRelationshipInventories(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_ADD";

        $response = $this->assetService->addRelationshipInventories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRelationshipInventory(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_UPDATE";

        $response = $this->assetService->updateRelationshipInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRelationshipInventory(Request $request)
    {
        $route_name = "RELATIONSHIP_INVENTORY_DELETE";
        $data_request = [
            'id' => $request->get('id'),
            'notes' => $request->get('notes', null)
        ];

        $response = $this->assetService->deleteRelationshipInventory($data_request, $route_name);
        return response()->json($response, $response['status']);
    }
}