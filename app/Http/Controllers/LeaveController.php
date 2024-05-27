<?php

namespace App\Http\Controllers;

use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    protected $LeaveService;
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->LeaveService = new LeaveService;
    }

    public function getLeaveStatuses(Request $request){
        $route_name = "LEAVE_STATUSES_GET";

        $response = $this->LeaveService->getLeaveStatuses($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getLeaves(Request $request){
        $route_name = "LEAVES_GET";

        $response = $this->LeaveService->getLeaves($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getLeave(Request $request){
        $route_name = "LEAVE_GET";

        $response = $this->LeaveService->getLeave($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addLeave(Request $request){
        $route_name = "LEAVE_ADD";

        $response = $this->LeaveService->addLeave($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateLeave(Request $request){
        $route_name = "LEAVE_UPDATE";

        $response = $this->LeaveService->updateLeave($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteLeave(Request $request){
        $route_name = "LEAVE_DELETE";

        $response = $this->LeaveService->deleteLeave($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function approveLeave(Request $request){
        $route_name = "LEAVE_APPROVE";

        $response = $this->LeaveService->approveLeave($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getLeaveTypes(Request $request){
        $route_name = "LEAVE_TYPES_GET";

        $response = $this->LeaveService->getLeaveTypes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getLeaveType(Request $request){
        $route_name = "LEAVE_TYPE_GET";

        $response = $this->LeaveService->getLeaveType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addLeaveType(Request $request){
        $route_name = "LEAVE_TYPE_ADD";

        $response = $this->LeaveService->addLeaveType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteLeaveType(Request $request){
        $route_name = "LEAVE_TYPE_DELETE";

        $response = $this->LeaveService->deleteLeaveType($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
