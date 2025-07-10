<?php

namespace App\Services;

use App\Resume;
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

	public function updateResumeSkill(Request $request, $route_name){
		$access = $this->globalService->checkRoute($route_name);
		if ($access["success"] === false) return $access;

		try{
			$skill = ResumeSkill::find($request->id);
			
			if(!$skill){
				return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
			}

			$skill->name = ucfirst($request->skill_name);
			
			if (!$skill->save()) return ["success" => false, "message" => "Gagal Menambah Resume Skill", "status" => 400];
			return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $skill, "status" => 200];
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
}