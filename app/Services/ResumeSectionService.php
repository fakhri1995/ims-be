<?php

namespace App\Services;

use App\Resume;
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
						$resume->city = $request->city;
						$resume->province = $request->province;
						$resume->summary = $request->summary;
						$resume->linkedin = $request->linkedin;

            if (!$resume->save()) return ["success" => false, "message" => "Gagal Menambah Resume", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $resume, "status" => 200];

        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
	}
}