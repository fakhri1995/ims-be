<?php

namespace App\Http\Controllers;

use App\Services\ResumeSectionService;
use App\Services\ResumeService;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function __construct()
    {
        $this->resumeService = new ResumeService;
        $this->resumeSectionService = new ResumeSectionService;
    }

    public function getResumes(Request $request){
        $route_name = "RESUMES_GET";

        $response = $this->resumeService->getResumes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getResume(Request $request){
        $route_name = "RESUME_GET";

        $response = $this->resumeService->getResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addResume(Request $request){
        $route_name = "RESUME_ADD";

        $response = $this->resumeService->addResume($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function addResumeSection(Request $request){
        $route_name = "RESUME_SECTION_ADD";

        $response = $this->resumeService->addResumeSection($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateResume(Request $request){
        $route_name = "RESUME_UPDATE";

        $response = $this->resumeService->updateResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteResume(Request $request){
        $route_name = "RESUME_DELETE";

        $response = $this->resumeService->deleteResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteResumeSection(Request $request){
        $route_name = "RESUME_SECTION_DELETE";

        $response = $this->resumeService->deleteResumeSection($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getResumeAssessment(Request $request){
        $route_name = "RESUMES_GET";

        $response = $this->resumeService->getResumes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCountResume(Request $request)
    {
        $route_name = "RESUME_COUNT_GET";

        $response = $this->resumeService->getCountResume($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Assessment Management
    public function getAssessment(Request $request){
        $route_name = "ASSESSMENT_GET";

        $response = $this->resumeService->getAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAssessments(Request $request){
        $route_name = "ASSESSMENTS_GET";

        $response = $this->resumeService->getAssessments($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAssessment(Request $request){
        $route_name = "ASSESSMENT_ADD";

        $response = $this->resumeService->addAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAssessment(Request $request){
        $route_name = "ASSESSMENT_UPDATE";

        $response = $this->resumeService->updateAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAssessment(Request $request){
        $route_name = "ASSESSMENT_DELETE";

        $response = $this->resumeService->deleteAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addResumeAssessment(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_ADD";

        $response = $this->resumeService->addResumeAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteResumeAssessment(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_DELETE";

        $response = $this->resumeService->deleteResumeAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCountAssessment(Request $request)
    {
        $route_name = "ASSESSMENT_COUNT_GET";

        $response = $this->resumeService->getCountAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateResumeAssessment(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_UPDATE";

        $response = $this->resumeService->updateResumeAssessment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAssessmentList(Request $request)
    {
        $route_name = "RESUME_ASSESSMENT_LIST";

        $response = $this->resumeService->getAssessmentList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getSkillLists(Request $request)
    {
        $route_name = "RESUME_SKILL_LISTS";

        $response = $this->resumeService->getSkillLists($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function addResumeEvaluation(Request $request)
    {
        $route_name = "RESUME_EVALUATION_ADD";

        $response = $this->resumeService->addResumeEvaluation($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateResumeEvaluation(Request $request)
    {
        $route_name = "RESUME_EVALUATION_UPDATE";

        $response = $this->resumeService->updateResumeEvaluation($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteResumeEvaluation(Request $request)
    {
        $route_name = "RESUME_EVALUATION_DELETE";

        $response = $this->resumeService->deleteResumeEvaluation($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateResumePersonalInfo(Request $request)
    {
        $route_name = "RESUME_PERSONAL_INFO_UPDATE";

        $response = $this->resumeSectionService->updateResumePersonalInfo($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
