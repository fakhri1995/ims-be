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

class ResumeService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function objectModelBuilder($data, $fillable, $modelObject, $addition = NULL, $updateKey = false, $updateWhere = []){
        if(!is_array($data)){
            return false;
        }
        
        if($updateKey){
            $where = array_merge(['id' => $data['id']],$updateWhere);
            $obj =  $modelObject->where($where)->where($updateWhere)->first();
        }
        else {
            $obj = new $modelObject;
        }

        // foreach($data as $key => $value){
        //     if(in_array($key,$fillable)){
        //         $obj->$key = $value;
        //     }
        // }

        foreach($fillable as $key){
            if(isset($data[$key])){
                $obj->$key = $data[$key];
            }
        }

        if($addition){
            foreach($addition as $key => $value){
                $obj->$key = $value;
            }
        }

        return $obj;
    }

    public function objectModelBuilderArray($arrayData, $fillable, $modelObject, $addition = NULL, $updateKey = false, $updateWhere = []){
        $objs = [];
        if(!is_array($arrayData)){
            return false;
        }

        foreach($arrayData as $data){
            $obj = $this->objectModelBuilder($data,$fillable, $modelObject, $addition, $updateKey, $updateWhere);
            $objs[] = $obj;
        }
        
        return $objs;
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
        $resume = resume::with(['educations', 'experiences', 'projects', 'skills', 'trainings', 'certificates', 'achievements'])->find($id);
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
        
        try{ 
            // Resume section
            // $fillableResume = ["name","telp","email","role","city","province"];
            // $resumeRequest = $request->all();
            // $resume = new Resume();
            // foreach($resumeRequest as $key => $value){
            //     if(in_array($key,$fillableResume)){
            //         $resume->$key = $value;
            //     }
            // }
            // $resume->created_at = Date('Y-m-d H:i:s');
            // $resume->updated_at = Date('Y-m-d H:i:s');
            // $resume->created_by = auth()->user()->id;

            $fillableResume = ["name","telp","email","role","city","province"];
            $resumeRequest = $request->all();
            
            $additionResume = [
                'created_at' => Date('Y-m-d H:i:s'),
                'updated_at' => Date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id,
            ];

            $resume = $this->objectModelBuilder($resumeRequest, $fillableResume, new Resume(), $additionResume);
            if(!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];

            // education section
            $fillableEducation = ["university","major","gpa","graduation_year"];
            $educations = $this->objectModelBuilderArray($request->educations, $fillableEducation, new ResumeEducation());

            // experience section
            $fillableExperience = ["role","company","start_date","end_date","description"];
            $experiences = $this->objectModelBuilderArray($request->experiences, $fillableExperience, new ResumeExperience());

            // project section
            $fillableProject = ["name","year"];
            $projects = $this->objectModelBuilderArray($request->projects, $fillableProject, new ResumeProject());
            
            // skill section
            $fillableSkill = ["name"];
            $skills = $this->objectModelBuilderArray($request->skills, $fillableSkill, new ResumeSkill());
            
            // training section
            $fillableTraining = ["name","organizer","year"];
            $trainings = $this->objectModelBuilderArray($request->trainings, $fillableTraining, new ResumeTraining());

            // certificate section
            $fillableCertificate = ["name","organizer","year"];
            $certificates = $this->objectModelBuilderArray($request->certificates, $fillableCertificate, new ResumeCertificate());

            // achievement section
            $fillableAchievement = ["achievement","name","organizer","year"];
            $achievements = $this->objectModelBuilderArray($request->achievements, $fillableAchievement, new ResumeAchievement());

            $insertBatch = DB::transaction(function() use($resume, $educations, $experiences, $projects, $skills, $trainings, $certificates, $achievements){
                try{
                    $educationBatch = $educations ? $resume->educations()->saveMany($educations) : true;
                    if(!$educationBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Education", "status" => 400];
                    $experienceBatch = $experiences ? $resume->experiences()->saveMany($experiences) : true;
                    if(!$experienceBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Experience", "status" => 400];
                    $projectBatch = $projects ? $resume->projects()->saveMany($projects) : true;
                    if(!$projectBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Project", "status" => 400];
                    $skillBatch = $skills ? $resume->skills()->saveMany($skills) : true;
                    if(!$skillBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Skill", "status" => 400];
                    $trainingBatch = $trainings ? $resume->trainings()->saveMany($trainings) : true;
                    if(!$trainingBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Training", "status" => 400];
                    $certificateBatch = $certificates ? $resume->certificates()->saveMany($certificates) : true;
                    if(!$certificateBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Certificate", "status" => 400];
                    $achievementBatch = $achievements ? $resume->achievements()->saveMany($achievements) : true;
                    if(!$achievementBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Achievement", "status" => 400];
                }catch(Exception $e){
                    return ["success" => false, "message" => $e, "status" => 400];;
                }
                return true;
            });

            if($insertBatch !== true){
                return $insertBatch;
            }
             
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $resume->id, "status" => 201];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateResume(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\Resume,id",
            "name" => "filled",
            "telp" => "filled|numeric",
            "email" => "filled|email",
            "role" => "filled",
            "city" => "filled",
            "province" => "filled",

            "education" => "filled|array",
            "education.id" => "required_with:education|exists:App\ResumeEducation,id",
            "education.university" => "required_with:education",
            "education.university" => "required_with:education",
            "education.major" => "required_with:education",
            "education.gpa" => "required_with:education|numeric|between:0.00,4.00",
            "education.graduation_year" => "required_with:education|date",
            
            "experience" => "filled|array",
            "experience.id" => "required_with:experienc|exists:App\ResumeExperience,id",
            "experience.role" => "required_with:experience",
            "experience.company" => "required_with:experience",
            "experience.start_date" => "required_with:experience|date",
            "experience.end_date" => "required_with:experience|date",
            "experience.description" => "required_with:experience",
            
            "project" => "filled|array",
            "project.id" => "required_with:project|exists:App\ResumeProject,id",
            "project.name" => "required_with:project",
            "project.year" => "required_with:project|date",
            
            "skill" => "filled|array",
            "skill.id" => "required_with:skill|exists:App\ResumeSkill,id",
            "skill.name" => "required_with:skill",
            
            "training" => "filled|array",
            "training.id" => "required_with:training|exists:App\ResumeTraining,id",
            "training.name" => "required_with:training",
            "training.organizer" => "required_with:training",
            "training.year" => "required_with:training|date",
            
            "certificate" => "filled|array",
            "certificate.id" => "required_with:certificate|exists:App\ResumeCertificate,id",
            "certificate.name" => "required_with:certificate",
            "certificate.organizer" => "required_with:certificate",
            "certificate.year" => "required_with:certificate|date",
            
            "achievement" => "filled|array",
            "achievement.id" => "required_with:achievement|exists:App\ResumeAchievement,id",
            "achievement.achievement" => "required_with:achievement",
            "achievement.name" => "required_with:achievement",
            "achievement.organizer" => "required_with:achievement",
            "achievement.year" => "required_with:achievement|date"
        ]);
        

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
        // $fillableResume = ["name","telp","email","role","city","province","portofolio_url"];

        // $id = $request->get("id");
        // $resume = resume::find($id);
        
        // if(!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        // foreach($request->all() as $key => $value){
        //     if(in_array($key,$fillableResume)){
        //         $resume->$key = $request->$key;
        //     }
        // }

        // $resume->updated_at = Date('Y-m-d H:i:s');

        
        
        $resume_id = $request->get("id");
        $resume = Resume::find($resume_id);
        if(!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        $fillableResume = ["name","telp","email","role","city","province"];
        $resumeRequest = $request->all();
        
        $additionResume = [
            'updated_at' => Date('Y-m-d H:i:s'),
        ];

        $resume = $this->objectModelBuilder($resumeRequest, $fillableResume, new Resume(), $additionResume, true);

        // education section
        $fillableEducation = ["university","major","gpa","graduation_year"];
        $education = $this->objectModelBuilder($request->education, $fillableEducation, new ResumeEducation(), NULL, true, ['cv_id' => $resume_id]);
        
        // experience section
        $fillableExperience = ["role","company","start_date","end_date","description"];
        $experience = $this->objectModelBuilder($request->experience, $fillableExperience, new ResumeExperience(), NULL, true, ['cv_id' => $resume_id]);

        // project section
        $fillableProject = ["name","year"];
        $project = $this->objectModelBuilder($request->project, $fillableProject, new ResumeProject(), NULL, true, ['cv_id' => $resume_id]);
        
        // skill section
        $fillableSkill = ["name"];
        $skill = $this->objectModelBuilder($request->skill, $fillableSkill, new ResumeSkill(), NULL, true, ['cv_id' => $resume_id]);
        
        // training section
        $fillableTraining = ["name","organizer","year"];
        $training = $this->objectModelBuilder($request->training, $fillableTraining, new ResumeTraining(), NULL, true, ['cv_id' => $resume_id]);

        // certificate section
        $fillableCertificate = ["name","organizer","year"];
        $certificate = $this->objectModelBuilder($request->certificate, $fillableCertificate, new ResumeCertificate(), NULL, true, ['cv_id' => $resume_id]);

        // achievement section
        $fillableAchievement = ["achievement","name","organizer","year"];
        $achievement = $this->objectModelBuilder($request->achievement, $fillableAchievement, new ResumeAchievement(), NULL, true, ['cv_id' => $resume_id]);

        $insertBatch = DB::transaction(function() use($resume, $education, $experience, $project, $skill, $training, $certificate, $achievement){
            try{
                if(!$resume->save()) return ["success" => false, "message" => "Gagal Mengubah Resume", "status" => 400];
                $educationBatch = $education ? $education->save() : true;
                if(!$educationBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Education", "status" => 400];
                $experienceBatch = $experience ? $experience->save() : true;
                if(!$experienceBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Experience", "status" => 400];
                $projectBatch = $project ? $project->save() : true;
                if(!$projectBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Project", "status" => 400];
                $skillBatch = $skill ? $skill->save() : true;
                if(!$skillBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Skill", "status" => 400];
                $trainingBatch = $training ? $training->save() : true;
                if(!$trainingBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Training", "status" => 400];
                $certificateBatch = $certificate ? $certificate->save() : true;
                if(!$certificateBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Certificate", "status" => 400];
                $achievementBatch = $achievement ? $achievement->save() : true;
                if(!$achievementBatch) return ["success" => false, "message" => "Gagal Menambah Resume - Achievement", "status" => 400];
            
            }catch(Exception $e){
                return ["success" => false, "message" => $e, "status" => 400];;
            }
            return true;
        });

        if($insertBatch != true){
            return $insertBatch;
        }

        try{
            return ["success" => true, "message" => "Career Berhasil Diubah", "status" => 200];
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