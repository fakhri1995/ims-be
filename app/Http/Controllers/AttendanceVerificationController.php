<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceVerificationService;

class AttendanceVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->attendanceVerification = new AttendanceVerificationService;
    }

    // Attendance Forms
    public function getAttendanceVerifications(Request $request)
    {
        $route_name = "ATTENDANCE_VERIFICATIONS_GET";

        $response = $this->attendanceVerification->getAttendanceVerifications($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceHistoryVerifications(Request $request)
    {
        $route_name = "ATTENDANCE_HISTORY_VERIFICATIONS_GET";

        $response = $this->attendanceVerification->getAttendanceHistoryVerifications($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function approveAttendanceVerification(Request $request)
    {
        $route_name = "ATTENDANCE_VERIFICATION_APPROVE";

        $response = $this->attendanceVerification->approveAttendanceVerification($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function rejectAttendanceVerification(Request $request)
    {
        $route_name = "ATTENDANCE_VERIFICATION_REJECT";

        $response = $this->attendanceVerification->rejectAttendanceVerification($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
   