<?php

namespace App\Http\Controllers;

use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleService;
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->scheduleService = new ScheduleService;
    }

    public function getSchedules(Request $request){
        $route_name = "SCHEDULES_GET";

        $response = $this->scheduleService->getSchedules($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getSchedule(Request $request){
        $route_name = "SCHEDULE_GET";

        $response = $this->scheduleService->getSchedule($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addSchedule(Request $request){
        $route_name = "SCHEDULE_ADD";

        $response = $this->scheduleService->addSchedule($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateSchedule(Request $request){
        $route_name = "SCHEDULE_UPDATE";

        $response = $this->scheduleService->updateSchedule($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteSchedule(Request $request){
        $route_name = "SCHEDULE_DELETE";

        $response = $this->scheduleService->deleteSchedule($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
