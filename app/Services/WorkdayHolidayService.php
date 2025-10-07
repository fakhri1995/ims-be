<?php 

namespace App\Services;

use App\WorkdayHoliday;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;

class WorkdayHolidayService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    
    public function getWorkdayHolidays(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $workday_id = $request->workday_id ?? NULL;
            $workday_holidays = WorkdayHoliday::where('workday_id', $workday_id);
            if(!$workday_holidays) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 404];
            $data = $workday_holidays->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getWorkdayHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id ?? NULL;
            $workday_holiday = WorkdayHoliday::find($id);
            if(!$workday_holiday) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 404];
            $data = $workday_holiday;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addWorkdayHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $workday_holiday = new WorkdayHoliday;
            $workday_holiday->name = $request->name;
            $workday_holiday->workday_id = $request->workday_id;
            $workday_holiday->from = $request->from;
            $workday_holiday->to = $request->to;

            $workday_holiday->save();
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $workday_holiday->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateWorkdayHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $workday_holiday = WorkdayHoliday::find($id);
            if(!$workday_holiday) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $workday_holiday->name = $request->name;
            $workday_holiday->workday_id = $request->workday_id;
            $workday_holiday->from = $request->from;
            $workday_holiday->to = $request->to;
            $workday_holiday->save();
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteWorkdayHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $workday_holiday = WorkdayHoliday::find($id);
            if(!$workday_holiday) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            $workday_holiday->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

}