<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaskService;

class TaskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    
    public function __construct()
    {
        $this->taskService = new TaskService;
    }

    // Task

    // public function getAdminTaskData(Request $request)
    // {
    //     $route_name = "TASK_ADMIN_DATA_GET";

    //     $response = $this->taskService->getAdminTaskData($request, $route_name);
    //     return response()->json($response, $response['status']);
    // }
    
    public function getStatusTaskList(Request $request)
    {
        $route_name = "TASK_STATUS_LIST_GET";

        $response = $this->taskService->getStatusTaskList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getDeadlineTasks(Request $request)
    {
        $route_name = "TASK_DEADLINE_GET";

        $response = $this->taskService->getDeadlineTasks($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskStaffCounts(Request $request)
    {
        $route_name = "TASK_STAFF_COUNTS_GET";

        $response = $this->taskService->getTaskStaffCounts($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getTasks(Request $request)
    {
        $route_name = "TASKS_GET";

        $response = $this->taskService->getTasks($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTask(Request $request)
    {
        $route_name = "TASK_GET";

        $response = $this->taskService->getTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTask(Request $request)
    {
        $route_name = "TASK_ADD";

        $response = $this->taskService->addTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateTask(Request $request)
    {
        $route_name = "TASK_UPDATE";

        $response = $this->taskService->updateTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTask(Request $request)
    {
        $route_name = "TASK_DELETE";

        $response = $this->taskService->deleteTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Task Detail
    public function addTaskDetail(Request $request)
    {
        $route_name = "TASK_DETAIL_ADD";

        $response = $this->taskService->addTaskDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateTaskDetail(Request $request)
    {
        $route_name = "TASK_DETAIL_UPDATE";

        $response = $this->taskService->updateTaskDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTaskDetail(Request $request)
    {
        $route_name = "TASK_DETAIL_DELETE";

        $response = $this->taskService->deleteTaskDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Type Task

    public function getTaskTypeCounts(Request $request)
    {
        $route_name = "TASK_TYPE_COUNTS_GET";

        $response = $this->taskService->getTaskTypeCounts($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getFilterTaskTypes(Request $request)
    {
        $route_name = "TASK_ADD";

        $response = $this->taskService->getFilterTaskTypes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskTypes(Request $request)
    {
        $route_name = "TASKS_GET";

        $response = $this->taskService->getTaskTypes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskType(Request $request)
    {
        $route_name = "TASK_GET";

        $response = $this->taskService->getTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTaskType(Request $request)
    {
        $route_name = "TASK_ADD";

        $response = $this->taskService->addTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateTaskType(Request $request)
    {
        $route_name = "TASK_UPDATE";

        $response = $this->taskService->updateTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTaskType(Request $request)
    {
        $route_name = "TASK_DELETE";

        $response = $this->taskService->deleteTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }
}