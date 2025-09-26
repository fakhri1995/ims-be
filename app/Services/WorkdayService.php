<?php 

namespace App\Services;

use App\Company;
use App\PublicHoliday;
use Exception;
use App\Services\GlobalService;
use App\Workday;
use Illuminate\Http\Request;

class WorkdayService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    public function getWorkdayCompanies(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', null);

            $companies = Company::with(["workdays, employeeCount"])->withCount("workdays");
            $data = $companies->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getWorkdayStatistics(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $company_id = $request->company_id;
            $company = Company::with(['employees','workdays'])->withCount(['employees'])->where('id', $company_id)->first();
            $total_employees = $company->employees_count;

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getWorkdays(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $company_id = $request->company_id;
            $workdays = Workday::where('company_id', $company_id);
            
            $data = $workdays->get(); //what you want to send
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->id;
            
            $workday = Workday::with(["holidays"])->where('id', $id);
            $data = $workday->first();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPublicHolidays(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $holidays = PublicHoliday::get();
            $data = $holidays;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $holidays, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $year = $request->year;
            $month = $request->month;
            $date = $year . '-' . $month . '-01';

            $schedule = $request->schedule;

            $workday = new Workday();
            $workday->name = $request->name;
            $workday->company_id = $request->company_id;
            $workday->date = $date;
            $workday->schedule = $schedule;
            $workday->save();

            $holidays = $request->holidays ?? [];
            $workday->holidays()->sync($holidays);
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $workday->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $year = $request->year;
            $month = $request->month;
            $date = $year . '-' . $month . '-01';

            $schedule = $request->schedule;

            $workday = Workday::find($id);
            $workday->name = $request->name;
            $workday->company_id = $request->company_id;
            $workday->date = $date;
            $workday->schedule = $schedule;
            $workday->save();

            $holidays = $request->holidays ?? [];
            $workday->holidays()->sync($holidays);
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $workday = Workday::find($id);
            if($workday === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $workday->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

}