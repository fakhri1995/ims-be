<?php

namespace App\Jobs;

use App\Recruitment;
use App\TicketStatus;
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
use App\ResumeTraining;
use Dom\Attr;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessRabbitMQMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function fire($job)
    {
        $data = (object) json_decode($job->getRawBody(), true)["data"]["data"];
        Log::info('handling job', (array) $data);
        $email = $data->user["email"];
        $normalizedEmail = is_array($email) ? implode(',', $email) : $email;

        $resume = new Resume;
        $resume->name = $data->user["name"];
        $resume->telp = $data->user["phone"];
        $resume->email = $normalizedEmail;
        $resume->city = $data->user["location"];
        $resume->province = $data->user["location"];
        $resume->created_at = Date('Y-m-d H:i:s');
        $resume->updated_at = Date('Y-m-d H:i:s');
        $resume->created_by = 1;
        
        $resume->linkedin = $data->user["linkedin"];
        $resume->summary = $data->user["summary"];

        if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];
        $resume->owner_id = $resume->id;
        $resume->save();

        $recruitment = new Recruitment;
        $recruitment->name = $resume->name;
        $recruitment->email = $resume->email;
        $recruitment->recruitment_role_id = 0;
        $recruitment->recruitment_jalur_daftar_id = 1;
        $recruitment->recruitment_stage_id = 1;
        $recruitment->recruitment_status_id = 0;
        $recruitment->cv_processing_status = 1;
        $recruitment->cv_processing_batch = $resume->id;
        $recruitment->lampiran = [];
        $recruitment->created_at = date("Y-m-d H:i:s");
        $recruitment->updated_at = date("Y-m-d H:i:s");
        $recruitment->created_by = 1;
        $recruitment->owner_id = $resume->id;
        
        if (!$recruitment->save()) return ["success" => false, "message" => "Gagal Menambah Recruitment", "status" => 400];


        foreach($data->projects as $project){
                DB::beginTransaction();
                $requestProject = (object)$project;

                $after_id = $requestProject->after_id ?? NULL;
                if ($after_id != NULL) {
                        $projectAfter = ResumeProject::find($after_id);
                        if (!$projectAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $project = new ResumeProject();
                $project->name = $requestProject->name;
                $project->year = !$requestProject->end_date ? '0000-00-00' : $requestProject->end_date;
                $project->description = $requestProject->description ?? "";

                $projects = new ResumeProject();
                if ($after_id == NULL) {
                        $projects->increment("display_order");
                        $project->display_order = 1;
                } else {
                        $projects->where("display_order", ">", $projectAfter->display_order)->increment("display_order");
                        $project->display_order = $projectAfter->display_order + 1;
                }

                $resume->projects()->save($project);
                DB::commit();
        }

        foreach($data->experience as $experience){
                DB::beginTransaction();
                    $requestExperience = (object)$experience;

                    $after_id = $requestExperience->after_id ?? NULL;
                    if ($after_id != NULL) {
                            $experienceAfter = ResumeExperience::find($after_id);
                            if (!$experienceAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                    }

                    $experience = new ResumeExperience();
                    $responsibilities = $requestExperience->responsibility;
                    $normalized_responsibilities = is_array($responsibilities) ? implode(',', $responsibilities) : $responsibilities;
                    $experience->role = $requestExperience->position;
                    $experience->company = $requestExperience->company;
                    $experience->start_date = $requestExperience->start_date;
                    $experience->end_date = $requestExperience->end_date;
                    $experience->description = $normalized_responsibilities;

                    //new
                    $experience->achievements = implode(', ', $requestExperience->achievements);
                    $experience->technologies = implode(', ', $requestExperience->technologies);

                    $experiences = new ResumeExperience();
                    if ($after_id == NULL) {
                            $experiences->increment("display_order");
                            $experience->display_order = 1;
                    } else {
                            $experiences->where("display_order", ">", $experienceAfter->display_order)->increment("display_order");
                            $experience->display_order = $experienceAfter->display_order + 1;
                    }

                    $resume->experiences()->save($experience);
                DB::commit();
        }

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
                $education->start_date = $requestEducation->start_date ? date('Y-m-01', strtotime($requestEducation->start_date)) : null;
                $education->end_date = $requestEducation->end_date ? date('Y-m-t', strtotime($requestEducation->end_date . '-01')) : null;

                //new
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
        }

        foreach($data->skills as $skill){
                $requestSkill = (object)$skill;
                $skill = new ResumeSkill();
                $skill->name = ucfirst($requestSkill->skill_name);
                $resume->skills()->save($skill);
        }

        foreach($data->tools as $tool){
                $requestSkill = (object)$tool;
                $skill = new ResumeSkill();
                $skill->name = ucfirst($requestSkill->tool_name);
                $resume->skills()->save($skill);
        }

        foreach($data->achievements as $achievement){
                DB::beginTransaction();
                $requestAchievement = (object)$achievement;

                $after_id = $requestAchievement->after_id ?? NULL;
                if ($after_id != NULL) {
                        $achievementAfter = ResumeAchievement::find($after_id);
                        if (!$achievementAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $achievement = new ResumeAchievement();
                $achievement->name = $requestAchievement->description;
                $achievement->organizer = $requestAchievement->organization ?? "";
                $achievement->year = !$requestAchievement->date ? '0000-00-00' : $requestAchievement->date;

                $achievements = new ResumeAchievement();
                if ($after_id == NULL) {
                        $achievements->increment("display_order");
                        $achievement->display_order = 1;
                } else {
                        $achievements->where("display_order", ">", $achievementAfter->display_order)->increment("display_order");
                        $achievement->display_order = $achievementAfter->display_order + 1;
                }

                $resume->achievements()->save($achievement);
                DB::commit();
        }

        foreach($data->certifications as $certificate){
                DB::beginTransaction();
                $requestCertificates = (object)$certificate;

                $after_id = $requestCertificates->after_id ?? NULL;
                if ($after_id != NULL) {
                        $certificateAfter = ResumeCertificate::find($after_id);
                        if (!$certificateAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $certificate = new ResumeCertificate();
                $certificate->name = $requestCertificates->name;
                $certificate->organizer = $requestCertificates->issuer ?? "";
                $certificate->year = !$requestCertificates->date_earned ? "2000-01-01" : $requestCertificates->date_earned;

                $certificates = new ResumeCertificate();
                if ($after_id == NULL) {
                        $certificates->increment("display_order");
                        $certificate->display_order = 1;
                } else {
                        $certificates->where("display_order", ">", $certificateAfter->display_order)->increment("display_order");
                        $certificate->display_order = $certificateAfter->display_order + 1;
                }

                $resume->certificates()->save($certificate);
                DB::commit();
        }

        foreach($data->languages as $language){
                DB::beginTransaction();
                $requestLanguage = (object)$language;

                $after_id = $requestLanguage->after_id ?? NULL;
                if ($after_id != NULL) {
                        $languageAfter = ResumeLanguage::find($after_id);
                        if (!$languageAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $language = new ResumeLanguage();
                $language->language = $requestLanguage->language;
                $language->proficiency = $requestLanguage->proficiency;
                $language->certifications = implode(', ', $requestLanguage->certifications);
                

                $certificates = new ResumeCertificate();
                if ($after_id == NULL) {
                        $certificates->increment("display_order");
                        $certificate->display_order = 1;
                } else {
                        $certificates->where("display_order", ">", $certificateAfter->display_order)->increment("display_order");
                        $certificate->display_order = $certificateAfter->display_order + 1;
                }

                $resume->certificates()->save($certificate);
                DB::commit();
        }
    }
}