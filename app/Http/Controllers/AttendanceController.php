<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->attendanceService = new AttendanceService;
    }

    // Attendance Forms
    public function getAttendanceForms(Request $request)
    {
        $route_name = "ATTENDANCE_FORMS_GET";

        $response = $this->attendanceService->getAttendanceForms($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_GET";

        $response = $this->attendanceService->getAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_ADD";
        
        $response = $this->attendanceService->addAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addUserAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_USERS_ADD";
        
        $response = $this->attendanceService->addUserAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function removeUserAttendanceForm(Request $request)
    {
        $route_name = "ATTENDANCE_FORM_USERS_REMOVE";
        
        $response = $this->attendanceService->removeUserAttendanceForm($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Attendance Activities
    public function getAttendanceActivities(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITIES_GET";

        $response = $this->attendanceService->getAttendanceActivities($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceActivity(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITY_GET";

        $response = $this->attendanceService->getAttendanceActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceActivity(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITY_ADD";
        
        $response = $this->attendanceService->addAttendanceActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceActivity(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITY_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceActivity(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITY_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Attendance User
    public function getAttendancesUsers(Request $request)
    {
        $route_name = "ATTENDANCES_USERS_GET";

        $response = $this->attendanceService->getAttendancesUsers($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendancesUsersPaginate(Request $request)
    {
        $route_name = "ATTENDANCE_USERS_PAGINATE_GET";

        $response = $this->attendanceService->getAttendancesUsersPaginate($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendancesUsersCount(Request $request)
    {
        $route_name = "ATTENDANCES_USERS_COUNT_GET";

        $response = $this->attendanceService->getAttendancesUsersStatistic($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendancesUser(Request $request)
    {
        $route_name = "ATTENDANCES_USER_GET";

        $response = $this->attendanceService->getAttendancesUser($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceUser(Request $request)
    {
        $route_name = "ATTENDANCE_USER_GET";

        $response = $this->attendanceService->getAttendanceUser($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceUserAdmin(Request $request)
    {
        $route_name = "ATTENDANCE_USER_ADMIN_GET";

        $response = $this->attendanceService->getAttendanceUserAdmin($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function exportAttendanceActivityUser(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITY_USER_EXPORT";

        $response = $this->attendanceService->exportAttendanceActivityUser($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return $response['data'];
    }

    public function exportAttendanceActivityUsers(Request $request)
    {
        $route_name = "ATTENDANCE_ACTIVITY_USERS_EXPORT";

        $response = $this->attendanceService->exportAttendanceActivityUsers($request, $route_name);
        if(!$response['success']) return response()->json($response, $response['status']);
        return $response['data'];
    }

    public function setAttendanceToggle(Request $request)
    {
        $route_name = "ATTENDANCE_TOGGLE_SET";

        $response = $this->attendanceService->setAttendanceToggle($request, $route_name);
        return response()->json($response, $response['status']);
    }

   // Attendance Project 
    public function getAttendanceProjects(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECTS_GET";

        $response = $this->attendanceService->getAttendanceProjects($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceProject(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_ADD";
        
        $response = $this->attendanceService->addAttendanceProject($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceProject(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceProject($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceProject(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceProject($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    // Attendance Project Type
    public function getAttendanceProjectTypes(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_TYPES_GET";

        $response = $this->attendanceService->getAttendanceProjectTypes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceProjectType(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_TYPE_ADD";
        
        $response = $this->attendanceService->addAttendanceProjectType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceProjectType(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_TYPE_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceProjectType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceProjectType(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_TYPE_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceProjectType($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Attendance Project Status
    public function getAttendanceProjectStatuses(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_STATUSES_GET";

        $response = $this->attendanceService->getAttendanceProjectStatuses($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceProjectStatus(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_STATUS_ADD";
        
        $response = $this->attendanceService->addAttendanceProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceProjectStatus(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_STATUS_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceProjectStatus(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_STATUS_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceProjectStatus($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Attendance Project Category
    public function getAttendanceProjectCategories(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_CATEGORIES_GET";

        $response = $this->attendanceService->getAttendanceProjectCategories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceProjectCategory(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_CATEGORY_ADD";
        
        $response = $this->attendanceService->addAttendanceProjectCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceProjectCategory(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_CATEGORY_UPDATE";
        
        $response = $this->attendanceService->updateAttendanceProjectCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceProjectCategory(Request $request)
    {
        $route_name = "ATTENDANCE_PROJECT_CATEGORY_DELETE";
        
        $response = $this->attendanceService->deleteAttendanceProjectCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceTaskActivity(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTIVITY_GET";

        $response = $this->attendanceService->getAttendanceTaskActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceTaskActivities(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTVITIES_GET";

        $response = $this->attendanceService->getAttendanceTaskActivities($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAttendanceTaskActivitiesAdmin(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTIVITIES_ADMIN_GET";

        $response = $this->attendanceService->getAttendanceTaskActivitiesAdmin($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceTaskActivity(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTIVITY_ADD";

        $response = $this->attendanceService->addAttendanceTaskActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAttendanceTaskActivities(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTIVITIES_ADD";

        $response = $this->attendanceService->addAttendanceTaskActivities($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAttendanceTaskActivity(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTIVITY_UPDATE";

        $response = $this->attendanceService->updateAttendanceTaskActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAttendanceTaskActivity(Request $request)
    {
        $route_name = "ATTENDANCE_TASK_ACTIVITY_DELETE";

        $response = $this->attendanceService->deleteAttendanceTaskActivity($request, $route_name);
        return response()->json($response, $response['status']);
    }
}