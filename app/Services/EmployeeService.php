<?php 

namespace App\Services;

use App\Employee;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmployeeService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    public function getEmployee($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employee = Employee::find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
                return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
            }



            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployees($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            return ["success" => true, "message" => "Data Berhasil", "data" => null, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployee($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        
        try{
            $employee = new Employee();
            $current_date = date("Y-m-d H:i:s");
            $employee->created_at = $current_date;
            $employee->updated_at = $current_date;
            $employee->created_by = auth()->user()->id;
            $employee->is_posted = false;
            $employee->save();

            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateEmployeeDraft($request)
    {

    }

    public function updateEmployeeValid($request)
    {

    }

    public function updateEmployee($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "is_posted" => "boolean"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employee = Employee::find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
                return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
            }

            $is_posted = $request->is_posted;
            if($is_posted){
                return $this->updateEmployeeValid($request);
            }else{
                return $this->updateEmployeeDraft($request);
            }
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployee($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            return ["success" => true, "message" => "Data Berhasil", "data" => null, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

}