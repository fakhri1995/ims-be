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
    
    public function getCountRecruitment(Request $request)
    {
        $route_name = "RECRUITMENT_COUNT_GET";

        $response = $this->recruitmentService->getCountRecruitment($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitment_status(Request $request)
    {
        $route_name = "RECRUITMENT_UPDATE_STATUS";

        $response = $this->recruitmentService->updateRecruitment_status($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitment_stage(Request $request)
    {
        $route_name = "RECRUITMENT_UPDATE_STAGE";

        $response = $this->recruitmentService->updateRecruitment_stage($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitmentLogNotes(Request $request)
    {
        $route_name = "RECRUITMENT_LOG_NOTES_ADD";

        $response = $this->recruitmentService->addRecruitmentLogNotes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitments_status(Request $request)
    {
        $route_name = "RECRUITMENTS_UPDATE_STATUS";

        $response = $this->recruitmentService->updateRecruitments_status($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitments_stage(Request $request)
    {
        $route_name = "RECRUITMENTS_UPDATE_STAGE";

        $response = $this->recruitmentService->updateRecruitments_stage($request, $route_name);
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

    public function getRecruitmentRoleTypesList(Request $request)
    {
        $route_name = "RECRUITMENT_ROLE_TYPES_LIST_GET";

        $response = $this->recruitmentService->getRecruitmentRoleTypesList($request, $route_name);
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

    //RECRUITMENT STAGE SECTION

    public function getRecruitmentStage(Request $request)
    {
        $route_name = "RECRUITMENT_STAGE_GET";

        $response = $this->recruitmentService->getRecruitmentStage($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getRecruitmentStages(Request $request)
    {
        $route_name = "RECRUITMENT_STAGES_GET";

        $response = $this->recruitmentService->getRecruitmentStages($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRecruitmentStagesList(Request $request)
    {
        $route_name = "RECRUITMENT_STAGES_LIST_GET";

        $response = $this->recruitmentService->getRecruitmentStagesList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitmentStage(Request $request)
    {
        $route_name = "RECRUITMENT_STAGE_ADD";

        $response = $this->recruitmentService->addRecruitmentStage($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitmentStage(Request $request)
    {
        $route_name = "RECRUITMENT_STAGE_UPDATE";

        $response = $this->recruitmentService->updateRecruitmentStage($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRecruitmentStage(Request $request)
    {
        $route_name = "RECRUITMENT_STAGE_DELETE";

        $response = $this->recruitmentService->deleteRecruitmentStage($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //END OF RECRUITMENT STAGE SECTION

    //RECRUITMENT JALUR_DAFTAR SECTION

    public function getRecruitmentJalurDaftar(Request $request)
    {
        $route_name = "RECRUITMENT_JALUR_DAFTAR_GET";

        $response = $this->recruitmentService->getRecruitmentJalurDaftar($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getRecruitmentJalurDaftars(Request $request)
    {
        $route_name = "RECRUITMENT_JALUR_DAFTARS_GET";

        $response = $this->recruitmentService->getRecruitmentJalurDaftars($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRecruitmentJalurDaftarsList(Request $request)
    {
        $route_name = "RECRUITMENT_JALUR_DAFTARS_LIST_GET";

        $response = $this->recruitmentService->getRecruitmentJalurDaftarsList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitmentJalurDaftar(Request $request)
    {
        $route_name = "RECRUITMENT_JALUR_DAFTAR_ADD";

        $response = $this->recruitmentService->addRecruitmentJalurDaftar($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitmentJalurDaftar(Request $request)
    {
        $route_name = "RECRUITMENT_JALUR_DAFTAR_UPDATE";

        $response = $this->recruitmentService->updateRecruitmentJalurDaftar($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRecruitmentJalurDaftar(Request $request)
    {
        $route_name = "RECRUITMENT_JALUR_DAFTAR_DELETE";

        $response = $this->recruitmentService->deleteRecruitmentJalurDaftar($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //END OF RECRUITMENT JALUR_DAFTAR SECTION

    //RECRUITMENT EMAIL TEMPLATE SECTION

    public function getRecruitmentEmailTemplate(Request $request)
    {
        $route_name = "RECRUITMENT_EMAIL_TEMPLATE_GET";

        $response = $this->recruitmentService->getRecruitmentEmailTemplate($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRecruitmentEmailTemplates(Request $request)
    {
        $route_name = "RECRUITMENT_EMAIL_TEMPLATES_GET";

        $response = $this->recruitmentService->getRecruitmentEmailTemplates($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRecruitmentEmailTemplatesList(Request $request)
    {
        $route_name = "RECRUITMENT_EMAIL_TEMPLATES_LIST_GET";

        $response = $this->recruitmentService->getRecruitmentEmailTemplatesList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRecruitmentEmailTemplate(Request $request)
    {
        $route_name = "RECRUITMENT_EMAIL_TEMPLATE_ADD";

        $response = $this->recruitmentService->addRecruitmentEmailTemplate($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRecruitmentEmailTemplate(Request $request)
    {
        $route_name = "RECRUITMENT_EMAIL_TEMPLATE_UPDATE";

        $response = $this->recruitmentService->updateRecruitmentEmailTemplate($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRecruitmentEmailTemplate(Request $request)
    {
        $route_name = "RECRUITMENT_EMAIL_TEMPLATE_DELETE";

        $response = $this->recruitmentService->deleteRecruitmentEmailTemplate($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //END OF RECRUITMENT EMAIL TEMPLATE SECTION
}



