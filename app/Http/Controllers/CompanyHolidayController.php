<?php

namespace App\Http\Controllers;

use App\Services\CompanyHolidayService;
use Illuminate\Http\Request;

class CompanyHolidayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->companyHolidayService = new CompanyHolidayService;
    }

    public function getCompanyHolidays(Request $request){
        $route_name = "COMPANY_HOLIDAYS_GET";

        $response = $this->companyHolidayService->getCompanyHolidays($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCompanyHoliday(Request $request){
        $route_name = "COMPANY_HOLIDAY_GET";

        $response = $this->companyHolidayService->getCompanyHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addCompanyHoliday(Request $request){
        $route_name = "COMPANY_HOLIDAY_ADD";

        $response = $this->companyHolidayService->addCompanyHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateCompanyHoliday(Request $request){
        $route_name = "COMPANY_HOLIDAY_UPDATE";

        $response = $this->companyHolidayService->updateCompanyHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteCompanyHoliday(Request $request){
        $route_name = "COMPANY_HOLIDAY_DELETE";

        $response = $this->companyHolidayService->deleteCompanyHoliday($request, $route_name);
        return response()->json($response, $response['status']);
    }
 
}