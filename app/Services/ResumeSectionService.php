<?php

namespace App\Services;

use App\Resume;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeSkill;
use Exception;
use Illuminate\Http\Request;

class ResumeSectionService
{
	protected $globalService;

	public function __construct()
	{
		$this->globalService = new GlobalService;
	}

	public function updateResumePersonalInfo(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

        try {
            $resume_id = $request->resume_id;

            $resume = Resume::find($resume_id);
            if(!$resume){
                return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            }

            $resume->name = $request->name;
						$resume->telp = $request->telp;
						$resume->email = $request->email;
						$resume->location = $request->location;
						$resume->summary = $request->summary;
						$resume->linkedin = $request->linkedin;

            if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $resume, "status" => 200];

        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
	}

	public function addResumeSkill(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$resume = Resume::find($request->resume_id);
			
			if(!$resume){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			$skill = new ResumeSkill();
			$skill->name = ucfirst($request->skill_name);
			$resume->skills()->save($skill);

			return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $skill, "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function deleteResumeSkill(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$skill = ResumeSkill::find($request->id);
			
			if(!$skill){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			if (!$skill->delete()) return ["success" => false, "message" => "Gagal Delete Resume Skill", "status" => 400];
			return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function addResumeEducation(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$resume = Resume::find($request->resume_id);
			
			if(!$resume){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			$after_id = $request->after_id ?? NULL;
			if ($after_id != NULL) {
				$educationAfter = ResumeEducation::find($after_id);
				if (!$educationAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
			}

			$education = new ResumeEducation();
			$education->university = $request->university;
			$education->major = $request->major;
			$education->gpa = !$request->gpa ? NULL : $request->gpa;
			$education->degree = !$request->degree ? NULL : $request->degree;
			$education->start_date = $request->start_date ? date('Y-m-01', strtotime($request->start_date)) : null;
			$education->end_date = $request->end_date ? date('Y-m-t', strtotime($request->end_date . '-01')) : null;
			$education->location = $request->location;
			$education->honors = $request->honors;
			$education->relevant_coursework = $request->relevant_coursework;

			$educations = new ResumeEducation();
			if ($after_id == NULL) {
				$educations->increment("display_order");
				$education->display_order = 1;
			} else {
				$educations->where("display_order", ">", $educationAfter->display_order)->increment("display_order");
				$education->display_order = $educationAfter->display_order + 1;
			}

			$resume->educations()->save($education);

			return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $education, "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function updateResumeEducation(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		
		try{
			$education = ResumeEducation::find($request->id);
			
			if(!$education){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			$education->university = $request->university;
			$education->major = $request->major;
			$education->gpa = !$request->gpa ? NULL : $request->gpa;
			$education->degree = !$request->degree ? NULL : $request->degree;
			$education->start_date = $request->start_date ? date('Y-m-01', strtotime($request->start_date)) : null;
			$education->end_date = $request->end_date ? date('Y-m-t', strtotime($request->end_date . '-01')) : null;
			$education->location = $request->location;
			$education->honors = $request->honors;
			$education->relevant_coursework = $request->relevant_coursework;

			
			if (!$education->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];
			return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $education, "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function deleteResumeEducation(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$education = ResumeEducation::find($request->id);
			
			if(!$education){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			if (!$education->delete()) return ["success" => false, "message" => "Gagal Delete Resume Education", "status" => 400];
			return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function addResumeExperience(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$resume = Resume::find($request->resume_id);
			
			if(!$resume){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			$after_id = $request->after_id ?? NULL;
			if ($after_id != NULL) {
				$experienceAfter = ResumeExperience::find($after_id);
				if (!$experienceAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
			}

			$experience = new ResumeExperience();
			$experience->role = $request->role;
			$experience->company = $request->company;
			$experience->start_date = $request->start_date;
			$experience->end_date = $request->end_date;
			$experience->description = $request->responsibilities;
			$experience->achievements = $request->achievements;
			$experience->technologies = $request->technologies;
			$experience->location = $request->location;
			$experience->industry = $request->industry;

			$experiences = new ResumeExperience();
			if ($after_id == NULL) {
				$experiences->increment("display_order");
				$experience->display_order = 1;
			} else {
				$experiences->where("display_order", ">", $experienceAfter->display_order)->increment("display_order");
				$experience->display_order = $experienceAfter->display_order + 1;
			}

			$resume->educations()->save($experience);

			return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $experience, "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function updateResumeExperience(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		
		try{
			$experience = ResumeExperience::find($request->id);
			
			if(!$experience){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			$experience->role = $request->role;
			$experience->company = $request->company;
			$experience->start_date = $request->start_date;
			$experience->end_date = $request->end_date;
			$experience->description = $request->responsibilities;
			$experience->achievements = $request->achievements;
			$experience->technologies = $request->technologies;
			$experience->location = $request->location;
			$experience->industry = $request->industry;
			
			if (!$experience->save()) return ["success" => false, "message" => "Gagal Menambah Resume Experience", "status" => 400];
			return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $experience, "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}

	public function deleteResumeExperience(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$experience = ResumeExperience::find($request->id);
			
			if(!$experience){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			if (!$experience->delete()) return ["success" => false, "message" => "Gagal Delete Resume Experience", "status" => 400];
			return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
		} catch (Exception $err) {
			return ["success" => false, "message" => $err, "status" => 400];
		}
	}
}