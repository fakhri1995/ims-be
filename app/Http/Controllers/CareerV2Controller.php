<?php

namespace App\Http\Controllers;

use App\Services\CareerV2ApplyService;
use App\Services\CareerV2Service;
use Illuminate\Http\Request;

class CareerV2Controller extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->careerV2Service = new CareerV2Service;
        $this->careerV2ApplyService = new CareerV2ApplyService;
    }


    // Career Section
    public function getCareer(Request $request){
        $route_name = "CAREER_V2_GET";

        $response = $this->careerV2Service->getCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCareers(Request $request){
        $route_name = "CAREERS_V2_GET";

        $response = $this->careerV2Service->getCareers($request,$route_name);
        return response()->json($response, $response['status']);
    }

    public function getTopFiveCareers(Request $request){
        $route_name = "CAREERS_V2_TOP_FIVE_GET";

        $response = $this->careerV2Service->getTopFiveCareers($request,$route_name);
        return response()->json($response, $response['status']);
    }

    public function getPostedCareer(Request $request){
        $route_name = "CAREER_V2_POSTED_GET";

        $response = $this->careerV2Service->getPostedCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getPostedCareers(Request $request){
        $route_name = "CAREERS_V2_POSTED_GET";

        $response = $this->careerV2Service->getPostedCareers($request,$route_name);
        return response()->json($response, $response['status']);
    }

    public function addCareer(Request $request){
        $route_name = "CAREER_V2_ADD";
        
        $response = $this->careerV2Service->addCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateCareer(Request $request){
        $route_name = "CAREER_V2_UPDATE";

        $response = $this->careerV2Service->updateCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteCareer(Request $request){
        $route_name = "CAREER_V2_DELETE";

        $response = $this->careerV2Service->deleteCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }


    // Career Apply Section
    public function getCareerApply(Request $request){
        $route_name = "CAREER_V2_APPLY_GET";

        $response = $this->careerV2ApplyService->getCareerApply($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCareerApplys(Request $request){
        $route_name = "CAREERS_V2_APPLY_GET";

        $response = $this->careerV2ApplyService->getCareerApplys($request,$route_name);
        return response()->json($response, $response['status']);
    }

    public function addCareerApply(Request $request){
        $route_name = "CAREER_V2_APPLY_ADD";
        
        $response = $this->careerV2ApplyService->addCareerApply($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateCareerApply(Request $request){
        $route_name = "CAREER_V2_APPLY_UPDATE";

        $response = $this->careerV2ApplyService->updateCareerApply($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteCareerApply(Request $request){
        $route_name = "CAREER_V2_APPLY_DELETE";

        $response = $this->careerV2ApplyService->deleteCareerApply($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCountCareerApplicant(Request $request){
        $route_name = "CAREER_V2_COUNT_APPLICANT_GET";

        $response = $this->careerV2ApplyService->getCountCareerApplicant($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCountCareersApplicant(Request $request){
        $route_name = "CAREERS_V2_COUNT_APPLICANT_GET";
        $response = $this->careerV2ApplyService->getCountCareerApplicant($request, $route_name, true);
        return response()->json($response, $response['status']);
    }

    public function getCountCareerPosted(Request $request){
        $route_name = "CAREER_V2_COUNT_CAREER_POSTED_GET";

        $response = $this->careerV2Service->getCountCareerPosted($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getMostCareersApplicant(Request $request){
        $route_name = "CAREERS_V2_MOST_APPLICANT_GET";

        $response = $this->careerV2ApplyService->getMostCareersApplicant($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function exportCareersApplicant(Request $request)
    {
        $route_name = "CAREERS_V2_APPLICANT_EXPORT";

        $response = $this->careerV2ApplyService->exportCareersApplicant($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return ($response['data']);
    }

    public function getCareerApplyStatuses(Request $request){
        $route_name = "CAREERS_V2_APPLY_STATUSES";

        $response = $this->careerV2Service->getCareerApplyStatuses($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return ($response['data']);
    }

    public function getCareerExperiences(Request $request){
        $route_name = "CAREERS_V2_EXPERIENCES";

        $response = $this->careerV2Service->getCareerExperiences($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return ($response['data']);
    }

    public function getCareerRoleTypes(Request $request){
        $route_name = "CAREERS_V2_ROLE_TYPES";

        $response = $this->careerV2Service->getCareerRoleTypes($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return ($response['data']);
    }

    public function recaptcha(Request $request)
    {
        $route_name = "RECAPTCHA";

        $response = $this->careerV2ApplyService->reCaptcha($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return ($response['data']);
    }
}
