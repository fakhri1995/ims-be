<?php 

namespace App\Services;

use App\Mail\RecruitmentMail;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

            if($recruitment->save()){
                $logService = new LogService;
                $logProperties = [
                    "log_type" => "created_recruitment",
                    "data" => $recruitment
                ];
                $logNotes = $request->notes ?? NULL;
                $logService->addLogRecruitment($recruitment->id, auth()->user()->id, "Created", $logProperties, $logNotes);
            }

            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitments(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "*.name" => "required",
            "*.email" => "required|email",
            "*.university" => "required",
            "*.recruitment_role_id" => "required|numeric",
            "*.recruitment_jalur_daftar_id" => "required|numeric",
            "*.recruitment_stage_id" => "required|numeric",
            "*.recruitment_status_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $requestCollection = collect($request);
            $recruitment_role_ids = $requestCollection->pluck('recruitment_role_id')->toArray();
            $recruitment_role_id_array = RecruitmentRole::whereIn("id",$recruitment_role_ids)->pluck("id")->toArray();
            $recruitment_role_id_diff = array_diff($recruitment_role_ids,$recruitment_role_id_array);
            if(count($recruitment_role_id_diff)) return ["success" => false, "message" => "Recruitment Role Id : [".implode(", ",$recruitment_role_id_diff)."] tidak ditemukan", "status" => 400];
            
            $recruitment_jalur_daftar_ids = $requestCollection->pluck('recruitment_jalur_daftar_id')->toArray();
            $recruitment_jalur_daftar_id_array = RecruitmentJalurDaftar::whereIn("id",$recruitment_jalur_daftar_ids)->pluck("id")->toArray();
            $recruitment_jalur_daftar_id_diff = array_diff($recruitment_jalur_daftar_ids,$recruitment_jalur_daftar_id_array);
            if(count($recruitment_jalur_daftar_id_diff)) return ["success" => false, "message" => "Recruitment Jalur Daftar Id : [".implode(", ",$recruitment_jalur_daftar_id_diff)."] tidak ditemukan", "status" => 400];

            $recruitment_status_ids = $requestCollection->pluck('recruitment_status_id')->toArray();
            $recruitment_status_id_array = RecruitmentStatus::whereIn("id",$recruitment_status_ids)->pluck("id")->toArray();
            $recruitment_status_id_diff = array_diff($recruitment_status_ids,$recruitment_status_id_array);
            if(count($recruitment_status_id_diff)) return ["success" => false, "message" => "Recruitment Status Id : [".implode(", ",$recruitment_status_id_diff)."] tidak ditemukan", "status" => 400];

            $recruitment_stage_ids = $requestCollection->pluck('recruitment_stage_id')->toArray();  
            $recruitment_stage_id_array = RecruitmentStage::whereIn("id",$recruitment_stage_ids)->pluck("id")->toArray();
            $recruitment_stage_id_diff = array_diff($recruitment_stage_ids,$recruitment_stage_id_array);
            if(count($recruitment_stage_id_diff)) return ["success" => false, "message" => "Recruitment Stage Id : [".implode(", ",$recruitment_stage_id_diff)."] tidak ditemukan", "status" => 400];
            

            $current_timestamp = date('Y-m-d H:i:s');
            $recruitments = [];
            $logs = [];
            $requestAll = $request->all();
            foreach($requestAll as $req){
                $req = (object)$req;
                $recruitment = new Recruitment();
                $recruitment->name = $req->name;
                $recruitment->email = $req->email;
                $recruitment->university = $req->university;
                $recruitment->recruitment_role_id = $req->recruitment_role_id;
                $recruitment->recruitment_jalur_daftar_id = $req->recruitment_jalur_daftar_id;
                $recruitment->recruitment_stage_id = $req->recruitment_stage_id;
                $recruitment->recruitment_status_id = $req->recruitment_status_id;

                $recruitment->created_at = $current_timestamp;
                $recruitment->updated_at = $current_timestamp;
                $recruitment->created_by = auth()->user()->id;

                if($recruitment->save()){
                    $properties = [
                        "log_type" => "created_recruitment",
                        "data" => $recruitment
                    ];
                    $logs[] = [
                        "subject_id" => $recruitment->id,
                        "causer_id" => auth()->user()->id,
                        "log_name" => "Created",
                        "properties" => json_encode($properties),
                        "notes" => $req->notes ?? NULL,
                        "created_at" => date("Y-m-d H:i:s"),
                    ];
                }
                $recruitments[] = $recruitment;
            }

            $logService = new LogService;
            $logService->addLogRecruitments($logs);

        try{
            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $recruitments, "status" => 200];
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
            "id" => "numeric|required"
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

    public function deleteRecruitments(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "ids" => "array|required",
            "ids.*" => "numeric"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $ids = $request->ids ?? [];
            
            $recruitment = Recruitment::whereIn("id",$ids);
            $recruitment_ids = $recruitment->pluck("id")->toArray();
            $recruitment_id_diff = array_diff($ids,$recruitment_ids);
            if(count($recruitment_id_diff)) return ["success" => false, "message" => "Recruitment Id : [".implode(", ",$recruitment_id_diff)."] tidak ditemukan", "status" => 400];

            $data = $recruitment->get();
            $recruitment->delete();

            try{
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $data, "status" => 200];
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

    public function updateRecruitment_status(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
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
            $recruitment_status_id = $request->recruitment_status_id ?? NULL;

            if(!RecruitmentStatus::find($recruitment_status_id)) return ["success" => false, "message" => "Recruitment Status yang dipilih tidak tersedia", "status" => 400];
            if($recruitment->recruitment_status_id == $request->recruitment_status_id) return ["success" => true, "message" => "Tidak terjadi perubahan pada status karena id sama", "data" => $recruitment, "status" => 200];
            
            $recruitment_status_id_old = $recruitment->recruitment_status_id;
            $recruitment->recruitment_status_id = $request->recruitment_status_id ?? "";

            
            $current_timestamp = date('Y-m-d H:i:s');
            $recruitment->updated_at = $current_timestamp;
            if($recruitment->save()){
                $logService = new LogService;
                $logProperties = [
                    "log_type" => "recruitment_status",
                    "old_recruitment_status_id" => $recruitment_status_id_old,
                    "new_recruitment_status_id" => $recruitment_status_id
                ];
                $logNotes = $request->notes ?? NULL;
                $logService->addLogRecruitment($id, auth()->user()->id, "Updated", $logProperties, $logNotes);
            }
            
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitment_stage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "recruitment_stage_id" => "required|numeric",
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

            $recruitment_stage_id = $request->recruitment_stage_id ?? NULL;

            if(!RecruitmentStage::find($recruitment_stage_id)) return ["success" => false, "message" => "Recruitment Stage yang dipilih tidak tersedia", "status" => 400];
            if($recruitment->recruitment_stage_id == $request->recruitment_stage_id) return ["success" => true, "message" => "Tidak terjadi perubahan pada stage karena id sama", "data" => $recruitment, "status" => 200];
            
            $recruitment_stage_id_old = $recruitment->recruitment_stage_id;
            $recruitment->recruitment_stage_id = $request->recruitment_stage_id ?? "";


            $current_timestamp = date('Y-m-d H:i:s');
            $recruitment->updated_at = $current_timestamp;
            if($recruitment->save()){
                $logService = new LogService;
                $logProperties = [
                    "log_type" => "recruitment_stage",
                    "old_recruitment_stage_id" => $recruitment_stage_id_old,
                    "new_recruitment_stage_id" => $recruitment_stage_id
                ];
                $logNotes = $request->notes ?? NULL;
                $logService->addLogRecruitment($id, auth()->user()->id, "Updated", $logProperties, $logNotes);
            }

            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRecruitmentLogNotes(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "notes" => "required",
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

            
            $logNotes = $request->notes;
            $logService = new LogService;
            $logService->addLogRecruitment($id, auth()->user()->id, "Notes", NULL , $logNotes);
            
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitment, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitments_status(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|array",
            "id.*" => "required|numeric",
            "recruitment_status_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

       
            $ids = $request->id;
            $recruitments = Recruitment::whereIn('id',$ids)->get();
            $recruitmentsArray = $recruitments->pluck('id')->toArray();
            $recruitmentsArrayDiff = array_diff($ids,$recruitmentsArray);
            if(count($recruitmentsArrayDiff) > 0) return ["success" => false, "message" => "Id : [".implode(", ",$recruitmentsArrayDiff)."] tidak ditemukan", "status" => 400];


        try{  
            $recruitment_status_id = $request->recruitment_status_id;
            if(!RecruitmentStatus::find($recruitment_status_id)) return ["success" => false, "message" => "Recruitment Status yang dipilih tidak tersedia", "status" => 400];
            

            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentsUpdateStage = Recruitment::whereIn('id',$ids)->where('recruitment_status_id',"!=",$recruitment_status_id)->update([
                "recruitment_status_id" => $recruitment_status_id,
                "updated_at" => $current_timestamp
            ]);

            if($recruitmentsUpdateStage){
                $batch = DB::transaction(function() use($request,$recruitments,$recruitment_status_id,$current_timestamp){
                    try{
                        foreach($recruitments as $recruitment){
                            $recruitment_status_id_old = $recruitment->recruitment_status_id;
                            $logService = new LogService;
                            $logProperties = [
                                "log_type" => "recruitment_status",
                                "old_recruitment_status_id" => $recruitment_status_id_old,
                                "new_recruitment_status_id" => $recruitment_status_id
                            ];
                            $logNotes = $request->notes ?? NULL;
                            if($recruitment_status_id_old != $recruitment_status_id){
                                $logService->addLogRecruitment($recruitment->id, auth()->user()->id, "Updated", $logProperties, $logNotes);
                            
                                $recruitment->recruitment_status_id = $recruitment_status_id;
                                $recruitment->updated_at = $current_timestamp;
                            }
                        }
                        return true;
                    }catch(Exception $e){
                        return ["error" => $e];
                    }
                }); 
                
                if(isset($batch['error'])){
                    return ["success" => false, "message" => $batch['error'], "status" => 400];
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitments, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRecruitments_stage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|array",
            "id.*" => "required|numeric",
            "recruitment_stage_id" => "required|numeric",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

       
            $ids = $request->id;
            $recruitments = Recruitment::whereIn('id',$ids)->get();
            $recruitmentsArray = $recruitments->pluck('id')->toArray();
            $recruitmentsArrayDiff = array_diff($ids,$recruitmentsArray);
            if(count($recruitmentsArrayDiff) > 0) return ["success" => false, "message" => "Id : [".implode(", ",$recruitmentsArrayDiff)."] tidak ditemukan", "status" => 400];


        try{  
            $recruitment_stage_id = $request->recruitment_stage_id;
            if(!RecruitmentStage::find($recruitment_stage_id)) return ["success" => false, "message" => "Recruitment Stage yang dipilih tidak tersedia", "status" => 400];
            

            $current_timestamp = date('Y-m-d H:i:s');
            $recruitmentsUpdateStage = Recruitment::whereIn('id',$ids)->where('recruitment_stage_id',"!=",$recruitment_stage_id)->update([
                "recruitment_stage_id" => $recruitment_stage_id,
                "updated_at" => $current_timestamp
            ]);
            
            if($recruitmentsUpdateStage){
                $batch = DB::transaction(function() use($request,$recruitments,$recruitment_stage_id,$current_timestamp){
                    try{
                        foreach($recruitments as $recruitment){
                            $recruitment_stage_id_old = $recruitment->recruitment_stage_id;
                            $logService = new LogService;
                            $logProperties = [
                                "log_type" => "recruitment_stage",
                                "old_recruitment_stage_id" => $recruitment_stage_id_old,
                                "new_recruitment_stage_id" => $recruitment_stage_id
                            ];
                            $logNotes = $request->notes ?? NULL;
                            if($recruitment_stage_id_old != $recruitment_stage_id){
                                $logService->addLogRecruitment($recruitment->id, auth()->user()->id, "Updated", $logProperties, $logNotes);
                            
                                $recruitment->recruitment_stage_id = $recruitment_stage_id;
                                $recruitment->updated_at = $current_timestamp;
                            }
                        }
                        return true;
                    }catch(Exception $e){
                        return ["error" => $e];
                    }
                }); 
                
                if(isset($batch['error'])){
                    return ["success" => false, "message" => $batch['error'], "status" => 400];
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $recruitments, "status" => 200];
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
        if($sort_by == "role_type") $recruitmentRoles = $recruitmentRoles->orderBy(RecruitmentRoleType::select("name")
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
            $recruitmentRole = RecruitmentRole::withCount('recruitments')->find($id);
            if(!$recruitmentRole) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            if($recruitmentRole->recruitments_count > 0) return ["success" => false, "message" => "Data masih digunakan pada recruitment", "status" => 400]; 
            
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
            
            $recruitmentStatus = RecruitmentStatus::withCount('recruitments')->find($id);
            if(!$recruitmentStatus) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            if($recruitmentStatus->recruitments_count > 0) return ["success" => false, "message" => "Data masih digunakan pada recruitment", "status" => 400]; 
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
            
            $recruitmentStage = RecruitmentStage::withCount('recruitments')->find($id);
            if(!$recruitmentStage) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            if($recruitmentStage->recruitments_count > 0) return ["success" => false, "message" => "Data masih digunakan pada recruitment", "status" => 400]; 
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
            
            $recruitmentJalurDaftar = RecruitmentJalurDaftar::withCount('recruitments')->find($id);
            if(!$recruitmentJalurDaftar) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400]; 
            if($recruitmentJalurDaftar->recruitments_count > 0) return ["success" => false, "message" => "Data masih digunakan pada recruitment", "status" => 400]; 
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

    public function sendRecruitmentEmail(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "subject" => "required",
            "body" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }


        
        $email = $request->email;
        $data = (object)[
            "subject" => $request->subject,
            "body" => $request->body,
            // "attachment" => base64_encode($request->file('attachment')) ?? NULL,
            "attachment" => $request->file('attachment') ?? NULL,
        ];
        
            $sendMail = Mail::to($email)->send(new RecruitmentMail($data));
        try{
            return ["success" => true, "message" => $sendMail, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    //END OF RECRUITMENT EMAIL TEMPLATE SECTION

}