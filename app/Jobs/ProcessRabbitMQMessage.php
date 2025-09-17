<?php

namespace App\Jobs;

use App\Recruitment;
use App\RecruitmentRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Resume;
use App\ResumeAchievement;
use App\ResumeCertificate;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeLanguage;
use App\ResumeProject;
use App\ResumeSkill;
use App\ResumeSummary;
use App\ResumeTool;
use App\ResumeTraining;
use Dom\Attr;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessRabbitMQMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function fire($job, $maxRetries = 3)
    {
        $attempt = 0;
        while($attempt < $maxRetries){
                try{
                        $data = (object) json_decode($job->getRawBody(), true)["data"]["data"];
                        Log::info('handling job', (array) $data);
                        if(!$data->user){
                                throw new Exception("User data not found");
                        }
                        $email = $data->user["email"];
                        $normalizedEmail = is_array($email) ? $email[0] : $email;
                        $is_duplicate = Resume::where('email', $normalizedEmail)->first();
                        if(empty($email)){
                                $is_duplicate = Resume::where('telp', $data->user["phone"])->first();
                                if(empty($data->user["phone"])){
                                        $is_duplicate = Resume::where('name', $data->user["name"])->first();
                                }
                        }
                        if($is_duplicate){
                                Log::info('duplicate detected');
                                throw new Exception("duplicate detected");
                                return true;
                        }

                        $resume = new Resume;
                        $resume->name = $data->user["name"] ?? "Name";
                        $resume->telp = $data->user["phone"] ?? "-";
                        $resume->email = $normalizedEmail ?? "-";
                        $resume->city = $data->user["location"] ?? "-";
                        $resume->province = $data->user["location"] ?? "-";
                        $resume->location = $data->user["location"] ?? "-";
                        $resume->created_at = Date('Y-m-d H:i:s');
                        $resume->updated_at = Date('Y-m-d H:i:s');
                        $resume->created_by = 1;
                        
                        $resume->linkedin = $data->user["linkedin"] ?? "-";
                        $resume->summary = $data->user["summary"] ?? "-";
        
                        if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];
                        $resume->owner_id = $resume->id;
                        $resume->save();

                        $cv_url = $data->metadata["s3_filepath"];

                        $lampiran[] = [
                                "judul_lampiran" => "CV",
                                "isi_lampiran" => $cv_url
                        ];

                        $file_path = str_replace("\\","/", $data->metadata["file_path"]);
                        $dir = dirname($file_path);
                        $role_alias = basename($dir);
                        $recruitment_role = RecruitmentRole::where("alias", $role_alias)->first();
                        $role_id = !$recruitment_role ? 0 : $recruitment_role->id;
        
                        $recruitment = new Recruitment;
                        $recruitment->name = $resume->name;
                        $recruitment->email = $resume->email;
                        $recruitment->recruitment_role_id = $role_id;
                        $recruitment->recruitment_jalur_daftar_id = 1;
                        $recruitment->recruitment_stage_id = 8;
                        $recruitment->recruitment_status_id = 0;
                        $recruitment->cv_processing_status = 1;
                        $recruitment->cv_processing_batch = $resume->id;
                        $recruitment->lampiran = $lampiran;
                        $recruitment->created_at = date("Y-m-d H:i:s");
                        $recruitment->updated_at = date("Y-m-d H:i:s");
                        $recruitment->created_by = 1;
                        $recruitment->owner_id = $resume->id;
                        
                        if (!$recruitment->save()) return ["success" => false, "message" => "Gagal Menambah Recruitment", "status" => 400];
        
                        $count = 1;
                        foreach($data->projects as $project){
                                DB::beginTransaction();
                                $requestProject = (object)$project;
                                
                                $project = new ResumeProject();
                                $project->name = $requestProject->name;
                                $project->year = !$requestProject->end_date ? NULL : $requestProject->end_date;
                                $project->description = $requestProject->description ?? "";
                                $project->display_order = $count;
        
                                $resume->projects()->save($project);
                                DB::commit();
                                $count += 1;
                        }
                        
                        $count = 1;
                        foreach($data->experience as $experience){
                                DB::beginTransaction();
                                $requestExperience = (object)$experience;
        
                                $experience = new ResumeExperience();
                                $responsibilities = $requestExperience->responsibility;
                                $normalized_responsibilities = is_array($responsibilities) ? implode(',', $responsibilities) : $responsibilities;
                                $experience->role = $requestExperience->position;
                                $experience->company = $requestExperience->company;
                                $experience->start_date = $requestExperience->start_date;
                                $experience->end_date = $requestExperience->end_date;
                                $experience->description = $normalized_responsibilities;
                                $experience->achievements = implode(', ', $requestExperience->achievements);
                                $experience->technologies = implode(', ', $requestExperience->technologies);
                                $experience->location = $requestExperience->location;
                                $experience->industry = $requestExperience->industry;
                                $experience->display_order = $count;
        
                                $resume->experiences()->save($experience);
                                DB::commit();
                                $count += 1;
                        }
        
                        $count = 1;
                        foreach($data->education as $education){
                                DB::beginTransaction();
                                $requestEducation = (object)$education;
        
                                $after_id = $requestEducation->after_id ?? NULL;
                                if ($after_id != NULL) {
                                        $educationAfter = ResumeEducation::find($after_id);
                                        if (!$educationAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                                }
        
                                $education = new ResumeEducation();
                                $education->university = $requestEducation->school_name;
                                $education->major = $requestEducation->field;
                                $education->gpa = !$requestEducation->gpa ? NULL : $requestEducation->gpa;
                                $education->degree = !$requestEducation->degree ? NULL : $requestEducation->degree;
                                $education->start_date = $requestEducation->start_date ? date('Y-m-01', strtotime($requestEducation->start_date)) : null;
                                $education->end_date = $requestEducation->end_date ? date('Y-m-t', strtotime($requestEducation->end_date . '-01')) : null;
                                $education->location = $requestEducation->location;
                                $education->honors = implode(', ', $requestEducation->honors);
                                $education->relevant_coursework = implode(', ', $requestEducation->relevant_coursework);
        
                                $educations = new ResumeEducation();
                                if ($after_id == NULL) {
                                        $educations->increment("display_order");
                                        $education->display_order = 1;
                                } else {
                                        $educations->where("display_order", ">", $educationAfter->display_order)->increment("display_order");
                                        $education->display_order = $educationAfter->display_order + 1;
                                }
        
                                $resume->educations()->save($education);
                                DB::commit();
                                $count += 1;
                        }
        
                        $count = 1;
                        foreach($data->skills as $skill){
                                $requestSkill = (object)$skill;
                                $skill = new ResumeSkill();
                                $skill->name = ucfirst($requestSkill->skill_name);
                                $resume->skills()->save($skill);
                                $count += 1;
                        }
        
                        foreach($data->tools as $tool){
                                $requestTool = (object)$tool;
                                $tool = new ResumeTool();
                                $tool->name = ucfirst($requestTool->tool_name);
                                $tool->category = $requestTool->category;
                                $tool->proficiency = $requestTool->proficiency;
                                $tool->details = $requestTool->details;
                                $tool->certifications = implode(', ', $requestTool->certifications);
        
                                $resume->tools()->save($tool);
                        }
        
                        $count = 1;
                        foreach($data->achievements as $achievement){
                                DB::beginTransaction();
                                $requestAchievement = (object)$achievement;

                                $achievement = new ResumeAchievement();
                                $achievement->name = $requestAchievement->description;
                                $achievement->organizer = $requestAchievement->organization ?? "";
                                $achievement->year = !$requestAchievement->date ? NULL : $requestAchievement->date;
                                $achievement->display_order = $count;
        
                                $resume->achievements()->save($achievement);
                                DB::commit();
                                $count += 1;
                        }
        
                        $count = 1;
                        foreach($data->certifications as $certificate){
                                DB::beginTransaction();
                                $requestCertificates = (object)$certificate;
        
                                $certificate = new ResumeCertificate();
                                $certificate->name = $requestCertificates->name;
                                $certificate->organizer = $requestCertificates->issuer ?? "";
                                if($requestCertificates->date_earned == "0000-00-00"){
                                        $certificate->year = "2000-01-01";
                                } else{
                                        $certificate->year = !$requestCertificates->date_earned ? "2000-01-01" : $requestCertificates->date_earned;
                                }
                                $certificate->display_order = $count;
        
                                $resume->certificates()->save($certificate);
                                DB::commit();
                                $count += 1;
                        }
        
                        $count = 1;
                        foreach($data->languages as $language){
                                DB::beginTransaction();
                                $requestLanguage = (object)$language;
        
                                $language = new ResumeLanguage();
                                $language->language = $requestLanguage->language;
                                $language->proficiency = $requestLanguage->proficiency;
                                $language->certifications = implode(', ', $requestLanguage->certifications);
                                $language->display_order = $count;
                                $resume->languages()->save($language);
                                DB::commit();
                                $count += 1;
                        }
                        return true;
                }catch(QueryException $e){
                        if ($e->getCode() == 1213) {
                                $attempt++;
                                Log::warning("Deadlock detected. Retry attempt $attempt");
                        } else {
                                throw $e;
                        }
                }
        }
    }
}