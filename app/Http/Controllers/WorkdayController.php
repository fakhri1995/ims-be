<?php

namespace App\Http\Controllers;

use App\Services\WorkdayService;
use Illuminate\Http\Request;

class WorkdayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->workdayService = new WorkdayService;
    }

    public function getWorkdayCompanies(Request $request){
        $route_name = "WORKDAY_COMPANIES_GET";

        $response = $this->workdayService->getWorkdayCompanies($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getWorkdayStatistics(Request $request){
        $route_name = "WORKDAY_STATISTICS_GET";

        $response = $this->workdayService->getWorkdayStatistics($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getWorkdays(Request $request){
        $route_name = "WORKDAYS_GET";

        $response = $this->workdayService->getWorkdays($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getWorkday(Request $request){
        $route_name = "WORKDAY_GET";

        $response = $this->workdayService->getWorkday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getPublicHolidays(Request $request){
        $route_name = "PUBLIC_HOLIDAYS_GET";

        $response = $this->workdayService->getPublicHolidays($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getPublicHolidaysWorkday(Request $request){
        $route_name = "PUBLIC_HOLIDAYS_WORKDAY_GET";

        $response = $this->workdayService->getPublicHolidaysWorkday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addWorkday(Request $request){
        $route_name = "WORKDAY_ADD";

        $response = $this->workdayService->addWorkday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateWorkday(Request $request){
        $route_name = "WORKDAY_UPDATE";

        $response = $this->workdayService->updateWorkday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteWorkday(Request $request){
        $route_name = "WORKDAY_DELETE";

        $response = $this->workdayService->deleteWorkday($request, $route_name);
        return response()->json($response, $response['status']);
    }
 
}