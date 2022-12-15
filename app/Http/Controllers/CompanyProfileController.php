<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyProfileService;

class CompanyProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->companyProfileService = new CompanyProfileService;
    }

    // Message
    public function getMessages(Request $request)
    {
        $route_name = "MESSAGES_GET";

        $response = $this->companyProfileService->getMessages($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addMessage(Request $request)
    {
        $route_name = "MESSAGE_GET";

        $response = $this->companyProfileService->addMessage($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteMessage(Request $request)
    {
        $route_name = "MESSAGE_DELETE";

        $response = $this->companyProfileService->deleteMessage($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Career
    public function getCareers(Request $request)
    {
        $route_name = "CAREERS_GET";

        $response = $this->companyProfileService->getCareers($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addCareer(Request $request)
    {
        $route_name = "CAREER_ADD";

        $response = $this->companyProfileService->addCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateCareer(Request $request)
    {
        $route_name = "CAREER_UPDATE";

        $response = $this->companyProfileService->updateCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteCareer(Request $request)
    {
        $route_name = "CAREER_DELETE";

        $response = $this->companyProfileService->deleteCareer($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addFormSolution(Request $request)
    {
        $route_name = "FORM_POST";

        $response = $this->companyProfileService->addFormSolution($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function addFormSolutionTalent(Request $request)
    {
        $route_name = "FORM_POST";

        $response = $this->companyProfileService->addFormSolutionTalents($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function addFormSolutionHardware(Request $request)
    {
        $route_name = "FORM_POST";

        $response = $this->companyProfileService->addFormSolutionHardware($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function getFormSolution(Request $request)
    {
        $route_name = "FORM_GET";

        $response = $this->companyProfileService->getFormSolution($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function getFormSolutionDetail(Request $request)
    {
        $route_name = "FORM_GET";

        $response = $this->companyProfileService->getFormSolutionDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addArticle(Request $request)
    {
        $route_name = "ARTICLE_POST";

        $response = $this->companyProfileService->addArticle($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function getArticle(Request $request)
    {
        $route_name = "FORM_GET";

        $response = $this->companyProfileService->getArticle($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function deleteArticle(Request $request)
    {
        $route_name = "FORM_GET";

        $response = $this->companyProfileService->deleteArticle($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function getArticleDetail(Request $request)
    {
        $route_name = "FORM_GET";

        $response = $this->companyProfileService->getArticleDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }
    public function updateArticle(Request $request)
    {
        $route_name = "FORM_GET";

        $response = $this->companyProfileService->updateArticle($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
}