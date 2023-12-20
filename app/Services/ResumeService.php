<?php

namespace App\Services;

use App\Assessment;
use App\AssessmentDetail;
use App\Resume;
use App\ResumeAchievement;
use App\ResumeAssessment;
use App\ResumeAssessmentDetail;
use App\ResumeAssessmentResult;
use App\ResumeCertificate;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeProject;
use App\ResumeSkill;
use App\ResumeSkillLists;
use App\ResumeTraining;
use App\ResumeSummary;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResumeService
{

    protected $globalService;

    public function __construct()
    {
        $this->globalService = new GlobalService;
    }


    public function getResumes(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        // if($access["success"] === false) return $access;
        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:name,role,email,telp",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }


        try {
            $assessment_ids = $request->assessment_ids ? explode(",", $request->assessment_ids) : NULL;
            $keyword = $request->keyword ?? NULL;
            // $resumes = Resume::with(['educations', 'experiences', 'projects', 'skills', 'trainings', 'certificates', 'achievements', 'assessmentResults', 'summaries']);
            $resumes = Resume::with("assessment");
            if (auth()->user()->role == $this->globalService->guest_role_id) {
                $resumes = $resumes->where("owner_id", auth()->user()->id);
            }
            $rows = $request->rows ?? 5;

            // filter
            if ($keyword) $resumes = $resumes->where("name", "LIKE", "%$keyword%");
            if ($assessment_ids) $resumes = $resumes->whereIn("assessment_id", $assessment_ids);

            // sort
            $sort_by = $request->sort_by ?? NULL;
            $sort_type = $request->get('sort_type', 'asc');
            if ($sort_by == "name") $resumes = $resumes->orderBy('name', $sort_type);
            if ($sort_by == "role") $resumes = $resumes->orderBy(ResumeAssessment::select("name")
                ->whereColumn("resume_assessments.id", "resumes.assessment_id"), $sort_type);

            if ($sort_by == "email") $resumes = $resumes->orderBy('email', $sort_type);
            if ($sort_by == "telp") $resumes = $resumes->orderBy('telp', $sort_type);

            $resumes = $resumes->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resumes, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getResume(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "exists:App\Resume,id|numeric|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $resume = Resume::with(['educations', 'experiences', 'projects', 'skills', 'trainings', 'certificates', 'achievements', 'assessment', 'assessmentResults', 'summaries', 'profileImage'])->find($id);
        if (!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if (auth()->user()->role == $this->globalService->guest_role_id && $resume->owner_id != auth()->user()->id) {
            return ["success" => false, "message" => "Anda tidak memiliki akses ke resume ini", "status" => 400];
        }

        try {
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resume, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addResume(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "telp" => "required|numeric",
            "email" => "required|email",
            "city" => "required",
            "province" => "required",
            "assessment_id" => "required|numeric|nullable",
            'profile_image' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            // Resume Basic Information
            $resume = new Resume();
            $resume->name = $request->name;
            $resume->telp = $request->telp;
            $resume->email = $request->email;
            $resume->city = $request->city;
            $resume->province = $request->province;
            $resume->assessment_id = $request->assessment_id;
            $resume->created_at = Date('Y-m-d H:i:s');
            $resume->updated_at = Date('Y-m-d H:i:s');
            $resume->created_by = auth()->user()->id;
            if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];

            if (method_exists($request, 'hasFile') && $request->hasFile('profile_image')) {
                $fileService = new FileService;
                $file = $request->file('profile_image');
                $table = 'App\Resume';
                $description = 'profile_image';
                $folder_detail = 'Resumes';

                $fileService->addFile($resume->id, $file, $table, $description, $folder_detail);
            }

            // assessment section
            $assessment_id = $request->assessment_id;
            $assessment = ResumeAssessment::with("details")->find($assessment_id);
            if (!$assessment) return ["success" => false, "message" => "Data Assessment Tidak Ditemukan", "status" => 400];
            $resumeAssessmentResultsObjArr = [];
            $count = 0;
            foreach ($assessment->details as $ad) {
                $resumeAssessmentResult = new ResumeAssessmentResult();
                $resumeAssessmentResult->criteria = $ad->criteria;
                $resumeAssessmentResult->value = "";
                $resumeAssessmentResultsObjArr[] = $resumeAssessmentResult;
                $count++;
            }
            if (!$resume->assessmentResults()->saveMany($resumeAssessmentResultsObjArr)) return ["success" => false, "message" => "Gagal Menambah Resume Assessment Result", "status" => 400];
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $resume->id, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addResumeSection(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\Resume,id",

            "education" => "filled|array",
            "education.university" => "required_with:education",
            "education.major" => "required_with:education",
            "education.gpa" => "numeric|between:0.00,4.00|nullable",
            "education.start_date" => "date_format:Y-m|nullable",
            "education.end_date" => "date_format:Y-m|nullable",

            "experience" => "filled|array",
            "experience.role" => "required_with:experience",
            "experience.company" => "required_with:experience",
            "experience.start_date" => "required_with:experience|date",
            "experience.end_date" => "date|nullable",
            "experience.description" => "required_with:experience",

            "project" => "filled|array",
            "project.name" => "required_with:project",
            "project.year" => "date|nullable",
            "project.description" => "string|nullable",

            "skill" => "filled|array",
            "skill.name" => "required_with:skill",

            "training" => "filled|array",
            "training.name" => "required_with:training",
            "training.organizer" => "string|nullable",
            "training.year" => "date|nullable",

            "certificate" => "filled|array",
            "certificate.name" => "required_with:certificate",
            "certificate.organizer" => "string|nullable",
            "certificate.year" => "date|nullable",

            "achievement" => "filled|array",
            "achievement.name" => "required_with:achievement",
            "achievement.organizer" => "string|nullable",
            "achievement.year" => "date|nullable",

            "summary" => "filled|array",
            "summary.description" => "required_with:summary"
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $resume_id = $request->id;
        $resume = Resume::find($resume_id);
        if (!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if (auth()->user()->role == $this->globalService->guest_role_id && $resume->owner_id != auth()->user()->id) {
            return ["success" => false, "message" => "Anda tidak memiliki akses ke resume ini", "status" => 400];
        }

        if ($request->education) {
            try {
                DB::beginTransaction();
                $requestEducation = (object)$request->education;

                $after_id = $requestEducation->after_id ?? NULL;
                if ($after_id != NULL) {
                    $educationAfter = ResumeEducation::find($after_id);
                    if (!$educationAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $education = new ResumeEducation();
                $education->university = $requestEducation->university;
                $education->major = $requestEducation->major;
                $education->gpa = !$requestEducation->gpa ? NULL : $requestEducation->gpa;
                $education->start_date = $requestEducation->start_date ? date('Y-m-01', strtotime($requestEducation->start_date)): null;
                $education->end_date = $requestEducation->end_date ? date('Y-m-t', strtotime($requestEducation->end_date . '-01')) : null;

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
                return ["success" => true, "message" => "Data Education Berhasil Ditambah",  "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Education Resume", "status" => 400];
            }
        } else if ($request->experience) {
            try {
                DB::beginTransaction();
                $requestExperience = (object)$request->experience;

                $after_id = $requestExperience->after_id ?? NULL;
                if ($after_id != NULL) {
                    $experienceAfter = ResumeExperience::find($after_id);
                    if (!$experienceAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $experience = new ResumeExperience();
                $experience->role = $requestExperience->role;
                $experience->company = $requestExperience->company;
                $experience->start_date = $requestExperience->start_date;
                $experience->end_date = $requestExperience->end_date;
                $experience->description = $requestExperience->description;

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
                return ["success" => true, "message" => "Data Experience Berhasil Ditambah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Experience Resume", "status" => 400];
            }
        } else if ($request->project) {
            try {
                DB::beginTransaction();
                $requestProject = (object)$request->project;

                $after_id = $requestProject->after_id ?? NULL;
                if ($after_id != NULL) {
                    $projectAfter = ResumeProject::find($after_id);
                    if (!$projectAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $project = new ResumeProject();
                $project->name = $requestProject->name;
                $project->year = !$requestProject->year ? null : $requestProject->year;
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
                return ["success" => true, "message" => "Data Project Berhasil Ditambah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Project Resume", "status" => 400];
            }
        } else if ($request->skill) {
            $requestSkill = (object)$request->skill;
            $skill = new ResumeSkill();
            $skill->name = ucfirst($requestSkill->name);
            DB::table('resume_skill_lists')->insertOrIgnore([['name' => $skill->name]]);
            if (!$resume->skills()->save($skill)) return ["success" => false, "message" => "Gagal Mengubah Skill Resume", "status" => 400];
            return ["success" => true, "message" => "Data Skill Berhasil Ditambah", "status" => 200];
        } else if ($request->training) {
            try {
                DB::beginTransaction();
                $requestTraining = (object)$request->training;

                $after_id = $requestTraining->after_id ?? NULL;
                if ($after_id != NULL) {
                    $trainingAfter = ResumeTraining::find($after_id);
                    if (!$trainingAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $training = new ResumeTraining();
                $training->name = $requestTraining->name;
                $training->organizer = $requestTraining->organizer ?? "";
                $training->year = !$requestTraining->year ? null : $requestTraining->year;

                $trainings = new ResumeTraining();
                if ($after_id == NULL) {
                    $trainings->increment("display_order");
                    $training->display_order = 1;
                } else {
                    $trainings->where("display_order", ">", $trainingAfter->display_order)->increment("display_order");
                    $training->display_order = $trainingAfter->display_order + 1;
                }

                $resume->trainings()->save($training);
                DB::commit();
                return ["success" => true, "message" => "Data Training Berhasil Ditambah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Training Resume", "status" => 400];
            }
        } else if ($request->certificate) {
            try {
                DB::beginTransaction();
                $requestCertificates = (object)$request->certificate;

                $after_id = $requestCertificates->after_id ?? NULL;
                if ($after_id != NULL) {
                    $certificateAfter = ResumeCertificate::find($after_id);
                    if (!$certificateAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $certificate = new ResumeCertificate();
                $certificate->name = $requestCertificates->name;
                $certificate->organizer = $requestCertificates->organizer ?? "";
                $certificate->year = !$requestCertificates->year ? null : $requestCertificates->year;

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
                return ["success" => true, "message" => "Data Certificate Berhasil Ditambah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Certificate Resume", "status" => 400];
            }
        } else if ($request->achievement) {
            try {
                DB::beginTransaction();
                $requestAchievement = (object)$request->achievement;

                $after_id = $requestAchievement->after_id ?? NULL;
                if ($after_id != NULL) {
                    $achievementAfter = ResumeAchievement::find($after_id);
                    if (!$achievementAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
                }

                $achievement = new ResumeAchievement();
                $achievement->name = $requestAchievement->name;
                $achievement->organizer = $requestAchievement->organizer ?? "";
                $achievement->year = !$requestAchievement->year ? null : $requestAchievement->year;

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
                return ["success" => true, "message" => "Data Achievement Berhasil Ditambah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Achievement Resume", "status" => 400];
            }
        } else if ($request->summary) {
            $requestSummary = (object)$request->summary;
            $summary = new ResumeSummary();
            $summary->description = $requestSummary->description;
            if (!$resume->summaries()->save($summary)) return ["success" => false, "message" => "Gagal Mengubah Summary Resume", "status" => 400];
            return ["success" => true, "message" => "Data Summary Berhasil Ditambah", "status" => 200];
        }
        try {
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateResume(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\Resume,id",
            "basic_information" => "filled|array",
            "basic_information.name" => "required_with:basic_information|filled",
            "basic_information.telp" => "required_with:basic_information|filled|numeric",
            "basic_information.email" => "required_with:basic_information|filled|email",
            "basic_information.city" => "required_with:basic_information|filled",
            "basic_information.province" => "required_with:basic_information|filled",
            "basic_information.assessment_id" => "numeric|exists:App\ResumeAssessment,id|nullable",
            'basic_information.profile_image' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',

            "education" => "filled|array",
            "education.id" => "required_with:education|exists:App\ResumeEducation,id",
            "education.university" => "required_with:education",
            "education.major" => "required_with:education",
            "education.gpa" => "numeric|between:0.00,4.00|nullable",
            "education.start_date" => "date_format:Y-m|nullable",
            "education.end_date" => "date_format:Y-m|nullable",

            "experience" => "filled|array",
            "experience.id" => "required_with:experienc|exists:App\ResumeExperience,id",
            "experience.role" => "required_with:experience",
            "experience.company" => "required_with:experience",
            "experience.start_date" => "required_with:experience|date",
            "experience.end_date" => "date|nullable",
            "experience.description" => "required_with:experience",

            "project" => "filled|array",
            "project.id" => "required_with:project|exists:App\ResumeProject,id",
            "project.name" => "required_with:project",
            "project.year" => "date|nullable",
            "project.description" => "string|nullable",

            "skill" => "filled|array",
            "skill.id" => "required_with:skill|exists:App\ResumeSkill,id",
            "skill.name" => "required_with:skill",

            "training" => "filled|array",
            "training.id" => "required_with:training|exists:App\ResumeTraining,id",
            "training.name" => "required_with:training",
            "training.organizer" => "string|nullable",
            "training.year" => "date|nullable",

            "certificate" => "filled|array",
            "certificate.id" => "required_with:certificate|exists:App\ResumeCertificate,id",
            "certificate.name" => "required_with:certificate",
            "certificate.organizer" => "string|nullable",
            "certificate.year" => "date|nullable",

            "achievement" => "filled|array",
            "achievement.id" => "required_with:achievement|exists:App\ResumeAchievement,id",
            "achievement.name" => "required_with:achievement",
            "achievement.organizer" => "string|nullable",
            "achievement.year" => "date|nullable",

            "summary" => "filled|array",
            "summary.description" => "required_with:summary"
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $resume_id = $request->id;
        $resume = Resume::with('profileImage')->find($resume_id);
        if (!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if (auth()->user()->role == $this->globalService->guest_role_id && $resume->owner_id != auth()->user()->id) {
            return ["success" => false, "message" => "Anda tidak memiliki akses ke resume ini", "status" => 400];
        }

        // resume basic information
        if ($request->basic_information) {
            try {
                DB::beginTransaction();
                $requestBasicInformation = (object)$request->basic_information;
                $resume->telp = $requestBasicInformation->telp;
                $resume->city = $requestBasicInformation->city;
                $resume->province = $requestBasicInformation->province;
                $resume->updated_at = Date('Y-m-d H:i:s');
                if (auth()->user()->role != $this->globalService->guest_role_id) {
                    $resume->name = $requestBasicInformation->name;
                    $resume->email = $requestBasicInformation->email;
                }

                // if assessment changes
                if ($requestBasicInformation->assessment_id) {
                    $assessmentResults = $resume->assessmentResults();
                    if (count($assessmentResults->get()) > 0) {
                        if (!$assessmentResults->delete()) return ["success" => false, "message" => "Gagal Menghapus Assessment Lama", "status" => 400];
                    }

                    $resume->assessment_id = $requestBasicInformation->assessment_id;
                    $assessment_id = $resume->assessment_id;
                    if (!$resume->save()) return ["success" => false, "message" => "Gagal Mengubah Resume", "status" => 400];

                    if ($request->hasFile('basic_information.profile_image')) {
                        $fileService = new FileService;
                        $file = $request->file('basic_information.profile_image');
                        $table = 'App\Resume';
                        $description = 'profile_image';
                        $folder_detail = 'Resumes';
                        if ($resume->profileImage->id) {
                            $del = $fileService->deleteForceFile($resume->profileImage->id);
                        }
                        $add = $fileService->addFile($resume->id, $file, $table, $description, $folder_detail);
                    }

                    $assessment = ResumeAssessment::with("details")->find($assessment_id);
                    if (!$assessment) return ["success" => false, "message" => "Data Assessment Tidak Ditemukan", "status" => 400];
                    $resumeAssessmentResultsObjArr = [];
                    $count = 0;
                    foreach ($assessment->details as $ad) {
                        $resumeAssessmentResult = new ResumeAssessmentResult();
                        $resumeAssessmentResult->criteria = $ad->criteria;
                        $resumeAssessmentResult->value = "";
                        $resumeAssessmentResultsObjArr[] = $resumeAssessmentResult;
                        $count++;
                    }
                    if (!$resume->assessmentResults()->saveMany($resumeAssessmentResultsObjArr)) return ["success" => false, "message" => "Gagal Menambah Resume Assessment Result", "status" => 400];
                }
                DB::commit();
                return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
            } catch (\Error $err) {
                return ["success" => false, "message" => $err, "status" => 400];
                DB::rollBack();
            }
        } else if ($request->education) {
            try {
                DB::beginTransaction();
                $requestEducation = (object)$request->education;
                $id = $requestEducation->id;
                $education = $resume->educations()->find($id);
                if (!$education) return ["success" => false, "message" => "Education ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];

                $after_id = $requestEducation->after_id ?? NULL;
                if ($after_id != NULL) {
                    $educationAfter = $resume->educations()->find($after_id);
                    if (!$educationAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
                }

                if ($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400];

                $education->university = $requestEducation->university;
                $education->major = $requestEducation->major;
                $education->gpa = !$requestEducation->gpa ? NULL : $requestEducation->gpa;
                $education->start_date = $requestEducation->start_date ? date('Y-m-01', strtotime($requestEducation->start_date)): null;
                $education->end_date = $requestEducation->end_date ? date('Y-m-t', strtotime($requestEducation->end_date . '-01')) : null;

                $educations = new ResumeEducation();
                if ($after_id == NULL) {
                    $educations->where("display_order", "<", $education->display_order)->increment("display_order");
                    $education->display_order = 1;
                } else {
                    if ($educationAfter->display_order < $education->display_order) {
                        $educations
                            ->where("display_order", ">", $educationAfter->display_order)
                            ->where("display_order", "<", $education->display_order)
                            ->increment("display_order");
                        $education->display_order = $educationAfter->display_order + 1;
                    } else {
                        $educations
                            ->where("display_order", ">", $education->display_order)
                            ->where("display_order", "<=", $educationAfter->display_order)
                            ->decrement("display_order");
                        $education->display_order = $educationAfter->display_order;
                    }
                }

                $education->save();
                DB::commit();
                return ["success" => true, "message" => "Data Education Berhasil Diubah", "id" => $resume->id, "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Education Resume", "status" => 400];
            }
        } else if ($request->experience) {
            try {
                DB::beginTransaction();

                $requestExperience = (object)$request->experience;
                $id = $requestExperience->id;
                $experience = $resume->experiences()->find($id);
                if (!$experience) return ["success" => false, "message" => "Experience ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];

                $after_id = $requestExperience->after_id ?? NULL;
                if ($after_id != NULL) {
                    $experienceAfter = $resume->experiences()->find($after_id);
                    if (!$experienceAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
                }

                if ($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400];

                $experience->role = $requestExperience->role;
                $experience->company = $requestExperience->company;
                $experience->start_date = $requestExperience->start_date;
                $experience->end_date = $requestExperience->end_date;
                $experience->description = $requestExperience->description;

                $experiences = new ResumeExperience();
                if ($after_id == NULL) {
                    $experiences->where("display_order", "<", $experience->display_order)->increment("display_order");
                    $experience->display_order = 1;
                } else {
                    if ($experienceAfter->display_order < $experience->display_order) {
                        $experiences
                            ->where("display_order", ">", $experienceAfter->display_order)
                            ->where("display_order", "<", $experience->display_order)
                            ->increment("display_order");
                        $experience->display_order = $experienceAfter->display_order + 1;
                    } else {
                        $experiences
                            ->where("display_order", ">", $experience->display_order)
                            ->where("display_order", "<=", $experienceAfter->display_order)
                            ->decrement("display_order");
                        $experience->display_order = $experienceAfter->display_order;
                    }
                }

                $experience->save();

                DB::commit();
                return ["success" => true, "message" => "Data Experience Berhasil Diubah", "status" => 200];
            } catch (\Throwable $th) {
                throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Experience Resume", "status" => 400];
            }
        } else if ($request->project) {
            try {
                DB::beginTransaction();
                $requestProject = (object)$request->project;
                $id = $requestProject->id;
                $project = $resume->projects()->find($id);
                if (!$project) return ["success" => false, "message" => "Project ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];

                $after_id = $requestProject->after_id ?? NULL;
                if ($after_id != NULL) {
                    $projectAfter = $resume->projects()->find($after_id);
                    if (!$projectAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
                }

                if ($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400];

                $project->name = $requestProject->name;
                $project->year = !$requestProject->year ? null : $requestProject->year;
                $project->description = $requestProject->description ?? "";

                $projects = new ResumeProject();
                if ($after_id == NULL) {
                    $projects->where("display_order", "<", $project->display_order)->increment("display_order");
                    $project->display_order = 1;
                } else {
                    if ($projectAfter->display_order < $project->display_order) {
                        $projects
                            ->where("display_order", ">", $projectAfter->display_order)
                            ->where("display_order", "<", $project->display_order)
                            ->increment("display_order");
                        $project->display_order = $projectAfter->display_order + 1;
                    } else {
                        $projects
                            ->where("display_order", ">", $project->display_order)
                            ->where("display_order", "<=", $projectAfter->display_order)
                            ->decrement("display_order");
                        $project->display_order = $projectAfter->display_order;
                    }
                }

                $project->save();
                DB::commit();
                return ["success" => true, "message" => "Data Project Berhasil Diubah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Project Resume", "status" => 400];
            }
        } else if ($request->skill) {
            $requestSkill = (object)$request->skill;
            $id = $requestSkill->id;
            $skill = $resume->skills()->find($id);
            if (!$skill) return ["success" => false, "message" => "Skill ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];
            $skill->name = ucfirst($requestSkill->name);
            DB::table('resume_skill_lists')->insertOrIgnore([['name' => $skill->name]]);
            if (!$skill->save()) return ["success" => false, "message" => "Gagal Mengubah Skill Resume", "status" => 400];
            return ["success" => true, "message" => "Data Skill Berhasil Diubah", "status" => 200];
        } else if ($request->training) {
            try {
                DB::beginTransaction();
                $requestTraining = (object)$request->training;
                $id = $requestTraining->id;
                $training = $resume->trainings()->find($id);
                if (!$training) return ["success" => false, "message" => "Training ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];

                $after_id = $requestTraining->after_id ?? NULL;
                if ($after_id != NULL) {
                    $trainingAfter = $resume->trainings()->find($after_id);
                    if (!$trainingAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
                }

                if ($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400];

                $training->name = $requestTraining->name;
                $training->organizer = $requestTraining->organizer ?? "";
                $training->year = !$requestTraining->year ? null : $requestTraining->year;

                $trainings = new ResumeTraining();
                if ($after_id == NULL) {
                    $trainings->where("display_order", "<", $training->display_order)->increment("display_order");
                    $training->display_order = 1;
                } else {
                    if ($trainingAfter->display_order < $training->display_order) {
                        $trainings
                            ->where("display_order", ">", $trainingAfter->display_order)
                            ->where("display_order", "<", $training->display_order)
                            ->increment("display_order");
                        $training->display_order = $trainingAfter->display_order + 1;
                    } else {
                        $trainings
                            ->where("display_order", ">", $training->display_order)
                            ->where("display_order", "<=", $trainingAfter->display_order)
                            ->decrement("display_order");
                        $training->display_order = $trainingAfter->display_order;
                    }
                }

                $training->save();
                DB::commit();
                return ["success" => true, "message" => "Data Training Berhasil Diubah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Training Resume", "status" => 400];
            }
        } else if ($request->certificate) {
            try {
                DB::beginTransaction();
                $requestCertificates = (object)$request->certificate;
                $id = $requestCertificates->id;
                $certificate = $resume->certificates()->find($id);
                if (!$certificate) return ["success" => false, "message" => "Certificate ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];

                $after_id = $requestCertificates->after_id ?? NULL;
                if ($after_id != NULL) {
                    $certificateAfter = $resume->certificates()->find($after_id);
                    if (!$certificateAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
                }

                if ($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400];

                $certificate->name = $requestCertificates->name;
                $certificate->organizer = $requestCertificates->organizer ?? "";
                $certificate->year = !$requestCertificates->year ? null : $requestCertificates->year;

                $certificates = new ResumeCertificate();
                if ($after_id == NULL) {
                    $certificates->where("display_order", "<", $certificate->display_order)->increment("display_order");
                    $certificate->display_order = 1;
                } else {
                    if ($certificateAfter->display_order < $certificate->display_order) {
                        $certificates
                            ->where("display_order", ">", $certificateAfter->display_order)
                            ->where("display_order", "<", $certificate->display_order)
                            ->increment("display_order");
                        $certificate->display_order = $certificateAfter->display_order + 1;
                    } else {
                        $certificates
                            ->where("display_order", ">", $certificate->display_order)
                            ->where("display_order", "<=", $certificateAfter->display_order)
                            ->decrement("display_order");
                        $certificate->display_order = $certificateAfter->display_order;
                    }
                }

                $certificate->save();
                DB::commit();
                return ["success" => true, "message" => "Data Certificate Berhasil Diubah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Certificate Resume", "status" => 400];
            }
        } else if ($request->achievement) {
            try {
                DB::beginTransaction();
                $requestAchievement = (object)$request->achievement;
                $id = $requestAchievement->id;
                $achievement = $resume->achievements()->find($id);
                if (!$achievement) return ["success" => false, "message" => "Achievement ID : [$id] bukan child dari Resume ID : [$resume_id]", "status" => 400];

                $after_id = $requestAchievement->after_id ?? NULL;
                if ($after_id != NULL) {
                    $achievementAfter = $resume->achievements()->find($after_id);
                    if (!$achievementAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
                }

                if ($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400];

                $achievement->name = $requestAchievement->name;
                $achievement->organizer = $requestAchievement->organizer ?? "";
                $achievement->year = !$requestAchievement->year ? null : $requestAchievement->year;

                $achievements = new ResumeAchievement();
                if ($after_id == NULL) {
                    $achievements->where("display_order", "<", $achievement->display_order)->increment("display_order");
                    $achievement->display_order = 1;
                } else {
                    if ($achievementAfter->display_order < $achievement->display_order) {
                        $achievements
                            ->where("display_order", ">", $achievementAfter->display_order)
                            ->where("display_order", "<", $achievement->display_order)
                            ->increment("display_order");
                        $achievement->display_order = $achievementAfter->display_order + 1;
                    } else {
                        $achievements
                            ->where("display_order", ">", $achievement->display_order)
                            ->where("display_order", "<=", $achievementAfter->display_order)
                            ->decrement("display_order");
                        $achievement->display_order = $achievementAfter->display_order;
                    }
                }

                $achievement->save();
                DB::commit();
                return ["success" => true, "message" => "Data Achievement Berhasil Diubah", "status" => 200];
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return ["success" => false, "message" => "Gagal Mengubah Achievement Resume", "status" => 400];
            }
        } else if ($request->summary) {
            $requestSummary = (object)$request->summary;
            $summary = $resume->summaries;
            $summary->description = $requestSummary->description;
            if (!$summary->save()) return ["success" => false, "message" => "Gagal Mengubah Summary Resume", "status" => 400];
            return ["success" => true, "message" => "Data Summary Berhasil Diubah", "status" => 200];
        }
    }

    public function deleteResume(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "exists:App\Resume,id|numeric|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $resume = resume::find($id);
        if (!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if (auth()->user()->role == $this->globalService->guest_role_id && $resume->owner_id != auth()->user()->id) {
            return ["success" => false, "message" => "Anda tidak memiliki akses ke resume ini", "status" => 400];
        }

        try {
            $resume->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $resume, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteResumeSection(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "exists:App\Resume,id|numeric|required",
            "education_id" => "filled|exists:App\ResumeEducation,id",
            "experience_id" => "filled|exists:App\ResumeExperience,id",
            "project_id" => "filled|exists:App\ResumeProject,id",
            "skill_id" => "filled|exists:App\ResumeSkill,id",
            "training_id" => "filled|exists:App\ResumeTraining,id",
            "certificate_id" => "filled|exists:App\ResumeCertificate,id",
            "achievement_id" => "filled|exists:App\ResumeAchievement,id",
            "summary_id" => "filled|exists:App\ResumeSummary,id",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $resume_id = $request->id;
            $resume = resume::find($resume_id);
            if (!$resume) return ["success" => false, "message" => "Data Resume Tidak Ditemukan", "status" => 400];
            if (auth()->user()->role == $this->globalService->guest_role_id && $resume->owner_id != auth()->user()->id) {
                return ["success" => false, "message" => "Anda tidak memiliki akses ke resume ini", "status" => 400];
            }
            if ($request->education_id) {
                $model = $resume->educations()->find($request->education_id);
                if (!$model) return ["success" => false, "message" => "Data Education yang dihapus tidak valid", "status" => 400];
                try {
                    DB::beginTransaction();
                    if ($model->display_order === 1) {
                        $rotation = $resume->educations()->where('display_order', '!=', 1)->latest('display_order')->first();
                        if ($rotation) {
                            $rotation->display_order = 1;
                            $rotation->save();
                        }
                    }
                    $model->delete();
                    DB::commit();
                    return ["success" => true, "message" => "Data Resume Education Dihapus", "status" => 200];
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return ["success" => false, "message" => "Gagal Menghapus Education Resume", "status" => 400];
                }
            } else if ($request->experience_id) {
                $model = $resume->experiences()->find($request->experience_id);
                if (!$model) return ["success" => false, "message" => "Data Experience yang dihapus tidak valid", "status" => 400];
                try {
                    DB::beginTransaction();
                    if ($model->display_order === 1) {
                        $rotation = $resume->experiences()->where('display_order', '!=', 1)->latest('display_order')->first();
                        if ($rotation) {
                            $rotation->display_order = 1;
                            $rotation->save();
                        }
                    }
                    $model->delete();
                    DB::commit();
                    return ["success" => true, "message" => "Data Resume Experience Dihapus", "status" => 200];
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return ["success" => false, "message" => "Gagal Menghapus Experience Resume", "status" => 400];
                }
            } else if ($request->project_id) {
                $model = $resume->projects()->find($request->project_id);
                if (!$model) return ["success" => false, "message" => "Data Project yang dihapus tidak valid", "status" => 400];
                try {
                    DB::beginTransaction();
                    if ($model->display_order === 1) {
                        $rotation = $resume->projects()->where('display_order', '!=', 1)->latest('display_order')->first();
                        if ($rotation) {
                            $rotation->display_order = 1;
                            $rotation->save();
                        }
                    }
                    $model->delete();
                    DB::commit();
                    return ["success" => true, "message" => "Data Resume Project Dihapus", "status" => 200];
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return ["success" => false, "message" => "Gagal Menghapus Project Resume", "status" => 400];
                }
            } else if ($request->skill_id) {
                $model = $resume->skills()->find($request->skill_id);
                if (!$model) return ["success" => false, "message" => "Data Skill yang dihapus tidak valid", "status" => 400];
                if (!$model->delete()) return ["success" => false, "message" => "Gagal Menghapus Skill Resume", "status" => 400];
                return ["success" => true, "message" => "Data Resume Skill Dihapus", "status" => 200];
            } else if ($request->training_id) {
                $model = $resume->trainings()->find($request->training_id);
                if (!$model) return ["success" => false, "message" => "Data Training yang dihapus tidak valid", "status" => 400];
                try {
                    DB::beginTransaction();
                    if ($model->display_order === 1) {
                        $rotation = $resume->trainings()->where('display_order', '!=', 1)->latest('display_order')->first();
                        if ($rotation) {
                            $rotation->display_order = 1;
                            $rotation->save();
                        }
                    }
                    $model->delete();
                    DB::commit();
                    return ["success" => true, "message" => "Data Training Project Dihapus", "status" => 200];
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return ["success" => false, "message" => "Gagal Menghapus Project Training", "status" => 400];
                }
            } else if ($request->certificate_id) {
                $model = $resume->certificates()->find($request->certificate_id);
                if (!$model) return ["success" => false, "message" => "Data Certificate yang dihapus tidak valid", "status" => 400];
                try {
                    DB::beginTransaction();
                    if ($model->display_order === 1) {
                        $rotation = $resume->certificates()->where('display_order', '!=', 1)->latest('display_order')->first();
                        if ($rotation) {
                            $rotation->display_order = 1;
                            $rotation->save();
                        }
                    }
                    $model->delete();
                    DB::commit();
                    return ["success" => true, "message" => "Data Certificate Project Dihapus", "status" => 200];
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return ["success" => false, "message" => "Gagal Menghapus Project Certificate", "status" => 400];
                }
            } else if ($request->achievement_id) {
                $model = $resume->achievements()->find($request->achievement_id);
                if (!$model) return ["success" => false, "message" => "Data Achievement yang dihapus tidak valid", "status" => 400];
                try {
                    DB::beginTransaction();
                    if ($model->display_order === 1) {
                        $rotation = $resume->achievements()->where('display_order', '!=', 1)->latest('display_order')->first();
                        if ($rotation) {
                            $rotation->display_order = 1;
                            $rotation->save();
                        }
                    }
                    $model->delete();
                    DB::commit();
                    return ["success" => true, "message" => "Data Achievement Project Dihapus", "status" => 200];
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return ["success" => false, "message" => "Gagal Menghapus Project Achievement", "status" => 400];
                }
            } else if ($request->summary_id) {
                $model = $resume->summaries()->find($request->summary_id);
                if (!$model) return ["success" => false, "message" => "Data Summary yang dihapus tidak valid", "status" => 400];
                if (!$model->delete()) return ["success" => false, "message" => "Gagal Menghapus Summary Resume", "status" => 400];
                return ["success" => true, "message" => "Data Resume Summary Dihapus", "status" => 200];
            }

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $resume, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    // Assessment Management
    public function getAssessments(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:name,details_count,resumes_count",
            "sort_type" => "in:asc,desc"
        ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $keyword = $request->keyword ?? NULL;

            $rows = $request->rows ?? 5;
            $assessments = ResumeAssessment::with(["details"])->withCount(["details", "resumes"]);

            // filter
            if ($keyword) $assessments = $assessments->where("name", "LIKE", "%$keyword%");

            // sort
            $sort_by = $request->sort_by ?? NULL;
            $sort_type = $request->get('sort_type', 'asc');
            if ($sort_by == "name") $assessments = $assessments->orderBy('name', $sort_type);
            if ($sort_by == "details_count") $assessments = $assessments->orderBy('details_count', $sort_type);
            if ($sort_by == "resumes_count") $assessments = $assessments->orderBy('resumes_count', $sort_type);


            $assessments = $assessments->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $assessments, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAssessment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $id = $request->id;
            $assessment = ResumeAssessment::with('details')->withCount(['resumes'])->find($id);
            if (!$assessment) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $assessment, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAssessment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "name" => "required|unique:App\ResumeAssessment",
            "add" => "required|array",
            "add.*.criteria" => "required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $assessment = new ResumeAssessment();
            $assessment->name = $request->name;
            $assessment->created_at = Date('Y-m-d H:i:s');
            $assessment->updated_at = Date('Y-m-d H:i:s');
            if (!$assessment->save()) return ["success" => false, "message" => "Gagal Menambah Assessment", "status" => 400];

            $adds = [];
            if ($request->add) {
                foreach ($request->add as $requestAdd) {
                    $requestAdd = (object)$requestAdd;
                    $add = new ResumeAssessmentDetail();
                    $add->criteria = $requestAdd->criteria;
                    $adds[] = $add;
                }
            }
            if (!$assessment->details()->saveMany($adds)) return ["success" => false, "message" => "Gagal Menambah Criteria Assessment", "status" => 400];;

            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $assessment->id, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function deleteAssessment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $id = $request->id;
            $assessment = ResumeAssessment::withCount(["resumes"])->find($id);
            if (!$assessment) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if ($assessment->resumes_count > 0) return ["success" => false, "message" => "Gagal menghapus karena beberapa kandidat masih menggunakan assessment", "status" => 400];
            if (!$assessment->delete()) return ["success" => false, "message" => "Gagal Menghapus Assessment", "status" => 400];

            return ["success" => true, "message" => "Data Berhasil Dihapus", "id" => $assessment->id, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAssessment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "name" => "required",
            "add" => "array",
            "update" => "array",
            "delete" => "array",
            "add.*.criteria" => "required",
            "update.*.id" => "numeric",
            "update.*.criteria" => "required_with:update.*.id",
            "delete.*" => "numeric",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $id = $request->id;
        $assessment = ResumeAssessment::find($id);
        if (!$assessment) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        if ($assessment->name != $request->name) {
            //check assessment name
            $checkAssessmentName = ResumeAssessment::where([
                ["name", "=", $request->name],
                ["id", "!=", $id]
            ])->count();
            if ($checkAssessmentName > 0) {
                return ["success" => false, "message" => "The name has already been taken.", "status" => 400];
            }
            $assessment->name = $request->name;
        }
        $assessmentDetails = $assessment->details();
        $assessmentDetailsId = $assessmentDetails->pluck('id')->toArray();

        $adds = [];
        if ($request->add) {
            foreach ($request->add as $requestAdd) {
                $requestAdd = (object)$requestAdd;
                $add = new ResumeAssessmentDetail();
                $add->criteria = $requestAdd->criteria;
                $adds[] = $add;
            }
        }

        $updates = [];
        if ($request->update) {
            $assessmentUpdatesId = [];

            foreach ($request->update as $requestUpdate) {
                $requestUpdate = (object)$requestUpdate;
                if (!isset($requestUpdate->id)) continue;
                $assessmentUpdatesId[] = $requestUpdate->id;
            }
            $updateDiffId = array_diff($assessmentUpdatesId, $assessmentDetailsId);
            if ($updateDiffId != []) return ["success" => false, "message" => "ID : [" . implode(", ", $updateDiffId) . "] yang akan di update bukan detail dari resume", "status" => 400];
            foreach ($request->update as $requestUpdate) {
                $requestUpdate = (object)$requestUpdate;
                if (!isset($requestUpdate->id)) continue;
                $update = ResumeAssessmentDetail::find($requestUpdate->id);
                $update->criteria = $requestUpdate->criteria;
                $updates[] = $update;
            }
        }

        $deletes = $request->delete ?? [];
        if ($deletes) {
            $deleteDiffId = array_diff($deletes, $assessmentDetailsId);
            if ($deleteDiffId != []) return ["success" => false, "message" => "ID : [" . implode(", ", $deletes) . "] yang akan di delete bukan detail dari resume", "status" => 400];
        }

        $batch = DB::transaction(function () use ($assessment, $adds, $updates, $deletes) {
            try {
                $stepMessage = [
                    "assessment" => "Terjadi error saat mengubah data assesment",
                    "adds" => "Terjadi error saat menambah data criteria",
                    "deletes" => "Terjadi error saat menghapus data criteria",
                    "updates" => "Terjadi error saat mengupdate data criteria"
                ];
                $step = "assessment";
                $assessment->updated_at = Date('Y-m-d H:i:s');
                $assessment->save();
                $step = "adds";
                $assessment->details()->saveMany($adds);
                $step = "deletes";
                $assessment->details()->whereIn("id", $deletes)->delete();
                $step = "updates";
                $assessment->details()->saveMany($updates);
                return true;
            } catch (Exception $e) {
                return ["error" => [$e, $stepMessage[$step]]];
            }
        });

        if (isset($batch['error'])) {
            return ["success" => false, "message" => $batch['error'], "status" => 400];
        }

        try {
            return ["success" => true, "message" => "Data Berhasil Diubah", "id" => $assessment->id, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateResumeAssessment(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;


        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\Resume,id",
            "assessment_result_values" => "required|array"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {

            $id = $request->id;
            $resume = Resume::with("assessmentResults")->find($id);
            if (!$resume) return ["success" => false, "message" => "Data Resume Tidak Ditemukan", "status" => 400];

            $assessmentResultsId = $resume->assessmentResults->pluck("id")->toArray();
            $values = $request->assessment_result_values;
            $valueLen = count($values);
            $criteriaLen = count($assessmentResultsId);
            if ($valueLen != $criteriaLen) return ["success" => false, "message" => "Jumlah criteria dan value tidak sesuai", "status" => 400];

            $resumeAssessmentResultsObjArr = [];
            $count = 0;

            $resumeAssessmentResults = ResumeAssessmentResult::whereIn("id", $assessmentResultsId)->get();
            foreach ($resumeAssessmentResults as $resumeAssessmentResult) {
                $resumeAssessmentResult->value = $values[$count];
                $resumeAssessmentResultsObjArr[] = $resumeAssessmentResult;
                $count++;
            }

            if (!$resume->assessmentResults()->saveMany($resumeAssessmentResultsObjArr)) return ["success" => false, "message" => "Gagal Mengubah Resume Assessment Result", "status" => 400];

            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function getCountAssessment($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        try {

            $assessments_count = ResumeAssessment::count();
            $resume_assessments_count = ResumeAssessment::select(["id", "name"])->withCount('resumes')->orderBy("resumes_count", "desc")->limit(10)->get();

            $assessments = [
                "assessments_count" => $assessments_count,
                "resume_assessments_count" => $resume_assessments_count
            ];

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $assessments, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCountResume($request, $route_name)
    {

        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        try {

            $resumes_count = Resume::count();
            $resume_assessments_count = ResumeAssessment::select(["id", "name"])->withCount('resumes')->orderBy("resumes_count", "desc")->limit(10)->get();

            $resumes = [
                "assessments_count" => $resumes_count,
                "resume_assessments_count" => $resume_assessments_count
            ];

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resumes, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAssessmentList($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        try {
            $resumeAssessments = ResumeAssessment::get(["id", "name"]);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resumeAssessments, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getSkillLists($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        try {
            $resumeSkillLists = ResumeSkillLists::limit(10);
            if ($request->name) $resumeSkillLists->where("name", "LIKE", "%$request->name%");
            $resumeSkillLists = $resumeSkillLists->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resumeSkillLists, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}
