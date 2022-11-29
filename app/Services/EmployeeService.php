<?php 

namespace App\Services;

use App\Employee;
use App\EmployeeContract;
use App\EmployeeDevice;
use App\EmployeeInventory;
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

            $id = $request->id;
            $employee = Employee::with(["contracts","inventories","id_card_photo"])->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
                return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
            }


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployees($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $employee = Employee::with(["contracts","inventories"])->paginate();

        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployee($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

            
            $employee = new Employee();
            $current_timestamp = date("Y-m-d H:i:s");
            $employee->created_at = $current_timestamp;
            $employee->updated_at = $current_timestamp;
            $employee->created_by = auth()->user()->id;
            $employee->is_posted = false;
            $employee->save();

           

            try{
            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
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

        
            $id = $request->id;
            $employee = Employee::with("id_card_photo")->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
                return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
            }
        try{
            $employee->name = $request->name ?? NULL;
            $employee->nip = $request->nip ?? NULL;
            $employee->nik = $request->nik ?? NULL;
            $employee->alias = $request->alias ?? NULL;
            $employee->email_office = $request->email_office ?? NULL;
            $employee->email_personal = $request->email_personal ?? NULL;
            $employee->domicile = $request->domicile ?? NULL;
            $employee->phone_number = $request->phone_number ?? NULL;
            $employee->birth_place = $request->birth_place ?? NULL;
            $employee->birth_date = $request->birth_date ?? NULL;
            $employee->gender = $request->gender ?? NULL;
            $employee->blood_type = $request->blood_type ?? NULL;
            $employee->marital_status = $request->marital_status ?? NULL;
            $employee->number_of_children = $request->number_of_children ?? NULL;
            $employee->bio_mother_name = $request->bio_mother_name ?? NULL;
            $employee->npwp = $request->npwp ?? NULL;
            $employee->bpjs_kesehatan = $request->bpjs_kesehatan ?? NULL;
            $employee->bpjs_ketenagakerjaan = $request->bpjs_ketenagakerjaan ?? NULL;
            $employee->acc_number_bukopin = $request->acc_number_bukopin ?? NULL;
            $employee->acc_number_another = $request->acc_number_another ?? NULL;
            $employee->is_posted = $request->is_posted ?? NULL;
            $employee->save();

            $file = $request->file('id_card_photo',NULL);
            if($file){
                $old_file_id = $employee->id_card_photo->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($employee->id, $file, 'App\Employee', 'employee_id_card_photo', 'Employee', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }
            
            return ["success" => true, "message" => "Data Berhasil Diupdate", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployee($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->id;
        $employee = Employee::find($id);
        if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
            return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
        }

        try{
            $employee->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }



    // EMPLOYEE CONTRACT
    public function getEmployeeContract($request, $route_name)
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

        $id = $request->id;
        $employeeContract = EmployeeContract::with(["employee","role","contract_status","contract_file"])->find($id);
        if(!$employeeContract) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeContract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployeeContracts($request, $route_name)
    {   

        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employee_id = $request->employee_id;

        $employeeContracts = EmployeeContract::with(["employee"])->where(["employee_id" => $employee_id])->get();

        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeContracts, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployeeContract($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
            return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
        }

        try{
            $employeeContract = new EmployeeContract();
            $current_timestamp = date("Y-m-d H:i:s");
            $employeeContract->created_at = $current_timestamp;
            $employeeContract->updated_at = $current_timestamp;
            $employeeContract->created_by = auth()->user()->id;
            $employeeContract->is_employee_active = false;
            $employeeContract->employee_id = $request->employee_id;
            $employeeContract->save();

            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employeeContract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateEmployeeContract($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            
            $id = $request->id;
            $employee_id = $request->employee_id;
            $employeeContract = EmployeeContract::find($id);
            if(!$employeeContract) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employeeContract->employee_id != $employee_id) return ["success" => false, "message" => "Kontrak dan Karyawan tidak sesuai", "status" => 400]; 

            $employeeContract->is_employee_active = $request->is_employee_active ?? NULL;
            $employeeContract->contract_name = $request->contract_name ?? NULL;
            $employeeContract->role_id = $request->role_id ?? NULL;
            $employeeContract->contract_status_id = $request->contract_status_id ?? NULL;
            $employeeContract->pkwt_reference = $request->pkwt_reference ?? NULL;
            $employeeContract->contract_start_at = $request->contract_start_at ?? NULL;
            $employeeContract->contract_end_at = $request->contract_end_at ?? NULL;
            $employeeContract->placement = $request->placement ?? NULL;
            $employeeContract->new_office = $request->new_office ?? NULL;
            $employeeContract->resign_at = $request->resign_at ?? NULL;
            $employeeContract->benefit = $request->benefit ?? NULL;
            $employeeContract->save();

            $file = $request->file('contract_file',NULL);
            if($file){
                $old_file_id = $employeeContract->contract_file->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($employeeContract->id, $file, 'App\EmployeeContract', 'employee_contract_file', 'EmployeeContract', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }
        try{
            return ["success" => true, "message" => "Data Berhasil Diupdate", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployeeContract($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employee_id = $request->employee_id;
            $employeeContract = EmployeeContract::find($id);
            if(!$employeeContract) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employeeContract->employee_id != $employee_id) return ["success" => false, "message" => "Kontrak dan Karyawan tidak sesuai", "status" => 400]; 
            $employeeContract->delete();
            
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employeeContract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // EMPLOYEE INVENTORY
    public function getEmployeeInventory($request, $route_name)
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

        $id = $request->id;
        $employeeInventory = EmployeeInventory::with(["employee","devices","delivery_file","return_file"])->find($id);
        if(!$employeeInventory) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeInventory, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployeeInventories($request, $route_name)
    {   

        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employee_id = $request->employee_id;

        $employeeInventories = EmployeeInventory::with(["employee","devices"])->where(["employee_id" => $employee_id])->get();

        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeInventories, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployeeInventory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if($employee->is_posted == false && $employee->created_by != auth()->user()->id){
            return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
        }

        try{
            $employeeInventory = new EmployeeInventory();
            $current_timestamp = date("Y-m-d H:i:s");
            $employeeInventory->created_at = $current_timestamp;
            $employeeInventory->updated_at = $current_timestamp;
            $employeeInventory->created_by = auth()->user()->id;
            $employeeInventory->employee_id = $request->employee_id;
            $employeeInventory->save();

            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employeeInventory, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateEmployeeInventory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $id = $request->id;
            $employeeInventory = EmployeeInventory::with(["delivery_file","return_file"])->find($id);
            if(!$employeeInventory) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $employeeInventory->id_number = $request->id_number ?? NULL;
            $employeeInventory->device_name = $request->device_name ?? NULL;
            $employeeInventory->referance_invertory = $request->referance_invertory ?? NULL;
            $employeeInventory->device_type = $request->device_type ?? NULL;
            $employeeInventory->serial_number = $request->serial_number ?? NULL;
            $employeeInventory->delivery_date = $request->delivery_date ?? NULL;
            $employeeInventory->return_date = $request->return_date ?? NULL;
            $employeeInventory->pic_delivery = $request->pic_delivery ?? NULL;
            $employeeInventory->pic_return = $request->pic_return ?? NULL;
            $employeeInventory->save();

            $delivery_file = $request->file('delivery_file',NULL);
            if($delivery_file){
                $old_file_id = $employeeInventory->delivery_file->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($employeeInventory->id, $delivery_file, 'App\EmployeeInventory', 'employee_delivery_file', 'EmployeeInventory', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }

            $return_file = $request->file('return_file',NULL);
            if($return_file){
                $old_file_id = $employeeInventory->return_file->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($employeeInventory->id, $return_file, 'App\EmployeeInventory', 'employee_return_file', 'EmployeeInventory', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }
            
        try{
            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employeeInventory, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployeeInventory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "employee_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try{
            $id = $request->id;
            $employee_id = $request->employee_id;
            $employeeInventory = EmployeeInventory::find($id);
            if(!$employeeInventory) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employeeInventory->employee_id != $employee_id) return ["success" => false, "message" => "Kontrak dan Karyawan tidak sesuai", "status" => 400]; 
            $employeeInventory->delete();
        
            
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employeeInventory, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // EMPLOYEE DEVICE
    public function getEmployeeDevice($request, $route_name)
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

        $id = $request->id;
        $employeeDevice = EmployeeDevice::with(["inventory"])->find($id);
        if(!$employeeDevice) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeDevice, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployeeDevices($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "employee_inventory_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employee_inventory_id = $request->employee_inventory_id;

        $employeeDevices = EmployeeDevice::with(["inventory"])->where(["employee_inventory_id" => $employee_inventory_id])->get();

        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeDevices, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployeeDevice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "employee_inventory_id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $employee_inventory_id = $request->employee_inventory_id;
        $employee_inventory = EmployeeInventory::find($employee_inventory_id);
        if(!$employee_inventory) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        
            $employeeDevice = new EmployeeDevice();
            $current_timestamp = date("Y-m-d H:i:s");
            $employeeDevice->created_at = $current_timestamp;
            $employeeDevice->updated_at = $current_timestamp;
            $employeeDevice->created_by = auth()->user()->id;
            $employeeDevice->employee_inventory_id = $request->employee_inventory_id;
            $employeeDevice->save();

        try{
            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employeeDevice, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateEmployeeDevice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "employee_inventory_id"  => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employeeDevice = EmployeeDevice::find($id);
            if(!$employeeDevice) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $employeeDevice->id_number = $request->id_number ?? NULL;
            $employeeDevice->device_name = $request->device_name ?? NULL;
            $employeeDevice->device_type = $request->device_type ?? NULL;
            $employeeDevice->serial_number = $request->serial_number ?? NULL;
            $employeeDevice->save();
            
            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employeeDevice, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployeeDevice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "employee_inventory_id"  => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employeeDevice = EmployeeDevice::find($id);
            if(!$employeeDevice) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $employeeDevice->delete();
            
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employeeDevice, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}