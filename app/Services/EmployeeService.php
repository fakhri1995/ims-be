<?php 

namespace App\Services;

use App\Employee;
use App\EmployeeContract;
use App\EmployeeDevice;
use App\EmployeeInventory;
use App\EmployeePayslip;
use App\EmployeeSalary;
use App\EmployeeSalaryColumn;
use App\RecruitmentRole;
use Exception;
use App\Services\GlobalService;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmployeeService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    public function getEmployeePlacementsCount(){
        try{
            $employee = EmployeeContract::where("is_employee_active",1)
            ->selectRaw("placement, count(*) as placement_count")
            ->groupBy("placement")
            ->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeeRolesCount(){
        try{
            $employee = EmployeeContract::with("role")
            ->where("is_employee_active",1)
            ->selectRaw("role_id, count(*) as role_count")
            ->groupBy("role_id")
            ->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeeStatusesCount(){

        $employeeContracts = EmployeeContract::selectRaw("id as contract_id,employee_id,is_employee_active ,max(contract_start_at) as contract_start_at")
        ->groupBy("employee_id");

        $employee = Employee::joinSub($employeeContracts, "employee_contracts", function($query){
            $query->on("employees.id","=","employee_contracts.employee_id");
        })
        ->selectRaw("count(*) as status_count, is_employee_active")
        ->groupBy("is_employee_active")
        ->get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
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
            $employee = Employee::with(["user","id_card_photo",
                "contracts","contracts.role","contracts.contract_status",
                "inventories","inventories.devices","inventories.delivery_file","inventories.return_file"])->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employee->is_posted == false && $employee->created_by != auth()->user()->id && $employee->created_by != null){
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


        $keyword = $request->keyword ?? NULL;
        $role_ids = $request->role_ids ? explode(",",$request->role_ids) : NULL;
        $placements = $request->placements ? explode(",",$request->placements) : NULL;
        $contract_status_ids = $request->contract_status_ids ? explode(",",$request->contract_status_ids) : NULL;
        $is_employee_active = $request->is_employee_active == 0 ? 0 : 1;
        $rows = $request->rows ?? 5;

        $current_timestamp = date("Y-m-d");
        $employees = Employee::with(["contract" => function ($query) use($current_timestamp) {
            $query->selectRaw("*, DATEDIFF(contract_end_at, '$current_timestamp') as contract_end_countdown");
            $query->orderBy("contract_end_countdown", "asc");
        },"contract.role","contract.contract_status"]);

        // filter
        if($keyword) $employees = $employees->where("name","LIKE", "%$keyword%");
        $employees = $employees->whereHas("contract", function ($query) use ($role_ids,$placements,$contract_status_ids, $is_employee_active) {
            if($role_ids) $query->whereIn('role_id', $role_ids);
            if($placements) $query->whereIn('placement', $placements);
            if($contract_status_ids) $query->whereIn('contract_status_id', $contract_status_ids);
            // $query->where('is_employee_active',$is_employee_active);
            return $query;
        });

        

        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type ?? 'asc';
        if($sort_by == NULL or $sort_by == "") $employees = $employees->orderBy(EmployeeContract::select("contract_end_at")
                ->whereColumn("employees.id","employee_contracts.employee_id")->limit(1),$sort_type);
        if($sort_by == "name") $employees = $employees->orderBy('name',$sort_type);
        if($sort_by == "role") $employees = $employees->orderBy(RecruitmentRole::select("name")
                ->whereColumn("employee_roles.id","employees.employee_role_id"),$sort_type);

        
        $employees = $employees->paginate();

        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employees, "status" => 200];
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

    public function addEmployeeFromUser($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "user_id" => "numeric|required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
            $user_id = $request->user_id;
            $user = User::find($user_id);
            if(!$user) return ["success" => false, "message" => "Data User Tidak Ditemukan", "status" => 400];
            $employeeExist = Employee::where("user_id",$user_id)->first();
            if($employeeExist) return ["success" => false, "message" => "User telah memiliki data employee", "status" => 400];
        try{
            $employee = new Employee();
            $employee->name = $user->name ?? NULL;
            $employee->nip = $user->nip ?? NULL;
            $employee->nik = $request->nik ?? NULL;
            $employee->alias = $request->alias ?? NULL;
            $employee->email_office = $user->email ?? NULL;
            $employee->email_personal = $request->email_personal ?? NULL;
            $employee->domicile = $request->domicile ?? NULL;
            $employee->phone_number = $user->phone_number ?? NULL;
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
            $employee->is_posted = false;
            $employee->user_id = $user_id;

            $current_timestamp = date("Y-m-d H:i:s");
            $employee->created_at = $current_timestamp;
            $employee->updated_at = $current_timestamp;
            $employee->created_by = auth()->user()->id;
            $employee->save();

            
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

        $employeeContracts = EmployeeContract::with(["employee"])
            ->where(["employee_id" => $employee_id])
            ->orderBy('contract_start_at','desc')
            ->get();

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
            $employeeContract->annual_leave = $request->annual_leave ?? NULL;
            $employeeContract->benefit = $request->benefit ?? NULL;

            $total_pemasukan = $request->gaji_pokok;

            $employeeContract->gaji_pokok = $total_pemasukan;
            $employeeContract->bpjs_ks = $total_pemasukan * 0.05; //5%
            $employeeContract->bpjs_tk_jht = $total_pemasukan * 0.057; // 0.057%
            $employeeContract->bpjs_tk_jkk = $total_pemasukan * 0.0024; //0,24 %
            $employeeContract->bpjs_tk_jkm = $total_pemasukan * 0.003; //0,3 %
            $employeeContract->bpjs_tk_jp = $total_pemasukan * 0.03; //3%
            $employeeContract->pph21 = $request->pph21;

            if($employeeContract->is_employee_active == true) EmployeeContract::where('employee_id',$employee_id)->update(['is_employee_active' => 0]);  

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
            "employee_id" => "numeric|required",
            "device" => "array",
            // "device.*.id" => "required_with:device"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $id = $request->id;
            $employeeInventory = EmployeeInventory::with(["delivery_file","return_file"])->find($id);
            if(!$employeeInventory) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
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
            
            $devices = $request->device;
            foreach($devices as $device){
                $device = (object)$device;
                $employeeDevice = EmployeeDevice::where(["id" => $device->id, "employee_inventory_id" => $device->employee_inventory_id])->first();
                if($employeeDevice){
                    $employeeDevice->id_number = $device->id_number;
                    $employeeDevice->device_name = $device->device_name;
                    $employeeDevice->device_type = $device->device_type;
                    $employeeDevice->serial_number = $device->serial_number;
                    $employeeDevice->save();
                }
            }
            
            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employeeDevice, "status" => 200];
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

    // EMPLOYEE SALARY COLUMN
    public function getEmployeeSalaryColumn($request, $route_name)
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
        $employeeSalaryTemplate = EmployeeSalaryColumn::find($id);
        if(!$employeeSalaryTemplate) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeSalaryTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployeeSalaryColumns($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employeeSalaryTemplates = EmployeeSalaryColumn::get();

        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeeSalaryTemplates, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployeeSalaryColumn($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
            $employeeSalaryTemplate = new EmployeeSalaryColumn();
            $current_timestamp = date("Y-m-d H:i:s");
            $employeeSalaryTemplate->name = $request->name;
            $employeeSalaryTemplate->percent = $request->percent;
            $employeeSalaryTemplate->type = $request->type;
            $employeeSalaryTemplate->required = $request->required;
            $employeeSalaryTemplate->is_amount_for_bpjs = $request->is_amount_for_bpjs;
            $employeeSalaryTemplate->created_at = $current_timestamp;
            $employeeSalaryTemplate->updated_at = $current_timestamp;
            $employeeSalaryTemplate->created_by = auth()->user()->id;
            $employeeSalaryTemplate->save();

        try{
            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employeeSalaryTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateEmployeeSalaryColumn($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employeeSalaryTemplate = EmployeeSalaryColumn::find($id);
            if(!$employeeSalaryTemplate) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $current_timestamp = date("Y-m-d H:i:s");
            $employeeSalaryTemplate->name = $request->name;
            $employeeSalaryTemplate->percent = $request->percent;
            $employeeSalaryTemplate->type = $request->type;
            $employeeSalaryTemplate->required = $request->required;
            $employeeSalaryTemplate->is_amount_for_bpjs = $request->is_amount_for_bpjs;
            $employeeSalaryTemplate->updated_at = $current_timestamp;
            $employeeSalaryTemplate->save();
            
            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employeeSalaryTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployeeSalaryColumn($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employeeSalaryTemplate = EmployeeSalaryColumn::find($id);
            if(!$employeeSalaryTemplate) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $employeeSalaryTemplate->delete();
            
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employeeSalaryTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    // EMPLOYEE PAYSLIP
    public function getEmployeePayslip($request, $route_name)
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
        $employeePayslip = EmployeePayslip::with(["employee","salaries","salaries.column"])->find($id);
        if(!$employeePayslip) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeePayslip, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployeePayslips($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $keyword = $request->keyword ?? NULL;
            $role_ids = $request->role_ids ? explode(",",$request->role_ids) : NULL;
            $placements = $request->placements ? explode(",",$request->placements) : NULL;
            $is_posted = $request->is_posted ?? NULL;
            $pay_year = $request->pay_year ?? NULL;
            $pay_month = $request->pay_month ?? NULL;
            $rows = $request->rows ?? 5;


            $employee_id = $request->employee_id ?? NULL;
            $employeePayslips = EmployeePayslip::with([
                "employee" => function ($query) use ($keyword) {
                    if($keyword) $query->where("name","LIKE", "%$keyword%");
                    return $query;
                },
                "employee.contract" => function ($query) use ($role_ids, $placements) {
                    if($role_ids) $query->whereIn("role_id",$role_ids);
                    if($placements) $query->whereIn("placement",$placements);
                    return $query;
                },
                "employee.contract.role"]);

            if($employee_id) $employeePayslips = $employeePayslips->where("employee_id",$employee_id);

            // filter
            if($is_posted != NULL) $employeePayslips = $employeePayslips->where("is_posted", $is_posted);
            if($pay_year) $employeePayslips = $employeePayslips->whereYear("tanggal_dibayarkan", $pay_year);
            if($pay_month) $employeePayslips = $employeePayslips->whereMonth("tanggal_dibayarkan", $pay_month);
            // $employeePayslips = $employeePayslips->whereHas("employee", function ($query) use ($keyword) {
            //     if($keyword) $query->where("name","LIKE", "%$keyword%");
            //     return $query;
            // });
            // $employeePayslips = $employeePayslips->whereHas("employee.contract", function ($query) use ($role_ids, $placements) {
            //     if($role_ids) $query->whereIn("role_id",$role_ids);
            //     if($placements) $query->whereIn("placement",$placements);
            //     return $query;
            // });
            
            // sort
            $sort_by = $request->sort_by ?? NULL;
            $sort_type = $request->sort_type ?? 'desc';

            if($sort_by == "name") $employeePayslips = $employeePayslips->orderBy('name',$sort_type);

            $employeePayslips = $employeePayslips->paginate($rows);
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeePayslips, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeePayslipsEmpty($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            // "employee_id" => "numeric"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $employeePayslips = "";

        try{
            // $employee_id = $request->employee_id ?? NULL;
            // if(!$employee_id) $employeePayslips = EmployeePayslip::with("employee")->paginate();
            // else $employeePayslips = EmployeePayslip::with("employee")->where("employee_id",$employee_id)->paginate();

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeePayslips, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addEmployeePayslip($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
         
        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|integer|exists:App\Employee,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        if(!$employee) return ["success" => false, "message" => "Employee id tidak ditemukan", "status" => 400];

            $employeePayslip = new EmployeePayslip();
            $current_timestamp = date("Y-m-d H:i:s");
            $employeePayslip->employee_id = $request->employee_id;
            $employeePayslip->total_hari_kerja = $request->total_hari_kerja;
            $employeePayslip->tanggal_dibayarkan = $request->tanggal_dibayarkan;
            $employeePayslip->total_gross_penerimaan = 0; //sum of penerimaan
            $employeePayslip->total_gross_pengurangan = 0; //sum of pengeluaran
            $total_pemasukan = 0;
            $employeePayslip->gaji_pokok = $total_pemasukan;
            $employeePayslip->bpjs_ks = $total_pemasukan * 0.05; //5%
            $employeePayslip->bpjs_tk_jht = $total_pemasukan * 0.057; // 0.057%
            $employeePayslip->bpjs_tk_jkk = $total_pemasukan * 0.0024; //0,24 %
            $employeePayslip->bpjs_tk_jkm = $total_pemasukan * 0.003; //0,3 %
            $employeePayslip->bpjs_tk_jp = $total_pemasukan * 0.03; //3%
            $employeePayslip->pph21 = 0;
            $employeePayslip->take_home_pay = $employeePayslip->total_gross_penerimaan - $employeePayslip->total_gross_pengurangan;
            $employeePayslip->is_posted = $request->is_posted;
            $employeePayslip->created_at = $current_timestamp;
            $employeePayslip->updated_at = $current_timestamp;
            $employeePayslip->created_by = auth()->user()->id;
            $employeePayslip->save();

        try{
            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $employeePayslip, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateEmployeePayslip($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "salaries.*.employee_salary_column_id" => "numeric|required|exists:App\EmployeeSalaryColumn,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $id = $request->id;
            $employeePayslip = EmployeePayslip::find($id);
            if(!$employeePayslip) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $current_timestamp = date("Y-m-d H:i:s");

            $gaji_pokok = $request->gaji_pokok ?? 0;
            $total_gross_penerimaan = $gaji_pokok;
            $total_gross_pengurangan = $request->pph21;

            $employee_salary_column_ids = [];
            foreach($request->salaries as $salary){
                $salary = (object)$salary;
                $employeePayslipSalaryColumn = EmployeeSalaryColumn::find($salary->employee_salary_column_id);
                if(!$employeePayslipSalaryColumn) continue;

                $employee_salary_column_ids[] = $salary->employee_salary_column_id;
                $salaryValue = $salary->value;
                if($employeePayslipSalaryColumn->percent) $salaryValue = $employeePayslipSalaryColumn->percent/100 * $gaji_pokok;
                
                if($employeePayslipSalaryColumn->type == 1) $total_gross_penerimaan+=$salaryValue;
                else if($employeePayslipSalaryColumn->type == 2) $total_gross_pengurangan+=$salaryValue;

                $employeePayslipSalary = EmployeeSalary::updateOrCreate(
                    [
                        "employee_payslip_id" => $id,
                        "employee_salary_column_id" => $salary->employee_salary_column_id,
                    ],
                    [
                        "value" => $salaryValue,
                    ]
                );
            }

            $deleteEmployeeSalary = EmployeeSalary::where("employee_payslip_id",2)->whereNotIn("employee_salary_column_id",$employee_salary_column_ids)->delete();

            $total_pemasukan = $gaji_pokok;
            $employeePayslip->gaji_pokok = $total_pemasukan;
            $employeePayslip->bpjs_ks = $total_pemasukan * 0.05; //5%
            $employeePayslip->bpjs_tk_jht = $total_pemasukan * 0.057; // 0.057%
            $employeePayslip->bpjs_tk_jkk = $total_pemasukan * 0.0024; //0,24 %
            $employeePayslip->bpjs_tk_jkm = $total_pemasukan * 0.003; //0,3 %
            $employeePayslip->bpjs_tk_jp = $total_pemasukan * 0.03; //3%
            $employeePayslip->pph21 = $request->pph21;

            $employeePayslip->employee_id = $request->employee_id;
            $employeePayslip->total_hari_kerja = $request->total_hari_kerja;
            $employeePayslip->tanggal_dibayarkan = $request->tanggal_dibayarkan;
            $employeePayslip->total_gross_penerimaan = $total_gross_penerimaan; //sum of penerimaan
            $employeePayslip->total_gross_pengurangan = $total_gross_pengurangan; //sum of pengeluaran
            $employeePayslip->take_home_pay = $total_gross_penerimaan - $total_gross_pengurangan;
            $employeePayslip->is_posted = $request->is_posted;
            $employeePayslip->updated_at = $current_timestamp;
            $employeePayslip->save();
           
        try{
            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employeePayslip, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteEmployeePayslip($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $employeePayslip = EmployeePayslip::find($id);
            if(!$employeePayslip) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            $employeePayslip->delete();
            
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employeePayslip, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeePayslipStatusCount($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $current_month = date("m");
            $employeePayslip = EmployeePayslip::selectRaw("is_posted, count(*) as total")->groupBy("is_posted")->whereMonth("created_at",$current_month)->get();
            
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeePayslip, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function downloadEmployeePayslip($request, $route_name)
    {
        $data = ['payslip' => ""];
        // $pdf = PDF::setEncryption("password")->loadView('pdf.employee_payslip', $data);

        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled ', true);

        $pdf = new Dompdf($options);
        // $pdf->getCanvas();
        // ->get_cpdf()
        // ->setEncryption('test123', 'test456', ['print', 'modify', 'copy', 'add']);
        $html = view('pdf.employee_payslip')->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        $output = $pdf->output();
        $res = new Response ($output, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'attachment; filename="test.pdf"',
            'Content-Length' => strlen($output),
        ));
        // file_put_contents('output.pdf', $pdf->output());
        // return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $pdf->download('Payslip Test.pdf'), "status" => 200];
        return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $res, "status" => 200];
    }
    
}