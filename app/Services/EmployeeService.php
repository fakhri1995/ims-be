<?php 

namespace App\Services;

use App\Employee;
use App\EmployeeBenefit;
use App\EmployeeContract;
use App\EmployeeDevice;
use App\EmployeeInventory;
use App\EmployeePayslip;
use App\EmployeeSalary;
use App\EmployeeSalaryColumn;
use App\RecruitmentRole;
use App\Role;
use Exception;
use App\Services\GlobalService;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmployeeService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    public function getEmployeePlacementsCount(){
        try{
            $employee = EmployeeContract::whereHas('employee_last_contract', function($q){
                $q->where('is_posted',1);
            })
            ->selectRaw('placement, count(1) as placement_count')
            ->where('is_employee_active',1)
            ->groupBy('placement')
            ->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeeRolesCount(){
        try{
            $employee = EmployeeContract::with(['role'])
            ->whereHas('employee_last_contract', function($q){
                $q->where('is_posted',1);
            })
            ->selectRaw('role_id, count(1) as role_count')
            ->where('is_employee_active',1)
            ->groupBy('role_id')
            ->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeeStatusesCount(){

        // $employee = DB::table('employees as e')
        // ->join('employee_contracts as ec','e.last_contract_id','ec.id')
        // ->select(DB::raw('ec.is_employee_active, count(1) as status_count'))
        // ->where('e.is_posted', 1)
        // ->groupBy('ec.is_employee_active')
        // ->get();

        $employee = EmployeeContract::whereHas('employee_last_contract', function($q){
                $q->where('is_posted',1);
            })
            ->selectRaw('is_employee_active, count(1) as status_count')
            ->groupBy('is_employee_active')
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
                "contracts" => function ($q){
                    $q->orderBy('contract_start_at','desc');
                },"contracts.role","contracts.contract_status","contracts.salaries","contract.salaries.column",
                "inventories","inventories.devices","inventories.delivery_file","inventories.return_file","last_month_payslip"])->find($id);
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
        

        $validator = Validator::make($request->all(), [
            "rows" => "numeric",
            "limit" => "numeric",
            "sort_type" => "in:asc,desc",
            "is_employee_active" => "boolean"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword ?? NULL;
        $role_ids = $request->role_ids ? explode(",",$request->role_ids) : NULL;
        $placements = $request->placements ? explode(",",$request->placements) : NULL;
        $contract_status_ids = $request->contract_status_ids ? explode(",",$request->contract_status_ids) : NULL;
        $is_employee_active = $request->is_employee_active == NULL? 1 : $request->is_employee_active;
        $rows = $request->rows ?? 5;
        $page = $request->page ?? 1;
        $offset = $rows*$page-$rows;
        
        $current_timestamp = date("Y-m-d");
        $employees = Employee::with(["contract" => function ($query) use($current_timestamp) {
            $query->selectRaw("*, DATEDIFF(contract_end_at, '$current_timestamp') as contract_end_countdown");
            // $query->orderBy("contract_end_countdown", "desc");
        },"contract.role","contract.contract_status"])->where('is_posted',1);

        function filter($employees, $keyword, $is_employee_active, $role_ids, $placements, $contract_status_ids){
            if($keyword) $employees = $employees->where("name","LIKE", "%$keyword%");
            $employees = $employees->whereHas("contract", function ($query) use($is_employee_active,$role_ids,$placements,$contract_status_ids) {
                if($is_employee_active != NULL) $query->where('is_employee_active', $is_employee_active);
                if($role_ids) $query->whereIn('role_id', $role_ids);
                if($placements) $query->whereIn('placement', $placements);
                if($contract_status_ids) $query->whereIn('contract_status_id', $contract_status_ids);
                return $query;
            });
            return $employees;
        }

        // filter
        $employees = filter($employees, $keyword, $is_employee_active, $role_ids, $placements, $contract_status_ids);
        
        // if($is_employee_active == 0) $employees = $employees->orWhereNull('last_contract_id');
        
        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type == 'desc' ? 'desc' : 'asc';
        
        if($sort_by == NULL or $sort_by == "" or $sort_by == "contract_end_countdown") {
            $employees = $employees->orderBy(EmployeeContract::select("contract_end_at")
                ->whereColumn("employees.last_contract_id","employee_contracts.id"),$sort_type);
        }
        if($sort_by == "name") $employees = $employees->orderBy('name',$sort_type);
        if($sort_by == "role") $employees = $employees->orderBy(RecruitmentRole::selectRaw("id,name")
                ->whereColumn("recruitment_roles.id","employee_contracts.role_id"),$sort_type);
        
        
        if($is_employee_active){
            $employeesDraft = Employee::with(["contract" => function ($query) use($current_timestamp) {
                $query->selectRaw("*, DATEDIFF(contract_end_at, '$current_timestamp') as contract_end_countdown");
            },"contract.role","contract.contract_status"])->where('updating_by',auth()->user()->id)->where('is_posted',0);
            $employeesDraft = filter($employeesDraft, $keyword, NULL, $role_ids, $placements, $contract_status_ids);
            $employeesDraft = $employeesDraft->orWhere('last_contract_id', NULL);

            $employeesDraftCount = $employeesDraft->count();
            $employeeCount = $employees->count();
            $employeeTotalCount = $employeesDraftCount + $employeeCount;
            $employeesDraft = $employeesDraft->offset($offset)->limit($rows)->get()->toArray();
            
            if(count($employeesDraft) < $rows){
                $rowDiff = $rows - count($employeesDraft);
                $offset2 = $rows * $page - $employeesDraftCount - $rows; 
                if($offset2 < 0) $offset2 = 0;
                $employees = $employees->offset($offset2)->limit($rowDiff)->get()->toArray();
            }else{
                $employees = [];
            }
    
            $employeeMerge = array_merge($employeesDraft, $employees);
            
            $last_page = ceil($employeeTotalCount/5);
    
            $data = [
                "current_page" => $page,
                "data" => $employeeMerge,
                "first_page_url" => "http://service-migsys.patar/getEmployees?page=1",
                "from" => count($employeeMerge) <= 0 ? NULL : $page*$rows-$rows+1,
                "last_page" => $last_page,
                "last_page_url" => "http://service-migsys.patar/getEmployees?page=$last_page",
                "next_page_url" => $page < $last_page ? $page + 1 : NULL,
                "path" => "http://service-migsys.patar/getEmployees",
                "per_page" => $rows,
                "prev_page_url" => $page <= 1 ? NULL : $page - 1,
                "to" => count($employeeMerge) <= 0 ? NULL : $page*$rows + count($employeeMerge) - $rows,
                "total" => $employeeTotalCount
            ];
        }else{
            $data = $employees->paginate($rows);
        }        
        
        

        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getEmployeesDraft($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        

        $validator = Validator::make($request->all(), [
            "rows" => "numeric",
            "limit" => "numeric",
            "sort_type" => "in:asc,desc"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $rows = $request->rows ?? 5;

        $current_timestamp = date("Y-m-d");
        $employees = Employee::with(["contract" => function ($query) use($current_timestamp) {
            $query->selectRaw("*, DATEDIFF(contract_end_at, '$current_timestamp') as contract_end_countdown");
            $query->orderBy("contract_end_countdown", "asc");
        },"contract.role","contract.contract_status"]);

        $employees = $employees->where('is_posted', 0);
        $employees = $employees->where('updating_by', auth()->user()->id)->paginate($rows);

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
            $employee->updating_by = auth()->user()->id;
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
            $employee->updating_by = auth()->user()->id;
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
            "is_posted" => "boolean",
            "user_id" => "numeric|min:1"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
            $id = $request->id;
            $employee = Employee::with("id_card_photo")->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($employee->updating_by != auth()->user()->id && $employee->updating_by != NULL){
                return ["success" => false, "message" => "Draft dibuat oleh user lain", "status" => 400]; 
            }

            $user_id = $request->user_id;
            if($user_id != NULL && $employee->user_id != NULL && $employee->user_id != $user_id) return ["success" => false, "message" => "Employee telah memiliki relasi ke user", "status" => 400]; 
            if($user_id != NULL){
                $user = User::find($user_id);
                if(!$user) return ["success" => false, "message" => "User Tidak Ditemukan", "status" => 400];
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
            $employee->acc_number_bukopin = $request->acc_number_bukopin ?? NULL;
            $employee->acc_name_another = $request->acc_name_another ?? NULL;
            $employee->acc_number_another = $request->acc_number_another ?? NULL;
            $employee->user_id = $user_id;
            $employee->is_posted = $request->is_posted;
            if(!$request->is_posted) $employee->updating_by = auth()->user()->id;
            else $employee->updating_by = NULL;
            $employee->updated_at = date('Y-m-d H:i:s');
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
            
            $employee = Employee::with("id_card_photo")->find($id);

            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employee, "status" => 200];
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
        $employeeContract = EmployeeContract::with(["employee","role","contract_status","contract_file","salaries","salaries.column"])->find($id);
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
            ->orderBy('is_employee_active','desc')
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
            $employeeContract->employee_id = $employee_id;
            $employeeContract->save();

            $employeeSalaryColumn = EmployeeSalaryColumn::where("required",1)->get();

            foreach($employeeSalaryColumn as $salary){
                $employeeSalary = EmployeeBenefit::updateOrCreate(
                    [
                        "employee_contract_id" => $employeeContract->id,
                        "employee_salary_column_id" => $salary->id,
                    ],
                    [
                        "value" => 0,
                        "is_amount_for_bpjs" => $salary->is_amount_for_bpjs
                    ]
                );
            }


            if($employee->last_contract_id == NULL) {
                $employee->last_contract_id = $employeeContract->id;
                $employee->save();
            }

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

            $is_employee_active = $request->is_employee_active ?? NULL;

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
            $salaries = $request->salaries ?? [];
            



            $current_timestamp = date("Y-m-d H:i:s");

            $total_pemasukan = $request->gaji_pokok;



            
            $gaji_pokok = $request->gaji_pokok ?? 0;
            $total_amount_for_bpjs = $gaji_pokok ?? 0;
            $employee_salary_column_ids = [];
            foreach($salaries as $salary){
                $salary = (object)$salary;
                $employeeSalaryColumn = EmployeeSalaryColumn::find($salary->employee_salary_column_id);
                if(!$employeeSalaryColumn) continue;
                
                $employee_salary_column_ids[] = $salary->employee_salary_column_id;
                $salaryValue = $salary->value;
                
                if($employeeSalaryColumn->percent > 0) {
                    $salaryValue = $employeeSalaryColumn->percent/100 * $gaji_pokok;}

                
                
                if($salary->is_amount_for_bpjs) $total_amount_for_bpjs+=$salaryValue;

                $employeeSalary = EmployeeBenefit::updateOrCreate(
                    [
                        "employee_contract_id" => $id,
                        "employee_salary_column_id" => $salary->employee_salary_column_id,
                    ],
                    [
                        "value" => $salaryValue,
                        "is_amount_for_bpjs" => $salary->is_amount_for_bpjs
                    ]
                );
            }

            $deleteEmployeeSalary = EmployeeBenefit::where("employee_contract_id",$id)->whereNotIn("employee_salary_column_id",$employee_salary_column_ids)->delete();

            $employeeContract->gaji_pokok = $total_pemasukan;
            $employeeContract->bpjs_ks = $total_amount_for_bpjs * 0.05; //5%
            $employeeContract->bpjs_tk_jht = $total_amount_for_bpjs * 0.057; // 0.057%
            $employeeContract->bpjs_tk_jkk = $total_amount_for_bpjs * 0.0024; //0,24 %
            $employeeContract->bpjs_tk_jkm = $total_amount_for_bpjs * 0.003; //0,3 %
            $employeeContract->bpjs_tk_jp = $total_amount_for_bpjs * 0.03; //3%
            $employeeContract->pph21 = $request->pph21;


            $employeeContract->is_employee_active = $is_employee_active;
            $employeeContract->updated_at = date('Y-m-d H:i:s');
            $employeeContract->save();
            
            if($is_employee_active == true) {
                EmployeeContract::where('employee_id',$employee_id)->where('id',"!=",$id)->update(['is_employee_active' => 0]);
                $employeeContract->employee->last_contract_id = $id;
                $employeeContract->employee->save();
            }  

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
            return ["success" => true, "message" => "Data Berhasil Diupdate", "data" => $employeeContract, "status" => 200];
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
            $employeeInventory->updated_at = date('Y-m-d H:i:s');
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
            
            $devices = $request->device ?? [];
            $employeeDevice = [];
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

            $employeeInventory = EmployeeInventory::with(["devices","delivery_file","return_file"])->find($id);
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
            $employeeDevice->updated_at = date('Y-m-d H:i:s');
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
            "is_amount_for_bpjs" => "required|boolean",
            "required" => 'boolean',
            "type" => "in:1,2",
            "percent" => "numeric|min:0",
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
            "is_amount_for_bpjs" => "required|boolean",
            "required" => 'boolean',
            "type" => "in:1,2",
            "percent" => "numeric|min:0",
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

    public function getEmployeesPayslip($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        

        $validator = Validator::make($request->all(), [
            "rows" => "numeric",
            "limit" => "numeric",
            "sort_type" => "in:asc,desc",
            "is_employee_active" => "boolean",
            "is_posted" => "boolean"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $keyword = $request->keyword ?? NULL;
        $role_ids = $request->role_ids ? explode(",",$request->role_ids) : NULL;
        $placements = $request->placements ? explode(",",$request->placements) : NULL;
        $contract_status_ids = $request->contract_status_ids ? explode(",",$request->contract_status_ids) : NULL;
        $is_employee_active = $request->is_employee_active == NULL? 1 : $request->is_employee_active;
        $rows = $request->rows ?? 5;
        $is_posted = $request->is_posted ?? NULL;

        $current_timestamp = date("Y-m-d");
        $employees = Employee::with(["contract","contract.role","contract.contract_status","last_month_payslip"])->where('is_posted',1);

        function filter($employees, $keyword, $is_employee_active, $role_ids, $placements, $contract_status_ids){
            if($keyword) $employees = $employees->where("name","LIKE", "%$keyword%");
            $employees = $employees->whereHas("contract", function ($query) use($role_ids,$placements,$contract_status_ids) {
                $query->where('is_employee_active', 1);
                if($role_ids) $query->whereIn('role_id', $role_ids);
                if($placements) $query->whereIn('placement', $placements);
                if($contract_status_ids) $query->whereIn('contract_status_id', $contract_status_ids);
                return $query;
            });
            return $employees;
        }
        
        // filter
        $employees = filter($employees, $keyword, $is_employee_active, $role_ids, $placements, $contract_status_ids);
        if($is_posted === 1 || $is_posted === 0) $employees = $employees->whereHas('last_month_payslip', function($q) use ($is_posted){
            $q->where("is_posted",$is_posted);
        });

        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type == 'desc' ? 'desc' : 'asc';
        
        if($sort_by == "name") $employees = $employees->orderBy('name',$sort_type);
        if($sort_by == "role") $employees = $employees->orderBy(RecruitmentRole::select("name")
                ->whereColumn("employee_roles.id","employees.employee_role_id"),$sort_type);

        $data = $employees->paginate($rows);
        
        
        

        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getEmployeePayslips($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|required",
            "rows" => "numeric",
            "limit" => "numeric"
        ]);

        $rows = $request->rows ?? 5;

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $employee_id = $request->employee_id;

        $employeePayslips = EmployeePayslip::where('employee_id',$employee_id)
        ->orderBy('year','desc')
        ->orderBy('month','desc')
        ->paginate($rows);
            
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
        if($route_name != "BYPASS"){
            $access = $this->globalService->checkRoute($route_name);
            if($access["success"] === false) return $access;
        }
         
        $validator = Validator::make($request->all(), [
            "employee_id" => "numeric|integer|exists:App\Employee,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $employee_id = $request->employee_id;
        $employee = Employee::with("contract")->find($employee_id);
        if(!$employee) return ["success" => false, "message" => "Employee id tidak ditemukan", "status" => 400];
        if(!$employee->contract) return ["success" => false, "message" => "Employee contract belum ada", "status" => 400];

            $employeePayslip = new EmployeePayslip();
            $current_timestamp = date("Y-m-d H:i:s");
            $employeePayslip->employee_id = $request->employee_id;
            $employeePayslip->total_hari_kerja = $request->total_hari_kerja;
            $employeePayslip->tanggal_dibayarkan = $request->tanggal_dibayarkan;
            $employeePayslip->gaji_pokok = $employee->contract->gaji_pokok;
            $employeePayslip->bpjs_ks = $employee->contract->bpjs_ks; //5%
            $employeePayslip->bpjs_tk_jht = $employee->contract->bpjs_tk_jht; // 0.057%
            $employeePayslip->bpjs_tk_jkk = $employee->contract->bpjs_tk_jkk; //0,24 %
            $employeePayslip->bpjs_tk_jkm = $employee->contract->bpjs_tk_jkm; //0,3 %
            $employeePayslip->bpjs_tk_jp = $employee->contract->bpjs_tk_jp; //3%
            $employeePayslip->pph21 = $employee->contract->pph21;
            $employeePayslip->is_posted = $request->is_posted;
            $employeePayslip->month = $request->month;
            $employeePayslip->year = $request->year;
            $employeePayslip->created_at = $current_timestamp;
            $employeePayslip->updated_at = $current_timestamp;
            $employeePayslip->created_by = auth()->user()->id;

            
            $total_gross_penerimaan = $employeePayslip->gaji_pokok; //sum of penerimaan

            $total_gross_pengurangan = $employeePayslip->pph21 +
                $employeePayslip->bpjs_ks +
                $employeePayslip->bpjs_tk_jht +
                $employeePayslip->bpjs_tk_jkk +
                $employeePayslip->bpjs_tk_jkm +
                $employeePayslip->bpjs_tk_jp;

            $salaries = $employee->contract->salaries ?? [];
            $employeePayslipSalary = [];
            foreach($salaries as $salary){
                $salary = (object)$salary;
                $employeePayslipSalaryColumn = EmployeeSalaryColumn::find($salary->employee_salary_column_id);
                if(!$employeePayslipSalaryColumn) continue;

                $salaryValue = $salary->value;

                if($employeePayslipSalaryColumn->type == 1) {
                    $total_gross_penerimaan+=$salaryValue;
                }
                else if($employeePayslipSalaryColumn->type == 2) $total_gross_pengurangan+=$salaryValue;

                $employeePayslipSalary[] = [
                    [
                        "employee_salary_column_id" => $salary->employee_salary_column_id,
                    ],
                    [
                        "value" => $salaryValue,
                        "is_amount_for_bpjs" => $salary->is_amount_for_bpjs
                    ]
                ];
            }

            $employeePayslip->total_gross_penerimaan = $total_gross_penerimaan;
            $employeePayslip->total_gross_pengurangan = $total_gross_pengurangan;

            $employeePayslip->take_home_pay = $employeePayslip->total_gross_penerimaan - $employeePayslip->total_gross_pengurangan;
            $employeePayslip->save();

            foreach($employeePayslipSalary as $eps){
                $eps[0]['employee_payslip_id'] = $employeePayslip->id;
                EmployeeSalary::updateOrCreate($eps[0], $eps[1]);
            }

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
            $total_gross_pengurangan = $request->pph21 ?? 0;
            $total_amount_for_bpjs = $gaji_pokok ?? 0;
            $employee_salary_column_ids = [];
            $salaries = $request->salaries ?? [];

            $deleteEmployeeSalary = EmployeeSalary::where("employee_payslip_id",$id)->delete();
            foreach($salaries as $salary){
                $salary = (object)$salary;
                $employeePayslipSalaryColumn = EmployeeSalaryColumn::find($salary->employee_salary_column_id);
                if(!$employeePayslipSalaryColumn) continue;

                $employee_salary_column_ids[] = $salary->employee_salary_column_id;
                $salaryValue = $salary->value;
                if($employeePayslipSalaryColumn->percent > 0) $salaryValue = $employeePayslipSalaryColumn->percent/100 * $gaji_pokok;

                if($employeePayslipSalaryColumn->type == 1) {
                    $total_gross_penerimaan+=$salaryValue;
                    if($salary->is_amount_for_bpjs) $total_amount_for_bpjs+=$salaryValue;
                }
                else if($employeePayslipSalaryColumn->type == 2) $total_gross_pengurangan+=$salaryValue;

                $employeePayslipSalary = EmployeeSalary::updateOrCreate(
                    [
                        "employee_payslip_id" => $id,
                        "employee_salary_column_id" => $salary->employee_salary_column_id,
                    ],
                    [
                        "value" => $salaryValue,
                        "is_amount_for_bpjs" => $salary->is_amount_for_bpjs
                    ]
                );
            }

            

            $employeePayslip->gaji_pokok = $gaji_pokok;
            $employeePayslip->bpjs_ks = $total_amount_for_bpjs * 0.05; //5%
            $employeePayslip->bpjs_tk_jht = $total_amount_for_bpjs * 0.057; // 0.057%
            $employeePayslip->bpjs_tk_jkk = $total_amount_for_bpjs * 0.0024; //0,24 %
            $employeePayslip->bpjs_tk_jkm = $total_amount_for_bpjs * 0.003; //0,3 %
            $employeePayslip->bpjs_tk_jp = $total_amount_for_bpjs * 0.03; //3%
            $employeePayslip->pph21 = $request->pph21;

            $total_gross_pengurangan = $total_gross_pengurangan +
                $employeePayslip->bpjs_ks +
                $employeePayslip->bpjs_tk_jht +
                $employeePayslip->bpjs_tk_jkk +
                $employeePayslip->bpjs_tk_jkm +
                $employeePayslip->bpjs_tk_jp;

            $employeePayslip->employee_id = $request->employee_id;
            $employeePayslip->total_hari_kerja = $request->total_hari_kerja;
            $employeePayslip->tanggal_dibayarkan = $request->tanggal_dibayarkan;
            $employeePayslip->total_gross_penerimaan = $total_gross_penerimaan; //sum of penerimaan
            $employeePayslip->total_gross_pengurangan = $total_gross_pengurangan;
            $employeePayslip->take_home_pay = $total_gross_penerimaan - $total_gross_pengurangan;
            $employeePayslip->is_posted = $request->is_posted;
            $employeePayslip->month = $request->month;
            $employeePayslip->year = $request->year;
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
            $current_year = date("Y");
            $current_month = date("m");
            $employeePayslip = EmployeePayslip::selectRaw("is_posted, count(*) as total")->groupBy("is_posted")->whereMonth("year",$current_year)->whereMonth("month",$current_month)->get();
            
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employeePayslip, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    // public function downloadEmployeePayslip($request, $route_name)
    // {
    //     $data = ['payslip' => ""];
    //     // $pdf = PDF::setEncryption("password")->loadView('pdf.employee_payslip', $data);

    //     $options = new Options();
    //     $options->set('defaultFont', 'Courier');
    //     $options->set('isHtml5ParserEnabled ', true);

    //     $pdf = new Dompdf($options);
    //     // $pdf->getCanvas();
    //     // ->get_cpdf()
    //     // ->setEncryption('test123', 'test456', ['print', 'modify', 'copy', 'add']);
    //     $html = view('pdf.employee_payslip')->render();
    //     $pdf->loadHtml($html);
    //     $pdf->setPaper('A4', 'landscape');
    //     $pdf->render();
    //     $output = $pdf->output();
    //     $res = new Response ($output, 200, array(
    //         'Content-Type' => 'application/pdf',
    //         'Content-Disposition' =>  'attachment; filename="test.pdf"',
    //         'Content-Length' => strlen($output),
    //     ));
    //     // file_put_contents('output.pdf', $pdf->output());
    //     // return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $pdf->download('Payslip Test.pdf'), "status" => 200];
    //     return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $res, "status" => 200];
    // }

    public function postedEmployeeLastPayslips($request, $route_name){
        

        $lastDate = explode("-",date("Y-m",strtotime("-1 month")));
        $year = $lastDate[0]; //current month - 1
        $month = $lastDate[1];
        $employeePayslips = EmployeePayslip::where(["year" => $year, "month" => $month])->update(['is_posted' => true]);

        return ["success" => true, "message" => "Berhasil Menerbitkan Payslip", "data" => "", "status" => 200];
    } 

    public function downloadEmployeePayslip($request, $route_name)
    {   

        $id = $request->id;
        $password = $request->password;
        
        $employeePayslip = EmployeePayslip::with('employee','employee.contract','employee.contract.role',
        "employee.contract.contract_status")->find($id);
        if(!$employeePayslip) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400];
        $isUserSuperAdmin = $this->globalService->isUserSuperAdmin();
        if(!$isUserSuperAdmin && $employeePayslip->employee_id != auth()->user()->id) return ["success" => false, "message" => "Payslip tidak sesuai dengan employee", "status" => 400];
        if(!$isUserSuperAdmin && !Hash::check(auth()->user()->password,$password)) return ["success" => false, "message" => "Validasi kata sandiA salah", "status" => 400];
        // dd($employeePayslip);
        
        

        $options = new Options();
        $options->set('isHtml5ParserEnabled ', true);
        $options->set("isPhpEnabled", true);
        $options->set("isRemoteEnabled", true);
        $options->set("dpi", 150);

        $pdf = new Dompdf($options);
        
        $html = view('pdf.employee_payslip', ["payslip" => $employeePayslip])->render();
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml($html);
        $pdf->render();
        // $pdf->getCanvas()
        //     ->get_cpdf()
        //     ->setEncryption('test123', 'test456', ['print', 'modify', 'copy', 'add']);
        $output = $pdf->output();
        $res = new Response ($output, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'attachment; filename="test.pdf"',
            'Content-Length' => strlen($output),
        ));
        
        return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $res, "status" => 200];
    }

    public function raiseLastPeriodPayslip(Request $request)
    {

                
            $lastDate = explode("-",date("Y-m",strtotime("-1 month")));
            $year = $lastDate[0]; //current month - 1
            $month = $lastDate[1];

            $employee = Employee::with('last_month_payslip')->get();
            
            $payload = new Request([
                "total_hari_kerja" => 25,
                "tanggal_dibayarkan" => "$year-$month-25",
                "is_posted" => false,
                "month" => $month,
                "year" => $year
            ]);
            $payload->setMethod("POST");

            $payslipData = [];
            foreach($employee as $e){
                if(!$e->last_month_payslip){
                    $payload->replace(['employee_id' => $e->id]);
                    $employeePayslip = $this->addEmployeePayslip($payload,"BYPASS");
                    if($employeePayslip["success"]) $payslipData[] = $employeePayslip["data"];
                }
            }

            try{
            return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => $payslipData, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

        
    }

    
}