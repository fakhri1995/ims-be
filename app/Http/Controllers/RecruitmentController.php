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

    //RECRUITMENT SECTION
    public function getRecruitment(Request $request)
    {
        $route_name = "RECRUITMENT_GET";

        $response = $this->recruitmentService->getRecruitment($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getRecruitments(Request $request)
    {
        $route_name = "RECRUITMENTS_GET";

        $response = $this->recruitmentService->getRecruitments($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitment(Request $request)
    {
        $route_name = "RECRUITMENT_ADD";

        $response = $this->recruitmentService->addRecruitment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitment(Request $request)
    {
        $route_name = "RECRUITMENT_UPDATE";

        $response = $this->recruitmentService->updateRecruitment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRecruitment(Request $request)
    {
        $route_name = "RECRUITMENT_DELETE";

        $response = $this->recruitmentService->deleteRecruitment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //END OF RECRUITMENT SECTION
    //RECRUITMENT ROLE SECTION

    public function getRecruitmentRole(Request $request)
    {
        $route_name = "RECRUITMENT_ROLE_GET";

        $response = $this->recruitmentService->getRecruitmentRole($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getRecruitmentRoles(Request $request)
    {
        $route_name = "RECRUITMENT_ROLES_GET";

        $response = $this->recruitmentService->getRecruitmentRoles($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRecruitmentRolesList(Request $request)
    {
        $route_name = "RECRUITMENT_ROLES_LIST_GET";

        $response = $this->recruitmentService->getRecruitmentRolesList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitmentRole(Request $request)
    {
        $route_name = "RECRUITMENT_ROLE_ADD";

        $response = $this->recruitmentService->addRecruitmentRole($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitmentRole(Request $request)
    {
        $route_name = "RECRUITMENT_ROLE_UPDATE";

        $response = $this->recruitmentService->updateRecruitmentRole($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRecruitmentRole(Request $request)
    {
        $route_name = "RECRUITMENT_ROLE_DELETE";

        $response = $this->recruitmentService->deleteRecruitmentRole($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //END OF RECRUITMENT ROLE SECTION


    //RECRUITMENT STATUS SECTION

    public function getRecruitmentStatus(Request $request)
    {
        $route_name = "RECRUITMENT_STATUS_GET";

        $response = $this->recruitmentService->getRecruitmentStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getRecruitmentStatuses(Request $request)
    {
        $route_name = "RECRUITMENT_STATUSES_GET";

        $response = $this->recruitmentService->getRecruitmentStatuses($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRecruitmentStatusesList(Request $request)
    {
        $route_name = "RECRUITMENT_STATUSES_LIST_GET";

        $response = $this->recruitmentService->getRecruitmentStatusesList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitmentStatus(Request $request)
    {
        $route_name = "RECRUITMENT_STATUS_ADD";

        $response = $this->recruitmentService->addRecruitmentStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitmentStatus(Request $request)
    {
        $route_name = "RECRUITMENT_STATUS_UPDATE";

        $response = $this->recruitmentService->updateRecruitmentStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRecruitmentStatus(Request $request)
    {
        $route_name = "RECRUITMENT_STATUS_DELETE";

        $response = $this->recruitmentService->deleteRecruitmentStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //END OF RECRUITMENT STATUS SECTION
}



