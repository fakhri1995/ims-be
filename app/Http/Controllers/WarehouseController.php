<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseService;

class WarehouseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->warehouseService = new WarehouseService;
    }

    // Purchase Orders
    public function getPurchaseOrders(Request $request)
    {
        $route_name = "PURCHASE_ORDERS_GET";

        $response = $this->warehouseService->getPurchaseOrders($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_GET";

        $response = $this->warehouseService->getPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_ADD";
        
        $response = $this->warehouseService->addPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updatePurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_UPDATE";
        
        $response = $this->warehouseService->updatePurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deletePurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_DELETE";
        
        $response = $this->warehouseService->deletePurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function acceptPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_ACCEPT";
        
        $response = $this->warehouseService->acceptPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function rejectPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_REJECT";
        
        $response = $this->warehouseService->rejectPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function sendPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_SEND";
        
        $response = $this->warehouseService->sendPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function receivePurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_RECEIVE";
        
        $response = $this->warehouseService->receivePurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Detail Purchase Orders
    public function getDetailPurchaseOrders(Request $request)
    {
        $route_name = "PURCHASE_ORDER_DETAILS_GET";

        $response = $this->warehouseService->getDetailPurchaseOrders($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addDetailPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_DETAIL_ADD";
        
        $response = $this->warehouseService->addDetailPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateDetailPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_DETAIL_UPDATE";
        
        $response = $this->warehouseService->updateDetailPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteDetailPurchaseOrder(Request $request)
    {
        $route_name = "PURCHASE_ORDER_DETAIL_DELETE";
        
        $response = $this->warehouseService->deleteDetailPurchaseOrder($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Detail Purchase Orders
    public function getQualityControlPurchases(Request $request)
    {
        $route_name = "PURCHASE_ORDER_QUALITY_CONTROLS_GET";

        $response = $this->warehouseService->getQualityControlPurchases($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getQualityControlPurchase(Request $request)
    {
        $route_name = "PURCHASE_ORDER_QUALITY_CONTROL_GET";

        $response = $this->warehouseService->getQualityControlPurchase($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function saveQC(Request $request)
    {
        $route_name = "PURCHASE_ORDER_QUALITY_CONTROL_GET";

        $response = $this->warehouseService->saveQC($request, $route_name);
        return response()->json($response, $response['status']);
    }
}