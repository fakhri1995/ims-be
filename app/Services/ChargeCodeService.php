<?php 

namespace App\Services;

use App\AttendanceCode;
use App\ChargeCode;
use App\Company;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;

class ChargeCodeService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function getChargeCodeCompanies(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try {
        $rows      = $request->get('rows', 10);
        $keyword   = $request->get('keyword', null);
        $sort_by   = $request->get('sort_by', null);
        $sort_type = $request->get('sort_type', null);

        if ($rows > 100) $rows = 100;
        if ($rows < 1)   $rows = 10;

        $companies = Company::select('id', 'name')
            ->withCount('chargeCodes')
            ->withCount('attendanceCodes')
            ->withCount('employees')
            ->has('chargecodes')
            ->orHas('attendanceCodes');

        $params = "?rows=$rows";
        if ($keyword)   $params .= "&keyword=$keyword";
        if ($sort_by)   $params .= "&sort_by=$sort_by";
        if ($sort_type) $params .= "&sort_type=$sort_type";

        if ($keyword) {
            $companies = $companies->where('name', 'like', "%{$keyword}%");
        }

        if ($sort_by) {
            if ($sort_type === null) $sort_type = 'desc';

            if ($sort_by === 'name') {
                $companies = $companies->orderBy('name', $sort_type);
            } else if ($sort_by === 'charge_codes_count') {
                $companies = $companies->orderBy('charge_codes_count', $sort_type);
            } else if ($sort_by === 'attendance_codes_count') {
                $companies = $companies->orderBy('attendance_codes_count', $sort_type);
            } else if ($sort_by === 'employees_count') {
                $companies = $companies->orderBy('employees_count', $sort_type);
            } else {
                $companies = $companies->orderBy('charge_codes_count', 'desc');
            }
        } else {
            $companies = $companies->orderBy('charge_codes_count', 'desc');
        }

        $companies = $companies->paginate($rows);
        $companies->withPath(env('APP_URL').'/getChargeCodeCompanies'.$params);

        if ($companies->isEmpty()) {
            return [
                "success" => true,
                "message" => "Company list with Charge Codes is empty",
                "data"    => $companies,
                "status"  => 200
            ];
        }

        return [
            "success" => true,
            "message" => "Company list with Charge Codes retrieved successfully",
            "data"    => $companies,
            "status"  => 200
        ];

        } catch (Exception $err) {
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }
    
    public function getChargeCodes(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $company_id = $request->company_id;
            $rows = $request->rows ?? 10;
            $page = $request->page ?? 1;
            $company = Company::find($company_id);
            $charge_codes = ChargeCode::where('company_id', $company_id);
            $data = $charge_codes->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", 
            "data" => [
                "company_name" => $company->name,
                "charge_codes" => $data
            ] , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getChargeCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $charge_code = ChargeCode::find($id);
            if(!$charge_code) return ["success" => false, "message" => "Charge Code Tidak Ditemukan", "status" => 404];
            $data = $charge_code;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addChargeCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{ 
            $company_id = $request->company_id;
            $name = $request->name;
            $description = $request->description;
            $color = $request->color;
            
            $charge_code = new ChargeCode();
            $charge_code->company_id = $company_id;
            $charge_code->name = $name;
            $charge_code->description = $description;
            $charge_code->color = $color;
            $charge_code->save();
            $data = $charge_code->id; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addChargeCodesCompany(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{ 
            $company_id = $request->company_id;

            $charge_codes = (object)$request->charge_codes;
            foreach($charge_codes as $custom){
                $charge_code_arr = (object)$custom;
                $charge_code = new ChargeCode();
                $charge_code->name = $charge_code_arr->name;
                $charge_code->description = $charge_code_arr->description;
                $charge_code->color = $charge_code_arr->color;
                $charge_code->company_id = $company_id;
                $charge_code->save();
            }
            $data = $company_id; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCompanyCodes(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{ 
            $company_id = $request->company_id;

            $attendance_codes = (object)$request->attendance_codes;
            foreach($attendance_codes as $custom){
                $attendance_code_arr = (object)$custom;
                $attendance_code = new AttendanceCode();
                $attendance_code->name = $attendance_code_arr->name;
                $attendance_code->description = $attendance_code_arr->description;
                $attendance_code->company_id = $company_id;
                $attendance_code->hari_masuk = $attendance_code_arr->hari_masuk;
                $attendance_code->hari_penggajian = $attendance_code_arr->hari_penggajian;
                $attendance_code->dapat_ditagih = $attendance_code_arr->dapat_ditagih;
                $attendance_code->perlu_verifikasi = $attendance_code_arr->perlu_verifikasi;
                $attendance_code->save();
            }

            $charge_codes = (object)$request->charge_codes;
            foreach($charge_codes as $custom){
                $charge_code_arr = (object)$custom;
                $charge_code = new ChargeCode();
                $charge_code->name = $charge_code_arr->name;
                $charge_code->description = $charge_code_arr->description;
                $charge_code->color = $charge_code_arr->color;
                $charge_code->company_id = $company_id;
                $charge_code->save();
            }
            $data = $company_id; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    public function addAttendanceCodesCompany(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{ 
            $company_id = $request->company_id;

            $attendance_codes = (object)$request->attendance_codes;
            foreach($attendance_codes as $custom){
                $attendance_code_arr = (object)$custom;
                $attendance_code = new AttendanceCode();
                $attendance_code->name = $attendance_code_arr->name;
                $attendance_code->description = $attendance_code_arr->description;
                $attendance_code->company_id = $company_id;
                $attendance_code->hari_masuk = $attendance_code_arr->hari_masuk;
                $attendance_code->hari_penggajian = $attendance_code_arr->hari_penggajian;
                $attendance_code->dapat_ditagih = $attendance_code_arr->dapat_ditagih;
                $attendance_code->perlu_verifikasi = $attendance_code_arr->perlu_verifikasi;
                $attendance_code->save();
            }
            $data = $company_id; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{ 
            $attendance_code = new AttendanceCode();
            $attendance_code->name = $request->name;
            $attendance_code->description = $request->description;
            $attendance_code->company_id = $request->company_id;
            $attendance_code->hari_masuk = $request->hari_masuk;
            $attendance_code->hari_penggajian = $request->hari_penggajian;
            $attendance_code->dapat_ditagih = $request->dapat_ditagih;
            $attendance_code->perlu_verifikasi = $request->perlu_verifikasi;
            $attendance_code->save();
            $data = $attendance_code->id; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceCodes(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $company_id = $request->company_id;
            $rows = $request->rows ?? 10;
            $page = $request->page ?? 1;
            $company = Company::find($company_id);
            $attendance_codes = AttendanceCode::where('company_id', $company_id);
            $data = $attendance_codes->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", 
            "data" => [
                "company_name" => $company->name,
                "charge_codes" => $data
            ] , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $attendance_code = AttendanceCode::find($id);
            if(!$attendance_code) return ["success" => false, "message" => "Attendance Code Tidak Ditemukan", "status" => 404];
            $data = $attendance_code;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->id;
            $attendance_code = AttendanceCode::find($id);
            if(!$attendance_code) return ["success" => false, "message" => "Attendance Code Tidak Ditemukan", "status" => 404];
            $attendance_code->name = $request->name;
            $attendance_code->description = $request->description;
            $attendance_code->hari_masuk = $request->hari_masuk;
            $attendance_code->hari_penggajian = $request->hari_penggajian;
            $attendance_code->dapat_ditagih = $request->dapat_ditagih;
            $attendance_code->perlu_verifikasi = $request->perlu_verifikasi;
            $attendance_code->save();
            $data = $attendance_code->id; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Diubah", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateChargeCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $company_id = $request->company_id;
            $name = $request->name;
            $description = $request->description;
            $color = $request->color;
            
            $charge_code = ChargeCode::with(["attendanceCodes"])->find($id);
            if(!$charge_code) return ["success" => false, "message" => "Charge Code Tidak Ditemukan", "status" => 404];

            $charge_code->company_id = $company_id;
            $charge_code->name = $name;
            $charge_code->description = $description;
            $charge_code->color = $color;
            $charge_code->save();
            $data = $charge_code->id;
            return ["success" => true, "message" => "Data Berhasil Diubah", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteChargeCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $charge_code = ChargeCode::find($id);
            if(!$charge_code) return ["success" => false, "message" => "Charge Code Tidak Ditemukan", "status" => 404];
            $charge_code->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceCode(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $attendance_code = AttendanceCode::find($id);
            if(!$attendance_code) return ["success" => false, "message" => "Attendance Code Tidak Ditemukan", "status" => 404];
            $attendance_code->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

}