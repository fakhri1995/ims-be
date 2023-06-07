<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InventoryService;

class InventoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    public function __construct()
    {
        $this->inventoryService = new InventoryService;
    }
    
    public function getProductInventories(Request $request)
    {
        $route_name = "PRODUCT_INVENTORIES_GET";
        
        $response = $this->inventoryService->getProductInventories($route_name);
        return response()->json($response, $response['status']);
    }

    public function getProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_GET";
        
        $response = $this->inventoryService->getProductInventory($route_name);
        return response()->json($response, $response['status']);
    }

    public function addProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_ADD";
        
        $response = $this->inventoryService->addProductInventory($route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_UPDATE";
        
        $response = $this->inventoryService->updateProductInventory($route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_DELETE";
        
        $response = $this->inventoryService->deleteProductInventory($route_name);
        return response()->json($response, $response['status']);
    }

    public function getInventoryBySearch(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_BY_SEARCH_GET";
        
        $response = $this->inventoryService->getInventoryBySearch($route_name);
        return response()->json($response, $response['status']);
    }

    
}