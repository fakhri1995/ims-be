<?php 

namespace App\Services;

use App\Recruitment;
use App\RecruitmentRole;
use App\Recruitments;
use App\RecruitmentStage;
use App\RecruitmentStatus;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitment = Recruitment::find($id);
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
        
        $recruitments = Recruitment::paginate(5);
        if(!$recruitments) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
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
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitment = new Recruitment();
            $recruitment->name = $request->name ?? "";
            $recruitment->email = $request->email ?? "";
            $recruitment->university = $request->university ?? "";
            $recruitment->role = $request->role ?? "";
            $recruitment->jalur_daftar = $request->jalur_daftar ?? "";
            $recruitment->stage = $request->stage ?? "";
            $recruitment->status = $request->status ?? "";


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

            $recruitment->name = $request->name ?? $recruitment->name;
            $recruitment->email = $request->email ?? $recruitment->email;
            $recruitment->university = $request->university ?? $recruitment->university;
            $recruitment->role = $request->role ?? $recruitment->role;
            $recruitment->jalur_daftar = $request->jalur_daftar ?? $recruitment->jalur_daftar;
            $recruitment->stage = $request->stage ?? $recruitment->stage;
            $recruitment->status = $request->status ?? $recruitment->status;


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

    //END OF RECRUITMENT SECTION

    //RECRUITMENT ROLES SECTION
    public function getRecruitmentRole(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentRole = RecruitmentRole::find($id);
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
        
        $recruitmentRoles = RecruitmentRole::paginate(5);
        
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
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitmentRole = new RecruitmentRole();
            $recruitmentRole->name = $request->name ?? "";
            $recruitmentRole->alias = $request->alias ?? "";
            $recruitmentRole->role_type_id = $request->role_type_id ?? "";


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

            $recruitmentRole->name = $request->name ?? $recruitmentRole->name;
            $recruitmentRole->alias = $request->alias ?? $recruitmentRole->alias;
            $recruitmentRole->role_type_id = $request->role_type_id ?? $recruitmentRole->role_type_id;


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
    //END OF RECRUITMENT ROLES SECTION

    //RECRUITMENT STATUS SECTION
    public function getRecruitmentStatus(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentStatus = RecruitmentStatus::find($id);
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
        
        $recruitmentStatuses = RecruitmentStatus::paginate(5);
        
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
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
        
            $recruitmentStatus = new RecruitmentStatus();
            $recruitmentStatus->name = $request->name ?? "";
            $recruitmentStatus->color = $request->color ?? "";


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
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $recruitmentStage = RecruitmentStage::find($id);
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
        
        $recruitmentStages = RecruitmentStage::paginate(5);
        
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

}