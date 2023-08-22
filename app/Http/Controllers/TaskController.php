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
    
    public function addTaskReport(Request $request)
    {
        $route_name = "TASK_REPORT_ADD";

        $response = $this->taskService->addTaskReport($request, $route_name);
        return response()->json($response, $response['status']);
    }


    public function getTaskReports(Request $request)
    {
        $route_name = "TASK_REPORTS_GET";

        $response = $this->taskService->getTaskReports($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskReport(Request $request)
    {
        $route_name = "TASK_REPORT_GET";

        $response = $this->taskService->getTaskReport($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTaskReport(Request $request)
    {
        $route_name = "TASK_REPORT_DELETE";

        $response = $this->taskService->deleteTaskReport($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getUserTasks(Request $request)
    {
        $route_name = "TASKS_USER_GET";

        $response = $this->taskService->getUserTasks($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getStaffTaskStatuses(Request $request)
    {
        $route_name = "TASK_STAFF_STATUSES_GET";
        
        $response = $this->taskService->getStaffTaskStatuses($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientTaskStatusList(Request $request)
    {
        $route_name = "TASK_CLIENT_STATUSES_GET";
        
        $response = $this->taskService->getClientTaskStatusList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getUserTaskStatusList(Request $request)
    {
        $route_name = "TASK_USER_STATUSES_GET";
        
        $response = $this->taskService->getUserTaskStatusList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getUserLastTwoTasks(Request $request)
    {
        $route_name = "TASKS_USER_LAST_TWO_GET";
        
        $response = $this->taskService->getUserLastTwoTasks($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getUserTaskTypeCounts(Request $request)
    {
        $route_name = "TASK_TYPE_USER_COUNTS_GET";
        
        $response = $this->taskService->getUserTaskTypeCounts($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskSparePartList(Request $request)
    {
        $route_name = "TASK_SPARE_PART_LIST_GET";
        
        $response = $this->taskService->getTaskSparePartList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskPickList(Request $request)
    {
        $route_name = "TASK_PICK_LIST_GET";
        
        $response = $this->taskService->getTaskPickList($request, $route_name);
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

    public function saveFilesTask(Request $request)
    {
        $route_name = "TASK_FILES_SAVE";

        $response = $this->taskService->saveFilesTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteFileTask(Request $request)
    {
        $route_name = "TASK_FILES_DELETE";

        $response = $this->taskService->deleteFileTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTask(Request $request)
    {
        $route_name = "TASK_DELETE";

        $response = $this->taskService->deleteTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeStatusToggle(Request $request)
    {
        $route_name = "TASK_STATUS_TOGGLE";

        $response = $this->taskService->changeStatusToggle($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeAttendanceToggle(Request $request)
    {
        $route_name = "TASK_ATTENDANCE_TOGGLE";

        $response = $this->taskService->changeAttendanceToggle($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function rejectTask(Request $request)
    {
        $route_name = "TASK_REJECT";

        $response = $this->taskService->rejectTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function approveTask(Request $request)
    {
        $route_name = "TASK_APPROVE";

        $response = $this->taskService->approveTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function submitTask(Request $request)
    {
        $route_name = "TASK_SUBMIT";

        $response = $this->taskService->submitTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function declineTask(Request $request)
    {
        $route_name = "TASK_DECLINE";

        $response = $this->taskService->declineTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function assignSelfTask(Request $request)
    {
        $route_name = "TASK_ASSIGN_SELF";

        $response = $this->taskService->assignSelfTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function sendInventoriesTask(Request $request)
    {
        $route_name = "TASK_SEND_INVENTORIES";

        $response = $this->taskService->sendInventoriesTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function sendInInventoryTask(Request $request)
    {
        $route_name = "TASK_SEND_IN_INVENTORY";

        $response = $this->taskService->sendInInventoryTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function sendOutInventoryTask(Request $request)
    {
        $route_name = "TASK_SEND_OUT_INVENTORY";

        $response = $this->taskService->sendOutInventoryTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function cancelSendInInventoryTask(Request $request)
    {
        $route_name = "TASK_CANCEL_SEND_IN_INVENTORY";

        $response = $this->taskService->cancelSendInInventoryTask($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function cancelSendOutInventoryTask(Request $request)
    {
        $route_name = "TASK_CANCEL_SEND_OUT_INVENTORY";

        $response = $this->taskService->cancelSendOutInventoryTask($request, $route_name);
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

    public function assignTaskDetail(Request $request)
    {
        $route_name = "TASK_DETAIL_ASSIGN";

        $response = $this->taskService->assignTaskDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function fillTaskDetail(Request $request)
    {
        $route_name = "TASK_DETAIL_FILL";

        $response = $this->taskService->fillTaskDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function fillTaskDetails(Request $request)
    {
        $route_name = "TASK_DETAILS_FILL";

        $response = $this->taskService->fillTaskDetails($request, $route_name);
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
        $route_name = "TASK_TYPES_GET";

        $response = $this->taskService->getFilterTaskTypes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskTypes(Request $request)
    {
        $route_name = "TASK_TYPES_GET";

        $response = $this->taskService->getTaskTypes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTaskType(Request $request)
    {
        $route_name = "TASK_TYPE_GET";

        $response = $this->taskService->getTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTaskType(Request $request)
    {
        $route_name = "TASK_TYPE_ADD";

        $response = $this->taskService->addTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateTaskType(Request $request)
    {
        $route_name = "TASK_TYPE_UPDATE";

        $response = $this->taskService->updateTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTaskType(Request $request)
    {
        $route_name = "TASK_TYPE_DELETE";

        $response = $this->taskService->deleteTaskType($request, $route_name);
        return response()->json($response, $response['status']);
    }
}