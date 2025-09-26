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

    public function getExamples(Request $request){
        $route_name = "EXAMPLES_GET";

        $response = $this->workdayService->getExamples($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getPublicHolidays(Request $request){
        $route_name = "PUBLIC_HOLIDAYS_GET";

        $response = $this->workdayService->getPublicHolidays($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addExample(Request $request){
        $route_name = "EXAMPLE_ADD";

        $response = $this->workdayService->addExample($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function addExampleSection(Request $request){
        $route_name = "EXAMPLE_SECTION_ADD";

        $response = $this->workdayService->addExampleSection($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateExample(Request $request){
        $route_name = "EXAMPLE_UPDATE";

        $response = $this->workdayService->updateExample($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteExample(Request $request){
        $route_name = "EXAMPLE_DELETE";

        $response = $this->workdayService->deleteExample($request, $route_name);
        return response()->json($response, $response['status']);
    }
 
}