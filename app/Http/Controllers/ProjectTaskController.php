<?php

namespace App\Http\Controllers;

use App\Services\ProjectTaskService;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function __construct()
    {
        $this->projectTaskService = new ProjectTaskService;
    }

    //PROJECT SECTION
    public function addProject(Request $request)
    {
        $route_name = "PROJECT_ADD";

        $response = $this->projectTaskService->addProject($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProject(Request $request)
    {
        $route_name = "PROJECT_GET";

        $response = $this->projectTaskService->getProject($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjects(Request $request)
    {
        $route_name = "PROJECTS_GET";

        $response = $this->projectTaskService->getProjects($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientProjects(Request $request)
    {
        $route_name = "CLIENT_PROJECTS_GET";

        $response = $this->projectTaskService->getClientProjects($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectsList(Request $request)
    {
        $route_name = "PROJECTS_LIST_GET";

        $response = $this->projectTaskService->getProjectsList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProject(Request $request)
    {
        $route_name = "PROJECT_UPDATE";

        $response = $this->projectTaskService->updateProject($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProject(Request $request)
    {
        $route_name = "PROJECT_DELETE";

        $response = $this->projectTaskService->deleteProject($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProject_status(Request $request)
    {
        $route_name = "PROJECT_UPDATE_STATUS";

        $response = $this->projectTaskService->updateProject_status($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectsCount(Request $request)
    {
        $route_name = "PROJECT_COUNT_GET";

        $response = $this->projectTaskService->getProjectsCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientProjectsCount(Request $request)
    {
        $route_name = "PROJECT_COUNT_CLIENT_GET";

        $response = $this->projectTaskService->getClientProjectsCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectsDeadline(Request $request)
    {
        $route_name = "PROJECT_DEADLINE_GET";

        $response = $this->projectTaskService->getProjectsDeadline($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientProjectsDeadline(Request $request)
    {
        $route_name = "PROJECTS_DEADLINE_CLIENT_GET";

        $response = $this->projectTaskService->getClientProjectsDeadline($request, $route_name);
        return response()->json($response, $response['status']);
    }

    
    //TASK SECTION
    public function addProjectTask(Request $request)
    {
        $route_name = "PROJECT_TASK_ADD";

        $response = $this->projectTaskService->addProjectTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectTask(Request $request)
    {
        $route_name = "PROJECT_TASK_GET";

        $response = $this->projectTaskService->getProjectTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectTasks(Request $request)
    {
        $route_name = "PROJECT_TASKS_GET";

        $response = $this->projectTaskService->getProjectTasks($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProjectTask(Request $request)
    {
        $route_name = "PROJECT_TASK_UPDATE";

        $response = $this->projectTaskService->updateProjectTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProjectTask(Request $request)
    {
        $route_name = "PROJECT_TASK_DELETE";

        $response = $this->projectTaskService->deleteProjectTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProjectTask_status(Request $request)
    {
        $route_name = "PROJECT_TASK_UPDATE_STATUS";

        $response = $this->projectTaskService->updateProjectTask_status($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectTasksCount(Request $request)
    {
        $route_name = "PROJECT_TASKS_COUNT_GET";

        $response = $this->projectTaskService->getProjectTasksCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectTasksDeadline(Request $request)
    {
        $route_name = "PROJECT_TASKS_DEADLINE_GET";

        $response = $this->projectTaskService->getProjectTasksDeadline($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getProjectTasksAdmin(Request $request)
    {
        $route_name = "PROJECT_TASKS_ADMIN_GET";

        $response = $this->projectTaskService->getProjectTasksAdmin($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectTaskStaffCount(Request $request)
    {
        $route_name = "PROJECT_TASK_STAFF_COUNT_GET";

        $response = $this->projectTaskService->getProjectTaskStaffCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    
    //STATUS SECTION
    public function addProjectStatus(Request $request)
    {
        $route_name = "PROJECT_STATUS_ADD";

        $response = $this->projectTaskService->addProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectStatus(Request $request)
    {
        $route_name = "PROJECT_STATUS_GET";

        $response = $this->projectTaskService->getProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectStatuses(Request $request)
    {
        $route_name = "PROJECT_STATUSES_GET";

        $response = $this->projectTaskService->getProjectStatuses($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProjectStatus(Request $request)
    {
        $route_name = "PROJECT_STATUS_UPDATE";

        $response = $this->projectTaskService->updateProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProjectStatus(Request $request)
    {
        $route_name = "PROJECT_STATUS_DELETE";

        $response = $this->projectTaskService->deleteProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Project
    public function addProjectLogNotes(Request $request){
        $route_name = "PROJECT_NOTE_ADD";   
        
        $response = $this->projectTaskService->addProjectLogNotes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteProjectLogNotes(Request $request){
        $route_name = "PROJECT_NOTE_DELETE";   
        
        $response = $this->projectTaskService->deleteProjectLogNotes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //Project Category
    public function getProjectCategoryList(Request $request){
        $route_name = "PROJECT_CATEGORIES_GET";   
        
        $response = $this->projectTaskService->getProjectCategoryList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getProjectCategoryListClient(Request $request){
        $route_name = "PROJECT_CATEGORIES_CLIENT_GET";   
        
        $response = $this->projectTaskService->getProjectCategoryListClient($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateProjectCategoryListClient(Request $request){
        $route_name = "PROJECT_CATEGORIES_CLIENT_UPDATE";   
        
        $response = $this->projectTaskService->updateProjectCategoryListClient($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
