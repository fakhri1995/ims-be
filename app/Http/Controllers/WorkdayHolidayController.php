<?php

namespace App\Http\Controllers;

use App\Services\WorkdayHolidayService;
use Illuminate\Http\Request;

class WorkdayHolidayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->workdayHolidayService = new WorkdayHolidayService;
    }

    public function getWorkdayHolidays(Request $request){
        $route_name = "WORKDAY_HOLIDAYS_GET";

        $response = $this->workdayHolidayService->getWorkdayHolidays($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getWorkdayHoliday(Request $request){
        $route_name = "WORKDAY_HOLIDAY_GET";

        $response = $this->workdayHolidayService->getWorkdayHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addWorkdayHoliday(Request $request){
        $route_name = "WORKDAY_HOLIDAY_ADD";

        $response = $this->workdayHolidayService->addWorkdayHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateWorkdayHoliday(Request $request){
        $route_name = "WORKDAY_HOLIDAY_UPDATE";

        $response = $this->workdayHolidayService->updateWorkdayHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteWorkdayHoliday(Request $request){
        $route_name = "WORKDAY_HOLIDAY_DELETE";

        $response = $this->workdayHolidayService->deleteWorkdayHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }
 
}