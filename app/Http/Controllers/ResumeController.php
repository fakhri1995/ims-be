<?php

namespace App\Http\Controllers;

use App\Services\ResumeService;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function __construct()
    {
        $this->recruitmentService = new ResumeService;
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
    
    public function addResumeSection(Request $request){
        $route_name = "RESUME_SECTION_ADD";

        $response = $this->recruitmentService->addResumeSection($request, $route_name);
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

    public function deleteResumeSection(Request $request){
        $route_name = "RESUME_SECTION_DELETE";

        $response = $this->recruitmentService->deleteResumeSection($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getResumeAssessment(Request $request){
        $route_name = "RESUMES_GET";

        $response = $this->recruitmentService->getResumes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCountResume(Request $request)
    {
        $route_name = "RESUME_COUNT_GET";

        $response = $this->recruitmentService->getCountResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Assessment Management
    public function getAssessment(Request $request){
        $route_name = "ASSESSMENT_GET";

        $response = $this->recruitmentService->getAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAssessments(Request $request){
        $route_name = "ASSESSMENTS_GET";

        $response = $this->recruitmentService->getAssessments($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAssessment(Request $request){
        $route_name = "ASSESSMENT_ADD";

        $response = $this->recruitmentService->addAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAssessment(Request $request){
        $route_name = "ASSESSMENT_UPDATE";

        $response = $this->recruitmentService->updateAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAssessment(Request $request){
        $route_name = "ASSESSMENT_DELETE";

        $response = $this->recruitmentService->deleteAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addResumeAssessment(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_ADD";

        $response = $this->recruitmentService->addResumeAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteResumeAssessment(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_DELETE";

        $response = $this->recruitmentService->deleteResumeAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCountAssessment(Request $request)
    {
        $route_name = "ASSESSMENT_COUNT_GET";

        $response = $this->recruitmentService->getCountAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateResumeAssessment(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_UPDATE";

        $response = $this->recruitmentService->updateResumeAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAssessmentList(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_LIST";

        $response = $this->recruitmentService->getAssessmentList($request, $route_name);
        return response()->json($response, $response['status']);
    }
    

}
