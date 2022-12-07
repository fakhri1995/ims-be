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

    public function getEmployeePlacementsCount(Request $request)
    {
        $route_name = "";

        $response = $this->employeeService->getEmployeePlacementsCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeeRolesCount(Request $request)
    {
        $route_name = "";

        $response = $this->employeeService->getEmployeeRolesCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeeStatusesCount(Request $request)
    {
        $route_name = "";

        $response = $this->employeeService->getEmployeeStatusesCount($request, $route_name);
        return response()->json($response, $response['status']);
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

    // EMPLOYEE CONTRACT
    public function getEmployeeContract(Request $request)
    {
        $route_name = "EMPLOYEE_CONTRACT_GET";

        $response = $this->employeeService->getEmployeeContract($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getEmployeeContracts(Request $request)
    {
        $route_name = "EMPLOYEE_CONTRACTS_GET";

        $response = $this->employeeService->getEmployeeContracts($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployeeContract(Request $request)
    {
        $route_name = "EMPLOYEE_CONTRACT_ADD";

        $response = $this->employeeService->addEmployeeContract($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateEmployeeContract(Request $request)
    {
        $route_name = "EMPLOYEE_CONTRACT_UPDATE";

        $response = $this->employeeService->updateEmployeeContract($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteEmployeeContract(Request $request)
    {
        $route_name = "EMPLOYEE_CONTRACT_DELETE";

        $response = $this->employeeService->deleteEmployeeContract($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // EMPLOYEE INVENTORY
    public function getEmployeeInventory(Request $request)
    {
        $route_name = "EMPLOYEE_INVENTORY_GET";

        $response = $this->employeeService->getEmployeeInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getEmployeeInventories(Request $request)
    {
        $route_name = "EMPLOYEE_INVENTORIES_GET";

        $response = $this->employeeService->getEmployeeInventories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployeeInventory(Request $request)
    {
        $route_name = "EMPLOYEE_INVENTORY_ADD";

        $response = $this->employeeService->addEmployeeInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateEmployeeInventory(Request $request)
    {
        $route_name = "EMPLOYEE_INVENTORY_UPDATE";

        $response = $this->employeeService->updateEmployeeInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteEmployeeInventory(Request $request)
    {
        $route_name = "EMPLOYEE_INVENTORY_DELETE";

        $response = $this->employeeService->deleteEmployeeInventory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // EMPLOYEE DEVICE
    public function getEmployeeDevice(Request $request)
    {
        $route_name = "EMPLOYEE_DEVICE_GET";

        $response = $this->employeeService->getEmployeeDevice($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getEmployeeDevices(Request $request)
    {
        $route_name = "EMPLOYEE_DEVICES_GET";

        $response = $this->employeeService->getEmployeeDevices($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployeeDevice(Request $request)
    {
        $route_name = "EMPLOYEE_DEVICE_ADD";

        $response = $this->employeeService->addEmployeeDevice($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateEmployeeDevice(Request $request)
    {
        $route_name = "EMPLOYEE_DEVICE_UPDATE";

        $response = $this->employeeService->updateEmployeeDevice($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteEmployeeDevice(Request $request)
    {
        $route_name = "EMPLOYEE_DEVICE_DELETE";

        $response = $this->employeeService->deleteEmployeeDevice($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
