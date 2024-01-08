<?php

namespace App\Http\Controllers;

use App\Services\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    protected $shiftService;
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->shiftService = new ShiftService;
    }

    public function getShifts(Request $request){
        $route_name = "SHIFTS_GET";

        $response = $this->shiftService->getShifts($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getShift(Request $request){
        $route_name = "SHIFT_GET";

        $response = $this->shiftService->getShift($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addShift(Request $request){
        $route_name = "SHIFT_ADD";

        $response = $this->shiftService->addShift($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateShift(Request $request){
        $route_name = "SHIFT_UPDATE";

        $response = $this->shiftService->updateShift($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateShiftStatus(Request $request){
        $route_name = "SHIFT_UPDATE";

        $response = $this->shiftService->updateShiftStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteShift(Request $request){
        $route_name = "SHIFT_DELETE";

        $response = $this->shiftService->deleteShift($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
