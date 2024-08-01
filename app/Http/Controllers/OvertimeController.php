<?php

namespace App\Http\Controllers;

use App\Services\OvertimeService;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    
    protected $OvertimeService;
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->OvertimeService = new OvertimeService;
    }

    public function getOvertimeStatistics(Request $request){
        $route_name = "OVERTIME_STATISTICS_GET";

        $response = $this->OvertimeService->getOvertimeStatistics($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getOvertimeStatuses(Request $request){
        $route_name = "OVERTIME_STATUSES_GET";

        $response = $this->OvertimeService->getOvertimeStatuses($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getOvertimeStatus(Request $request){
        $route_name = "OVERTIME_STATUSES_GET";

        $response = $this->OvertimeService->getOvertimeStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getOvertimes(Request $request){
        $route_name = "OVERTIMES_GET";

        $response = $this->OvertimeService->getOvertimes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getOvertimesUser(Request $request){
        $route_name = "OVERTIMES_USER_GET";

        $response = $this->OvertimeService->getOvertimesUser($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getOvertime(Request $request){
        $route_name = "OVERTIME_GET";

        $response = $this->OvertimeService->getOvertime($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addOvertime(Request $request){
        $route_name = "OVERTIME_ADD";

        $response = $this->OvertimeService->addOvertime($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addOvertimeUser(Request $request){
        $route_name = "OVERTIME_USER_ADD";

        $response = $this->OvertimeService->addOvertimeUser($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateOvertime(Request $request){
        $route_name = "OVERTIME_UPDATE";

        $response = $this->OvertimeService->updateOvertime($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteOvertime(Request $request){
        $route_name = "OVERTIME_DELETE";

        $response = $this->OvertimeService->deleteOvertime($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function approveOvertime(Request $request){
        $route_name = "OVERTIME_APPROVE";

        $response = $this->OvertimeService->approveOvertime($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function addOvertimeDocument(Request $request){
        $route_name = "OVERTIME_DOCUMENT_ADD";

        $response = $this->OvertimeService->addOvertimeDocument($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
