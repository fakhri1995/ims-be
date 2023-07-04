<?php

namespace App\Http\Controllers;

use App\Services\ExampleService;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->exampleService = new ExampleService;
    }

    public function getExamples(Request $request){
        $route_name = "EXAMPLES_GET";

        $response = $this->exampleService->getExamples($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getExample(Request $request){
        $route_name = "EXAMPLE_GET";

        $response = $this->exampleService->getExample($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addExample(Request $request){
        $route_name = "EXAMPLE_ADD";

        $response = $this->exampleService->addExample($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function addExampleSection(Request $request){
        $route_name = "EXAMPLE_SECTION_ADD";

        $response = $this->exampleService->addExampleSection($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateExample(Request $request){
        $route_name = "EXAMPLE_UPDATE";

        $response = $this->exampleService->updateExample($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteExample(Request $request){
        $route_name = "EXAMPLE_DELETE";

        $response = $this->exampleService->deleteExample($request, $route_name);
        return response()->json($response, $response['status']);
    }
 
}