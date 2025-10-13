<?php

namespace App\Http\Controllers;

use App\Services\ChargeCodeService;
use Illuminate\Http\Request;

class ChargeCodeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->chargecodeService = new ChargeCodeService;
    }

    public function getChargeCodeCompanies(Request $request){
        $route_name = "CHARGE_CODE_COMPANIES_GET";

        $response = $this->chargecodeService->getChargeCodeCompanies($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getChargeCodes(Request $request){
        $route_name = "CHARGE_CODES_GET";

        $response = $this->chargecodeService->getChargeCodes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getChargeCode(Request $request){
        $route_name = "CHARGE_CODE_GET";

        $response = $this->chargecodeService->getChargeCode($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addChargeCode(Request $request){
        $route_name = "CHARGE_CODE_ADD";

        $response = $this->chargecodeService->addChargeCode($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addChargeCodesCompany(Request $request){
        $route_name = "CHARGE_CODES_COMPANY_ADD";

        $response = $this->chargecodeService->addChargeCodesCompany($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceCode(Request $request){
        $route_name = "ATTENDANCE_CODES_ADD";

        $response = $this->chargecodeService->addAttendanceCode($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceCode(Request $request){
        $route_name = "ATTENDANCE_CODE_UPDATE";

        $response = $this->chargecodeService->updateAttendanceCode($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateChargeCode(Request $request){
        $route_name = "CHARGE_CODE_UPDATE";

        $response = $this->chargecodeService->updateChargeCode($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteChargeCode(Request $request){
        $route_name = "CHARGE_CODE_DELETE";

        $response = $this->chargecodeService->deleteChargeCode($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceCode(Request $request){
        $route_name = "ATTENDANCE_CODE_DELETE";

        $response = $this->chargecodeService->deleteAttendanceCode($request, $route_name);
        return response()->json($response, $response['status']);
    }
 
}