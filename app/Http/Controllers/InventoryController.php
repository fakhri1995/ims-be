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
        
        $response = $this->inventoryService->getProductInventories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_GET";
        
        $response = $this->inventoryService->getProductInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_ADD";
        
        $response = $this->inventoryService->addProductInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_UPDATE";
        
        $response = $this->inventoryService->updateProductInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProductInventory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_DELETE";
        
        $response = $this->inventoryService->deleteProductInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //PRODUCT CATEGORY
    public function getProductInventoryCategories(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORIES_GET";
        
        $response = $this->inventoryService->getProductInventoryCategories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProductInventoryCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_GET";
        
        $response = $this->inventoryService->getProductInventoryCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addProductInventoryCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORY__ADD";
        
        $response = $this->inventoryService->addProductInventoryCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProductInventoryCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORY__UPDATE";
        
        $response = $this->inventoryService->updateProductInventoryCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProductInventoryCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORY__DELETE";
        
        $response = $this->inventoryService->deleteProductInventoryCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }
}