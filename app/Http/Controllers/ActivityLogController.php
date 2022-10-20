<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LogService;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->logService = new LogService;
    }

    // Inventory

    public function getActivityInventoryLogs(Request $request)
    {
        $route_name = "INVENTORY_LOG_GET";   
        $id = $request->get('id', null);
        
        $response = $this->logService->getActivityInventoryLogs($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // Ticket

    public function getTicketLog(Request $request){
        $route_name = "TICKET_LOG_GET";   
        $id = $request->get('id', null);
        
        $response = $this->logService->getTicketLog($id, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getClientTicketLog(Request $request){
        $route_name = "TICKET_CLIENT_LOG_GET";   
        $id = $request->get('id', null);
        
        $response = $this->logService->getClientTicketLog($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // Company

    public function getCompanyLog(Request $request){
        $route_name = "COMPANY_LOG_GET";   
        
        $response = $this->logService->getCompanyLog($request, $route_name);
        return response()->json($response, $response['status']);
    }


    // Recruitment
    public function getRecruitmentLog(Request $request){
        $route_name = "RECRUITMENT_LOG_GET";   
        
        $id = $request->id ?? NULL;
        $response = $this->logService->getRecruitmentLog($id, $route_name);
        return response()->json($response, $response['status']);
    }
}