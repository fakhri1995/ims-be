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
    public function getCategories(Request $request)
    {
        $route_name = "CATEGORIES_GET";
        
        $response = $this->inventoryService->getCategories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCategory(Request $request)
    {
        $route_name = "CATEGORY_GET";
        
        $response = $this->inventoryService->getCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORY_ADD";
        
        $response = $this->inventoryService->addCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORY_UPDATE";
        
        $response = $this->inventoryService->updateCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteCategory(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_CATEGORY_DELETE";
        
        $response = $this->inventoryService->deleteCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProductInventoryId(Request $request)
    {
        $route_name = "PRODUCT_INVENTORY_GET_ID";
        
        $response = $this->inventoryService->getProductInventoryId($request, $route_name);
        return response()->json($response, $response['status']);
    }
}