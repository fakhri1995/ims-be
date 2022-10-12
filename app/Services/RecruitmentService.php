<?php 

namespace App\Services;

use App\Recruitment;
use App\RecruitmentEmailTemplate;
use App\RecruitmentJalurDaftar;
use App\RecruitmentRole;
use App\RecruitmentRoleType;
use App\RecruitmentStage;
use App\RecruitmentStatus;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecruitmentService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    //RECRUITMENT SECTION
    public function getRecruitment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitment = Recruitment::with(['role','role.type','jalur_daftar','stage','status'])->find($id);
        if(!$recruitment) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getRecruitments(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:id,name,role,jalur_daftar,stage,status",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword ?? NULL;
        $recruitment_role_id = $request->recruitment_role_id ? explode(",",$request->recruitment_role_id) : NULL;
        $recruitment_jalur_daftar_id = $request->recruitment_jalur_daftar_id ? explode(",",$request->recruitment_jalur_daftar_id) : NULL;
        $recruitment_stage_id = $request->recruitment_stage_id ? explode(",",$request->recruitment_stage_id) : NULL;
        $recruitment_status_id = $request->recruitment_status_id ? explode(",",$request->recruitment_status_id) : NULL;

        $rows = $request->rows ?? 5;
        $recruitments = Recruitment::with(['role','role.type','jalur_daftar','stage','status']);

        // filter
        if($keyword) $recruitments = $recruitments->where("name","LIKE", "%$keyword%");
        if($recruitment_role_id) $recruitments = $recruitments->whereIn("recruitment_role_id", $recruitment_role_id);
        if($recruitment_jalur_daftar_id) $recruitments = $recruitments->whereIn("recruitment_jalur_daftar_id", $recruitment_jalur_daftar_id);
        if($recruitment_stage_id) $recruitments = $recruitments->whereIn("recruitment_stage_id", $recruitment_stage_id);
        if($recruitment_status_id) $recruitments = $recruitments->whereIn("recruitment_status_id", $recruitment_status_id);
        

        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->get('sort_type','asc');
        if($sort_by == "id") $recruitments = $recruitments->orderBy('id',$sort_type);
        if($sort_by == "name") $recruitments = $recruitments->orderBy('name',$sort_type);
        if($sort_by == "role") $recruitments = $recruitments->orderBy(RecruitmentRole::select("name")
                ->whereColumn("recruitment_roles.id","recruitments.recruitment_role_id"),$sort_type);
        if($sort_by == "jalur_daftar") $recruitments = $recruitments->orderBy(RecruitmentJalurDaftar::select("name")
                ->whereColumn("recruitment_jalur_daftars.id","recruitments.recruitment_jalur_daftar_id"),$sort_type);
        if($sort_by == "stage") $recruitments = $recruitments->orderBy(RecruitmentStage::select("name")
                ->whereColumn("recruitment_stages.id","recruitments.recruitment_stage_id"),$sort_type);
        if($sort_by == "status") $recruitments = $recruitments->orderBy(RecruitmentStatus::select("name")
                ->whereColumn("recruitment_statuses.id","recruitments.recruitment_status_id"),$sort_type);

        $recruitments = $recruitments->paginate($rows);
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitments, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email",
            "university" => "required",
            "recruitment_role_id" => "required|numeric",
            "recruitment_jalur_daftar_id" => "required|numeric",
            "recruitment_stage_id" => "required|numeric",
            "recruitment_status_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{

            $recruitment_role_id = $request->recruitment_role_id ?? NULL;
            $recruitment_jalur_daftar_id = $request->recruitment_jalur_daftar_id ?? NULL;
            $recruitment_stage_id = $request->recruitment_stage_id ?? NULL;
            $recruitment_status_id = $request->recruitment_status_id ?? NULL;

            
            
            if(!RecruitmentRole::find($recruitment_role_id)) return ["success" => false, "message" => "Recruitment Role yang dipilih tidak tersedia", "status" => 400];
            if(!RecruitmentJalurDaftar::find($recruitment_jalur_daftar_id)) return ["success" => false, "message" => "Recruitment Jalur daftar yang dipilih tidak tersedia", "status" => 400];
            if(!RecruitmentStage::find($recruitment_stage_id)) return ["success" => false, "message" => "Recruitment Stage yang dipilih tidak tersedia", "status" => 400];
            if(!RecruitmentStatus::find($recruitment_status_id)) return ["success" => false, "message" => "Recruitment Status yang dipilih tidak tersedia", "status" => 400];

        
            $recruitment = new Recruitment();
            $recruitment->name = $request->name ?? "";
            $recruitment->email = $request->email ?? "";
            $recruitment->university = $request->university ?? "";
            $recruitment->recruitment_role_id = $request->recruitment_role_id ?? "";
            $recruitment->recruitment_jalur_daftar_id = $request->recruitment_jalur_daftar_id ?? "";
            $recruitment->recruitment_stage_id = $request->recruitment_stage_id ?? "";
            $recruitment->recruitment_status_id = $request->recruitment_status_id ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitment->created_at = $current_timestamp;
            $recruitment->updated_at = $current_timestamp;
            $recruitment->created_by = auth()->user()->id ?? "";

            $recruitment->save();

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "name" => "required",
            "email" => "required|email",
            "university" => "required",
            "recruitment_role_id" => "required|numeric",
            "recruitment_jalur_daftar_id" => "required|numeric",
            "recruitment_stage_id" => "required|numeric",
            "recruitment_status_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            $recruitment = Recruitment::find($id);
            if(!$recruitment){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }


            $recruitment_role_id = $request->recruitment_role_id ?? NULL;
            $recruitment_jalur_daftar_id = $request->recruitment_jalur_daftar_id ?? NULL;
            $recruitment_stage_id = $request->recruitment_stage_id ?? NULL;
            $recruitment_status_id = $request->recruitment_status_id ?? NULL;
            
            if(!RecruitmentRole::find($recruitment_role_id)) return ["success" => false, "message" => "Recruitment Role yang dipilih tidak tersedia", "status" => 400];
            if(!RecruitmentJalurDaftar::find($recruitment_jalur_daftar_id)) return ["success" => false, "message" => "Recruitment Jalur daftar yang dipilih tidak tersedia", "status" => 400];
            if(!RecruitmentStage::find($recruitment_stage_id)) return ["success" => false, "message" => "Recruitment Stage yang dipilih tidak tersedia", "status" => 400];
            if(!RecruitmentStatus::find($recruitment_status_id)) return ["success" => false, "message" => "Recruitment Status yang dipilih tidak tersedia", "status" => 400];
            
            
            $recruitment->name = $request->name ?? $recruitment->name;
            $recruitment->email = $request->email ?? $recruitment->email;
            $recruitment->university = $request->university ?? $recruitment->university;
            $recruitment->recruitment_role_id = $request->recruitment_role_id ?? "";
            $recruitment->recruitment_jalur_daftar_id = $request->recruitment_jalur_daftar_id ?? "";
            $recruitment->recruitment_stage_id = $request->recruitment_stage_id ?? "";
            $recruitment->recruitment_status_id = $request->recruitment_status_id ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitment->updated_at = $current_timestamp;

            $recruitment->save();

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRecruitment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitment = Recruitment::find($id);
            if(!$recruitment){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }
            $recruitment->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCountRecruitment($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{

            $recruitments_count = Recruitment::count(); 
            $recruitment_roles_count = RecruitmentRole::count();

            $recruitments = [
                "recruitments_count" => $recruitments_count,
                "recruitment_roles_count" => $recruitment_roles_count
            ];

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitments, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

    }

    //END OF RECRUITMENT SECTION

    //RECRUITMENT ROLES SECTION
    public function getRecruitmentRole(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentRole = RecruitmentRole::with(['type'])->withCount('recruitments')->find($id);
        if(!$recruitmentRole) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentRole, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getRecruitmentRoles(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:id,name,role_type,recruitments_count",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        
        $keyword = $request->keyword ?? NULL;
        $recruitment_role_type_id = $request->recruitment_role_type_id ? explode(",",$request->recruitment_role_type_id) : NULL;

        $rows = $request->rows ?? 5;
        $recruitmentRoles = RecruitmentRole::with(['type'])->withCount('recruitments');
        
        // filter
        if($keyword) $recruitmentRoles = $recruitmentRoles->where("name","LIKE", "%$keyword%");
        if($recruitment_role_type_id) $recruitmentRoles = $recruitmentRoles->whereIn("recruitment_role_type_id", $recruitment_role_type_id);
        
        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->get('sort_type','asc');
        if($sort_by == "id") $recruitmentRoles = $recruitmentRoles->orderBy('id',$sort_type);
        if($sort_by == "name") $recruitmentRoles = $recruitmentRoles->orderBy('name',$sort_type);
        if($sort_by == "role_type") $recruitmentRoles = $recruitmentRoles->orderBy(RecruitmentRole::select("name")
                ->whereColumn("recruitment_role_types.id","recruitment_roles.recruitment_role_type_id"),$sort_type);
        if($sort_by == "recruitments_count") $recruitmentRoles = $recruitmentRoles->orderBy('recruitments_count',$sort_type);


        $recruitmentRoles = $recruitmentRoles->paginate($rows);
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentRoles, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRecruitmentRolesList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $recruitmentRoles = RecruitmentRole::get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentRoles, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitmentRole(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "alias" => "required",
            "recruitment_role_type_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $recruitment_role_type_id = $request->recruitment_role_type_id ?? NULL;
            if(!RecruitmentRoleType::find($recruitment_role_type_id)) return ["success" => false, "message" => "Recruitment Role Type yang dipilih tidak tersedia", "status" => 400];


            $recruitmentRole = new RecruitmentRole();
            $recruitmentRole->name = $request->name ?? "";
            $recruitmentRole->alias = $request->alias ?? "";
            $recruitmentRole->recruitment_role_type_id = $request->recruitment_role_type_id ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentRole->created_at = $current_timestamp;
            $recruitmentRole->updated_at = $current_timestamp;

            $recruitmentRole->save();

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitmentRole, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitmentRole(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "name" => "required",
            "alias" => "required",
            "recruitment_role_type_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentRole = RecruitmentRole::find($id);
            if(!$recruitmentRole){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }

            $recruitment_role_type_id = $request->recruitment_role_type_id ?? NULL;
            if(!RecruitmentRoleType::find($recruitment_role_type_id)) return ["success" => false, "message" => "Recruitment Role Type yang dipilih tidak tersedia", "status" => 400];

            

            $recruitmentRole->name = $request->name ?? $recruitmentRole->name;
            $recruitmentRole->alias = $request->alias ?? $recruitmentRole->alias;
            $recruitmentRole->recruitment_role_type_id = $request->recruitment_role_type_id ?? $recruitmentRole->recruitment_role_type_id;


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentRole->updated_at = $current_timestamp;

            $recruitmentRole->save();

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitmentRole, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRecruitmentRole(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentRole = RecruitmentRole::find($id);
            if(!$recruitmentRole){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }
            $recruitmentRole->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $recruitmentRole, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRecruitmentRoleTypesList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $recruitmentRoleTypes = RecruitmentRoleType::get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentRoleTypes, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    //END OF RECRUITMENT ROLES SECTION

    //RECRUITMENT STATUS SECTION
    public function getRecruitmentStatus(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentStatus = RecruitmentStatus::withCount('recruitments')->find($id);
        if(!$recruitmentStatus) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentStatus, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getRecruitmentStatuses(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:id,name,recruitments_count",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword ?? NULL;

        $rows = $request->rows ?? 5;
        $recruitmentStatuses = RecruitmentStatus::withCount('recruitments');
        
        // filter
        if($keyword) $recruitmentStatuses = $recruitmentStatuses->where("name","LIKE", "%$keyword%");
        
        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->get('sort_type','asc');
        if($sort_by == "id") $recruitmentStatuses = $recruitmentStatuses->orderBy('id',$sort_type);
        if($sort_by == "name") $recruitmentStatuses = $recruitmentStatuses->orderBy('name',$sort_type);
        if($sort_by == "recruitments_count") $recruitmentStatuses = $recruitmentStatuses->orderBy('recruitments_count',$sort_type);

        $recruitmentStatuses = $recruitmentStatuses->paginate($rows);
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentStatuses, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRecruitmentStatusesList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $recruitmentStatuses = RecruitmentStatus::get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentStatuses, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitmentStatus(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "color" => "required",
            "description" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitmentStatus = new RecruitmentStatus();
            $recruitmentStatus->name = $request->name ?? "";
            $recruitmentStatus->color = $request->color ?? "";
            $recruitmentStatus->description = $request->description ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentStatus->created_at = $current_timestamp;
            $recruitmentStatus->updated_at = $current_timestamp;

            $recruitmentStatus->save();

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitmentStatus, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitmentStatus(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "name" => "required",
            "color" => "required",
            "description" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentStatus = RecruitmentStatus::find($id);
            if(!$recruitmentStatus){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }

            $recruitmentStatus->name = $request->name ?? $recruitmentStatus->name;
            $recruitmentStatus->color = $request->color ?? $recruitmentStatus->color;
            $recruitmentStatus->description = $request->description ?? $recruitmentStatus->description;


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentStatus->updated_at = $current_timestamp;

            $recruitmentStatus->save();

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitmentStatus, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRecruitmentStatus(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentStatus = RecruitmentStatus::find($id);
            if(!$recruitmentStatus){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }
            $recruitmentStatus->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $recruitmentStatus, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    //END OF RECRUITMENT STATUS SECTION

    //RECRUITMENT STAGES SECTION
    public function getRecruitmentStage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentStage = RecruitmentStage::withCount('recruitments')->find($id);
        if(!$recruitmentStage) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentStage, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getRecruitmentStages(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:id,name,recruitments_count",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword ?? NULL;

        $rows = $request->rows ?? 5;
        $recruitmentStages = RecruitmentStage::withCount('recruitments');
        
        // filter
        if($keyword) $recruitmentStages = $recruitmentStages->where("name","LIKE", "%$keyword%");
        
        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->get('sort_type','asc');
        if($sort_by == "id") $recruitmentStages = $recruitmentStages->orderBy('id',$sort_type);
        if($sort_by == "name") $recruitmentStages = $recruitmentStages->orderBy('name',$sort_type);
        if($sort_by == "recruitments_count") $recruitmentStages = $recruitmentStages->orderBy('recruitments_count',$sort_type);

        $recruitmentStages = $recruitmentStages->paginate($rows);
        
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentStages, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRecruitmentStagesList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $recruitmentStages = RecruitmentStage::get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentStages, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitmentStage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "description" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitmentStage = new RecruitmentStage();
            $recruitmentStage->name = $request->name ?? "";
            $recruitmentStage->description = $request->description ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentStage->created_at = $current_timestamp;
            $recruitmentStage->updated_at = $current_timestamp;

            $recruitmentStage->save();

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitmentStage, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitmentStage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "name" => "required",
            "description" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentStage = RecruitmentStage::find($id);
            if(!$recruitmentStage){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }

            $recruitmentStage->name = $request->name ?? $recruitmentStage->name;
            $recruitmentStage->description = $request->description ?? $recruitmentStage->description;


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentStage->updated_at = $current_timestamp;

            $recruitmentStage->save();

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitmentStage, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRecruitmentStage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentStage = RecruitmentStage::find($id);
            if(!$recruitmentStage){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }
            $recruitmentStage->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $recruitmentStage, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    //END OF RECRUITMENT STAGES SECTION

    //RECRUITMENT JALUR DAFTAR SECTION
    public function getRecruitmentJalurDaftar(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentJalurDaftar = RecruitmentJalurDaftar::withCount('recruitments')->find($id);
        if(!$recruitmentJalurDaftar) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentJalurDaftar, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getRecruitmentJalurDaftars(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        

        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:id,name,recruitments_count",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword ?? NULL;

        $rows = $request->rows ?? 5;
        $recruitmentJalurDaftars = RecruitmentJalurDaftar::withCount('recruitments');
        
        // filter
        if($keyword) $recruitmentJalurDaftars = $recruitmentJalurDaftars->where("name","LIKE", "%$keyword%");
        
        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->get('sort_type','asc');
        if($sort_by == "id") $recruitmentJalurDaftars = $recruitmentJalurDaftars->orderBy('id',$sort_type);
        if($sort_by == "name") $recruitmentJalurDaftars = $recruitmentJalurDaftars->orderBy('name',$sort_type);
        if($sort_by == "recruitments_count") $recruitmentJalurDaftars = $recruitmentJalurDaftars->orderBy('recruitments_count',$sort_type);

        $recruitmentJalurDaftars = $recruitmentJalurDaftars->paginate($rows);
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentJalurDaftars, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRecruitmentJalurDaftarsList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $recruitmentJalurDaftars = RecruitmentJalurDaftar::get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentJalurDaftars, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitmentJalurDaftar(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitmentJalurDaftar = new RecruitmentJalurDaftar();
            $recruitmentJalurDaftar->name = $request->name ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentJalurDaftar->created_at = $current_timestamp;
            $recruitmentJalurDaftar->updated_at = $current_timestamp;

            $recruitmentJalurDaftar->save();

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitmentJalurDaftar, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitmentJalurDaftar(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "name" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentJalurDaftar = RecruitmentJalurDaftar::find($id);
            if(!$recruitmentJalurDaftar){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }

            $recruitmentJalurDaftar->name = $request->name ?? $recruitmentJalurDaftar->name;


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentJalurDaftar->updated_at = $current_timestamp;

            $recruitmentJalurDaftar->save();

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitmentJalurDaftar, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRecruitmentJalurDaftar(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentJalurDaftar = RecruitmentJalurDaftar::find($id);
            if(!$recruitmentJalurDaftar){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }
            $recruitmentJalurDaftar->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $recruitmentJalurDaftar, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    //END OF RECRUITMENT JALUR DAFTAR SECTION


    //RECRUITMENT EMAIL TEMPLATE SECTION
    public function getRecruitmentEmailTemplate(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentEmailTemplate = RecruitmentEmailTemplate::find($id);
        if(!$recruitmentEmailTemplate) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentEmailTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getRecruitmentEmailTemplates(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:id,name",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword ?? NULL;

        $rows = $request->rows ?? 5;
        $recruitmentEmailTemplates = new RecruitmentEmailTemplate;
        
        // filter
        if($keyword) $recruitmentEmailTemplates = $recruitmentEmailTemplates->where("name","LIKE", "%$keyword%");
        
        // sort
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->get('sort_type','asc');
        if($sort_by == "id") $recruitmentEmailTemplates = $recruitmentEmailTemplates->orderBy('id',$sort_type);
        if($sort_by == "name") $recruitmentEmailTemplates = $recruitmentEmailTemplates->orderBy('name',$sort_type);

        $recruitmentEmailTemplates = $recruitmentEmailTemplates->paginate($rows);
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentEmailTemplates, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRecruitmentEmailTemplatesList(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $recruitmentEmailTemplates = RecruitmentEmailTemplate::get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitmentEmailTemplates, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitmentEmailTemplate(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "subject" => "required",
            "body" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitmentEmailTemplate = new RecruitmentEmailTemplate();
            $recruitmentEmailTemplate->name = $request->name ?? "";
            $recruitmentEmailTemplate->subject = $request->subject ?? "";
            $recruitmentEmailTemplate->body = $request->body ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentEmailTemplate->created_at = $current_timestamp;
            $recruitmentEmailTemplate->updated_at = $current_timestamp;

            $recruitmentEmailTemplate->save();

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitmentEmailTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitmentEmailTemplate(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "name" => "required",
            "subject" => "required",
            "body" => "required",
        ]);


        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentEmailTemplate = RecruitmentEmailTemplate::find($id);
            if(!$recruitmentEmailTemplate){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }

            $recruitmentEmailTemplate->name = $request->name ?? $recruitmentEmailTemplate->name;
            $recruitmentEmailTemplate->subject = $request->subject ?? $recruitmentEmailTemplate->subject;
            $recruitmentEmailTemplate->body = $request->body ?? $recruitmentEmailTemplate->body;


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentEmailTemplate->updated_at = $current_timestamp;

            $recruitmentEmailTemplate->save();

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitmentEmailTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRecruitmentEmailTemplate(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id ?? "";
            
            $recruitmentEmailTemplate = RecruitmentEmailTemplate::find($id);
            if(!$recruitmentEmailTemplate){
                return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            }
            $recruitmentEmailTemplate->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $recruitmentEmailTemplate, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    //END OF RECRUITMENT EMAIL TEMPLATE SECTION

}