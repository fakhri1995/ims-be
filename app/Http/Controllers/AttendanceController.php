<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->attendanceService = new AttendanceService;
    }

    // Attendance Forms
    public function getAttendanceForms(Request $request)
    {
        $route_name = "ATTENDANCE_FORMS_GET";

        $response = $this->attendanceService->getAttendanceForms($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_GET";

        $response = $this->attendanceService->getAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_ADD";
        
        $response = $this->attendanceService->addAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addUserAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_USERS_ADD";
        
        $response = $this->attendanceService->addUserAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function removeUserAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_USERS_REMOVE";
        
        $response = $this->attendanceService->removeUserAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function sendAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_SEND";
        
        $response = $this->attendanceService->sendAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function receiveAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_RECEIVE";
        
        $response = $this->attendanceService->receiveAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Detail Attendance Forms
    public function getDetailAttendanceForms(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_DETAILS_GET";

        $response = $this->attendanceService->getDetailAttendanceForms($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addDetailAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_DETAIL_ADD";
        
        $response = $this->attendanceService->addDetailAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateDetailAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_DETAIL_UPDATE";
        
        $response = $this->attendanceService->updateDetailAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteDetailAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_DETAIL_DELETE";
        
        $response = $this->attendanceService->deleteDetailAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Detail Attendance Forms
    public function getQualityControlPurchases(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_QUALITY_CONTROLS_GET";

        $response = $this->attendanceService->getQualityControlPurchases($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getQualityControlPurchase(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_QUALITY_CONTROL_GET";

        $response = $this->attendanceService->getQualityControlPurchase($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function saveQC(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_QUALITY_CONTROL_GET";

        $response = $this->attendanceService->saveQC($request, $route_name);
        return response()->json($response, $response['status']);
    }
}