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
        $route_name = "EMPLOYEE_PLACEMENTS_COUNT_GET";

        $response = $this->employeeService->getEmployeePlacementsCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeeRolesCount(Request $request)
    {
        $route_name = "EMPLOYEE_ROLES_COUNT_GET";

        $response = $this->employeeService->getEmployeeRolesCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeeStatusesCount(Request $request)
    {
        $route_name = "EMPLOYEE_STATUSES_COUNT_GET";

        $response = $this->employeeService->getEmployeeStatusesCount($request, $route_name);
        return response()->json($response, $response['status']);
    }
    

    public function getEmployee(Request $request)
    {
        $route_name = "EMPLOYEE_GET";

        $response = $this->employeeService->getEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getFilterEmployees(Request $request)
    {
        $route_name = "FILTER_EMPLOYEES_GET";

        $response = $this->employeeService->getFilterEmployees($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployees(Request $request)
    {
        $route_name = "EMPLOYEES_GET";

        $response = $this->employeeService->getEmployees($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeesDraft(Request $request)
    {
        $route_name = "EMPLOYEES_DRAFT_GET";

        $response = $this->employeeService->getEmployeesDraft($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployee(Request $request)
    {
        $route_name = "EMPLOYEE_ADD";

        $response = $this->employeeService->addEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployeeFromUser(Request $request)
    {
        $route_name = "EMPLOYEE_FROM_USER_ADD";

        $response = $this->employeeService->addEmployeeFromUser($request, $route_name);
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


    // EMPLOYEE SALARY TEMPLATE
    public function getEmployeeSalaryColumn(Request $request)
    {
        $route_name = "EMPLOYEE_SALARY_COLUMN_GET";

        $response = $this->employeeService->getEmployeeSalaryColumn($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getEmployeeSalaryColumns(Request $request)
    {
        $route_name = "EMPLOYEE_SALARY_COLUMNS_GET";

        $response = $this->employeeService->getEmployeeSalaryColumns($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addEmployeeSalaryColumn(Request $request)
    {
        $route_name = "EMPLOYEE_SALARY_COLUMN_ADD";

        $response = $this->employeeService->addEmployeeSalaryColumn($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateEmployeeSalaryColumn(Request $request)
    {
        $route_name = "EMPLOYEE_SALARY_COLUMN_UPDATE";

        $response = $this->employeeService->updateEmployeeSalaryColumn($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteEmployeeSalaryColumn(Request $request)
    {
        $route_name = "EMPLOYEE_SALARY_COLUMN_DELETE";

        $response = $this->employeeService->deleteEmployeeSalaryColumn($request, $route_name);
        return response()->json($response, $response['status']);
    }


    // EMPLOYEE PAYSLIP
    public function getEmployeePayslip(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_GET";

        $response = $this->employeeService->getEmployeePayslip($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getEmployeePayslips(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIPS_GET";

        $response = $this->employeeService->getEmployeePayslips($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeesPayslip(Request $request)
    {
        $route_name = "EMPLOYEES_PAYSLIP_GET";

        $response = $this->employeeService->getEmployeesPayslip($request, $route_name);
        return response()->json($response, $response['status']);
    }


    public function getEmployeePayslipEmpty(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_EMPTY_GET";

        $response = $this->employeeService->getEmployeePayslipEmpty($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getEmployeePayslipsEmpty(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIPS_EMPTY_GET";

        $response = $this->employeeService->getEmployeePayslipsEmpty($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function addEmployeePayslip(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_ADD";

        $response = $this->employeeService->addEmployeePayslip($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateEmployeePayslip(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_UPDATE";

        $response = $this->employeeService->updateEmployeePayslip($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteEmployeePayslip(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_DELETE";

        $response = $this->employeeService->deleteEmployeePayslip($request, $route_name);
        return response()->json($response, $response['status']);
    }

    
    public function getEmployeePayslipStatusCount(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_STATUS_COUNT_GET";

        $response = $this->employeeService->getEmployeePayslipStatusCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function downloadEmployeePayslip(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_DOWNLOAD";

        $response = $this->employeeService->downloadEmployeePayslip($request, $route_name);
        if($response['success'] === false) return response()->json($response, $response['status']);
        return $response['data'];
    }

    public function postedEmployeeLastPayslips(Request $request)
    {
        $route_name = "EMPLOYEES_PAYSLIPS_POST";

        $response = $this->employeeService->postedEmployeeLastPayslips($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function raiseLastPeriodPayslip(Request $request)
    {
        $route_name = "EMPLOYEE_PAYSLIP_RAISE";

        $response = $this->employeeService->raiseLastPeriodPayslip($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
