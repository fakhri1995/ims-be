<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceVerification;

class AttendanceVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->attendanceVerification = new AttendanceVerification;
    }

    // Attendance Forms
    public function getAttendanceVerifications(Request $request)
    {
        $route_name = "ATTENDANCE_FORMS_GET";

        $response = $this->attendanceVerification->getAttendanceVerifications($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
   