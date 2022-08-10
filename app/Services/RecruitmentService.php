<?php 

namespace App\Services;

use App\Resume;
use App\ResumeAchievement;
use App\ResumeCertificate;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeProject;
use App\ResumeSkill;
use App\ResumeTraining;
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

    public function eloquentBuilderArray($arrayData, $fillable, $modelObject, $addition = NULL, $updateKey  = NULL){
        $datas = [];
        foreach($arrayData as $ed){
            $data = $updateKey && isset($ed[$updateKey]) ? $modelObject->find($ed[$updateKey]) : new $modelObject;
            foreach($ed as $key => $value){
                if(in_array($key,$fillable)){
                    $data->$key = $value;
                }
            }

            foreach($addition as $key => $value){
                $data->$key = $value;
            }
            $datas[] = $data;
        }

        return $datas;
    }

    public function getResumes(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $resume = Resume::get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resume, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getResume(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "exists:App\Resume,id|numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->get("id");
        $resume = resume::find($id);
        if(!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resume, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addResume(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "telp" => "required|numeric",
            "email" => "required|email",
            "role" => "required",
            "city" => "required",
            "province" => "required",

            "educations" => "filled|array",
            "educations.*.university" => "required_with:educations",
            "educations.*.major" => "required_with:educations",
            "educations.*.gpa" => "required_with:educations|numeric|between:0.00,4.00",
            "educations.*.graduation_year" => "required_with:educations|date",
            
            "experiences" => "filled|array",
            "experiences.*.role" => "required_with:experiences",
            "experiences.*.company" => "required_with:experiences",
            "experiences.*.start_date" => "required_with:experiences|date",
            "experiences.*.end_date" => "required_with:experiences|date",
            "experiences.*.description" => "required_with:experiences",
            
            "projects" => "filled|array",
            "projects.*.name" => "required_with:projects",
            "projects.*.year" => "required_with:projects|date",
            
            "skills" => "filled|array",
            "skills.*.name" => "required_with:skills",
            
            "trainings" => "filled|array",
            "trainings.*.name" => "required_with:trainings",
            "trainings.*.organizer" => "required_with:trainings",
            "trainings.*.year" => "required_with:trainings|date",
            
            "certificates" => "filled|array",
            "certificates.*.name" => "required_with:certificates",
            "certificates.*.organizer" => "required_with:certificates",
            "certificates.*.year" => "required_with:certificates|date",
            
            "achievements" => "filled|array",
            "achievements.*.achievement" => "required_with:achievements",
            "achievements.*.name" => "required_with:achievements",
            "achievements.*.organizer" => "required_with:achievements",
            "achievements.*.year" => "required_with:achievements|date"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
            // Resume section
            $fillableResume = ["name","telp","email","role","city","province"];
            $resumeRequest = $request->all();
            $resume = new Resume();
            foreach($resumeRequest as $key => $value){
                if(in_array($key,$fillableResume)){
                    $resume->$key = $value;
                }
            }
            $resume->created_at = Date('Y-m-d H:i:s');
            $resume->updated_at = Date('Y-m-d H:i:s');
            $resume->created_by = auth()->user()->id;

            if(!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];
            $additionResumeChild = ["cv_id" => $resume->id];

            // education section
            $fillableEducation = ["university","major","gpa","graduation_year"];
            $educations = $this->eloquentBuilderArray($request->educations, $fillableEducation, new ResumeEducation(), $additionResumeChild);
            $educationBatch = $resume->educations()->saveMany($educations);
            if(!$educationBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Education", "status" => 400];
            

            // experience section
            $fillableExperience = ["role","company","start_date","end_date","description"];
            $experiences = $this->eloquentBuilderArray($request->experiences, $fillableExperience, new ResumeExperience(), $additionResumeChild);
            $experienceBatch = $resume->experiences()->saveMany($experiences);
            if(!$experienceBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Experience", "status" => 400];

            // project section
            $fillableProject = ["name","year"];
            $projects = $this->eloquentBuilderArray($request->projects, $fillableProject, new ResumeProject(), $additionResumeChild);
            $projectBatch = $resume->projects()->saveMany($projects);
            if(!$projectBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Project", "status" => 400];

            // skill section
            $fillableSkill = ["name"];
            $skills = $this->eloquentBuilderArray($request->skills, $fillableSkill, new ResumeSkill(), $additionResumeChild);
            $skillBatch = $resume->skills()->saveMany($skills);
            if(!$skillBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Skill", "status" => 400];

            // training section
            $fillableTraining = ["name","organizer","year"];
            $trainings = $this->eloquentBuilderArray($request->trainings, $fillableTraining, new ResumeTraining(), $additionResumeChild);
            $trainingBatch = $resume->trainings()->saveMany($trainings);
            if(!$trainingBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Training", "status" => 400];

            // certificate section
            $fillableCertificate = ["name","organizer","year"];
            $certificates = $this->eloquentBuilderArray($request->certificates, $fillableCertificate, new ResumeCertificate(), $additionResumeChild);
            $certificateBatch = $resume->certificates()->saveMany($certificates);
            if(!$certificateBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Certificate", "status" => 400];
            
            // achievement section
            $fillableAchievement = ["achievement","name","organizer","year"];
            $achievements = $this->eloquentBuilderArray($request->achievements, $fillableAchievement, new ResumeAchievement(), $additionResumeChild);
            $achievementBatch = $resume->achievements()->saveMany($achievements);
            if(!$achievementBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Achievement", "status" => 400];
            
        
            try{
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $resume->id, "status" => 201];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateResume(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "exists:App\Resume,id|numeric|required",
            "name" => "required",
            "telp" => "required|numeric",
            "email" => "required|email",
            "role" => "required",
            "city" => "required",
            "province" => "required",
            "portofolio_url" => "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
        $fillableResume = ["name","telp","email","role","city","province","portofolio_url"];

        $id = $request->get("id");
        $resume = resume::find($id);
        if(!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        foreach($request->all() as $key => $value){
            if(in_array($key,$fillableResume)){
                $resume->$key = $request->$key;
            }
        }

        $resume->updated_at = Date('Y-m-d H:i:s');

        try{
            $resume->save();
            return ["success" => true, "message" => "Career Berhasil Diubah", "id" => $resume->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteResume(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "exists:App\Resume,id|numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->get("id");
        $resume = resume::find($id);
        if(!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            $resume->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $resume, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }
}