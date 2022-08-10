<?php

namespace App\Http\Controllers;

use App\Services\RecruitmentService;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        $this->recruitmentService = new RecruitmentService;
    }

    public function getResumes(Request $request){
        $route_name = "RESUMES_GET";

        $response = $this->recruitmentService->getResumes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getResume(Request $request){
        $route_name = "RESUME_GET";

        $response = $this->recruitmentService->getResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addResume(Request $request){
        $route_name = "RESUME_ADD";

        $response = $this->recruitmentService->addResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateResume(Request $request){
        $route_name = "RESUME_UPDATE";

        $response = $this->recruitmentService->updateResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteResume(Request $request){
        $route_name = "RESUME_DELETE";

        $response = $this->recruitmentService->deleteResume($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
