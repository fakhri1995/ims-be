<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{   
    public function __construct()
    {
        $this->employeeService = new EmployeeService;
    }

    public function getEmployee(Request $request)
    {
        $route_name = "EMPLOYEE_GET";

        $response = $this->employeeService->getEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getEmployees(Request $request)
    {
        $route_name = "EMPLOYEES_GET";

        $response = $this->employeeService->getEmployees($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployee(Request $request)
    {
        $route_name = "EMPLOYEE_ADD";

        $response = $this->employeeService->addEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateEmployee(Request $request)
    {
        $route_name = "EMPLOYEE_UPDATE";

        $response = $this->employeeService->updateEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteEmployee(Request $request)
    {
        $route_name = "EMPLOYEE_DELETE";

        $response = $this->employeeService->deleteEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
