<?php 

namespace App\Services;

use App\ActivityLogRecruitment;
use App\Exports\RecruitmentExportTemplate;
use App\Mail\RecruitmentMail;
use App\Recruitment;
use App\RecruitmentAccountRoleTemplate;
use App\RecruitmentEmailTemplate;
use App\RecruitmentJalurDaftar;
use App\RecruitmentRole;
use App\RecruitmentRoleType;
use App\RecruitmentStage;
use App\RecruitmentStatus;
use App\Resume;
use App\ResumeAchievement;
use App\ResumeCertificate;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeProject;
use App\ResumeSkill;
use App\ResumeSummary;
use App\ResumeTraining;
use Exception;
use App\Services\GlobalService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;

class RecruitmentAIService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

		public function uploadCVsTemp($request, $route_name){
			try{
				$client = new Client();
				$resumes = $client->request('POST',
				'http://103.175.216.222:10001/api/v1/uploads',
				[
					'multipart' => [
						[
							'name' => 'files',
							'contents' => $request->file('files')
						]
					]
				]
				);
	
				$response = $resumes->getBody();
				$files = $response->files;
				foreach($files as $file){
					$data = $file->parsed_data;
					$resume = new Resume;
					$resume->name = $data->name;
					$resume->telp = $data->phone;
					$resume->email = $data->email;
					$resume->city = $data->location;
					$resume->province = $data->location;
					$resume->created_at = Date('Y-m-d H:i:s');
					$resume->updated_at = Date('Y-m-d H:i:s');
					$resume->created_by = auth()->user()->id;
					if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];

					$details = $this->fillResumeDetails($data, $resume);
					

				}
				return ["success" => true, "message" => "Data Berhasil diupload", "data" => $response, "status" => 200];
			}catch(Exception $err){
				return ["success" => false, "message" => $request->file('files'), "status" => 400];
			}
		}

		private function fillResumeDetails($data, $resume){
			if ($data->education) {
				try {
						DB::beginTransaction();
						$requestEducation = (object)$data->education;

						$after_id = $requestEducation->after_id ?? NULL;
						if ($after_id != NULL) {
								$educationAfter = ResumeEducation::find($after_id);
								if (!$educationAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
						}

						$education = new ResumeEducation();
						$education->university = $requestEducation->university;
						$education->major = $requestEducation->major;
						$education->gpa = !$requestEducation->gpa ? NULL : $requestEducation->gpa;
						$education->start_date = $requestEducation->start_date ? date('Y-m-01', strtotime($requestEducation->start_date)) : null;
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
		} else if ($data->experience) {
				try {
						DB::beginTransaction();
						$requestExperience = (object)$data->experience;

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
		} else if ($data->project) {
				try {
						DB::beginTransaction();
						$requestProject = (object)$data->project;

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
		} else if ($data->skill) {
				$requestSkill = (object)$data->skill;
				$skill = new ResumeSkill();
				$skill->name = ucfirst($requestSkill->name);
				DB::table('resume_skill_lists')->insertOrIgnore([['name' => $skill->name]]);
				if (!$resume->skills()->save($skill)) return ["success" => false, "message" => "Gagal Mengubah Skill Resume", "status" => 400];
				return ["success" => true, "message" => "Data Skill Berhasil Ditambah", "status" => 200];
		} else if ($data->training) {
				try {
						DB::beginTransaction();
						$requestTraining = (object)$data->training;

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
		} else if ($data->certificate) {
				try {
						DB::beginTransaction();
						$requestCertificates = (object)$data->certificate;

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
		} else if ($data->achievement) {
				try {
						DB::beginTransaction();
						$requestAchievement = (object)$data->achievement;

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
		} else if ($data->summary) {
				$requestSummary = (object)$data->summary;
				$summary = new ResumeSummary();
				$summary->description = $requestSummary->description;
				if (!$resume->summaries()->save($summary)) return ["success" => false, "message" => "Gagal Mengubah Summary Resume", "status" => 400];
				return ["success" => true, "message" => "Data Summary Berhasil Ditambah", "status" => 200];
		}
		}

		public function uploadCVs($route_name, $request){
			$access = $this->globalService->checkRoute($route_name);
			if ($access["success"] === false) return $access;
			
			$resume = new Resume;
			$resume->name = 'Lorem Ipsum';
			$resume->telp = '123456789';
			$resume->email = 'lorem@ipsum.com';
			$resume->city = 'Jakarta Selatan';
			$resume->province = 'DKI Jakarta';
			$resume->created_at = Date('Y-m-d H:i:s');
			$resume->updated_at = Date('Y-m-d H:i:s');
			$resume->created_by = auth()->user()->id;
			if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];

			$education = new ResumeEducation;
			$education->resume_id = $resume->id;
			$education->university = 'Lorem University';
			$education->major = 'Psikologi';
			$education->gpa = 4;
			$education->start_date = Date('Y-m-d');
			$education->end_date = Date('Y-m-d');
			$education->graduation_year = Date('Y-m-d');
			$education->display_order = 1;
			$education->save();

			$batch = DB::table('resumes')->orderBy('cv_processing_batch', 'desc')->first() + 1;
			$recruitment = new Recruitment;
			$recruitment->owner_id = $resume->id;
			$recruitment->name = 'Lorem Ipsum';
			$recruitment->email = 'lorem@ipsum.com';
			$recruitment->university = 'Lorem University';
			$recruitment->recruitment_role_id = 0;
			$recruitment->recruitment_jalur_daftar_id = 1;
			$recruitment->recruitment_stage_id = 1;
			$recruitment->cv_processing_status = 1;
			$recruitment->cv_processing_batch = $batch;
			$recruitment->lampiran = [];
			$recruitment->created_at = Date('Y-m-d H:i:s');
			$recruitment->updated_at = Date('Y-m-d H:i:s');
			$recruitment->created_by = auth()->user()->id;

			$recruitment->save();

			return ["success" => true, "message" => "Data Recruitment Berhasil Ditambah", "data" => [$recruitment], "status" => 200];
		}

		public function approveRecruitment($request, $route_name){
			$access = $this->globalService->checkRoute($route_name);
			if ($access["success"] === false) return $access;

			try{
				$id = $request->id;
				$approve = $request->approve;
	
				$recruitment = Recruitment::find($id);
				if(!$recruitment){
					return ["success" => false, "message" => "Data Recruitment tidak ditemukan", "status" => 400];
				}
				if(!$recruitment->cv_processing_status){
					return ["success" => false, "message" => "Recruitment tidak perlu di Approve", "status" => 400];
				}
				if($approve){
					$recruitment->cv_processing_status = 2;
				} else $recruitment->cv_processing_status = 3;
				return ["success" => true, "message" => "Recruitment berhasil diedit", "data" => $recruitment, "status" => 200];
			}catch(Exception $err){
				return ["success" => false, "message" => $err->getLine(), "status" => 400];
			}
		}
		
		public function getRecruitmentsAI($request, $route_name){
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
				$cv_processing_status = $request->cv_processing_status ? explode(",",$request->cv_processing_status) : NULL;

        $rows = $request->rows ?? 5;
        $recruitments = Recruitment::with(['role','role.type','jalur_daftar','stage','status','resume','user'])->where("cv_processing_status", '!=', 0);

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
        if($sort_by == "role") $recruitments = $recruitments->orderBy(RecruitmentRole::select("role")
                ->whereColumn("recruitment_roles.id","recruitments.recruitment_role_id"),$sort_type);
        if($sort_by == "jalur_daftar") $recruitments = $recruitments->orderBy(RecruitmentJalurDaftar::select("name")
                ->whereColumn("recruitment_jalur_daftars.id","recruitments.recruitment_jalur_daftar_id"),$sort_type);
        if($sort_by == "stage") $recruitments = $recruitments->orderBy(RecruitmentStage::select("name")
                ->whereColumn("recruitment_stages.id","recruitments.recruitment_stage_id"),$sort_type);
        if($sort_by == "status") $recruitments = $recruitments->orderBy(RecruitmentStatus::select("name")
                ->whereColumn("recruitment_statuses.id","recruitments.recruitment_status_id"),$sort_type);

        $recruitments = $recruitments->groupBy('cv_processing_batch')->paginate($rows);
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitments, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
		}

		public function getPendingRecruitmentsAI($request, $route_name){
			$access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $recruitments = Recruitment::with(['resume', 'resume.skills'])->where("cv_processing_status", 1);
				$recruitments = $recruitments->orderBy('id','desc');
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $recruitments->get(), "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
		}
}