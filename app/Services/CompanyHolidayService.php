<?php 

namespace App\Services;

use App\CompanyHoliday;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;

class CompanyHolidayService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    
    public function getCompanyHolidays(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
						$company_id = $request->company_id ?? NULL;
						$company_holidays = CompanyHoliday::where('company_id', $company_id);
						if(!$company_holidays) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 404];
            $data = $company_holidays->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCompanyHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
						$id = $request->id ?? NULL;
						$company_holiday = CompanyHoliday::find($id);
						if(!$company_holiday) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 404];
            $data = $company_holiday;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCompanyHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
						$company_holiday = new CompanyHoliday;
						$company_holiday->name = $request->name;
						$company_holiday->company_id = $request->company_id;
						$company_holiday->date = $request->date;

						$company_holiday->save();
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $company_holiday->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateCompanyHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
						$id = $request->id;
						$company_holiday = CompanyHoliday::find($id);
						if(!$company_holiday) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
						$company_holiday->name = $request->name;
						$company_holiday->company_id = $request->company_id;
						$company_holiday->date = $request->date;
						$company_holiday->save();
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteCompanyHoliday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
						$id = $request->id;
						$company_holiday = CompanyHoliday::find($id);
						if(!$company_holiday) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
						$company_holiday->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

}