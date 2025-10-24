<?php

namespace App\Services;
use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Exception;
use App\LongLatList;
use GuzzleHttp\Client;
use App\AttendanceForm;
use App\AttendanceUser;
use App\AttendanceProject;
use App\AttendanceActivity;
use Illuminate\Support\Str;
use App\Services\FileService;
use App\AttendanceCode;
use App\AttendanceVerification;
use App\Company;
use App\File;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Symfony\Component\HttpFoundation\File\File as FileFile;

class AttendanceVerificationService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    // Attendance Form
    public function getAttendanceVerifications($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            $attendance_verifications = AttendanceVerification::with(['supporting_file:link,description,fileable_id,fileable_type','attendanceUser.user', 'attendanceUser.attendanceCode.company',])->select('attendance_verifications.*')
            ->join('attendance_users', 'attendance_users.id', '=', 'attendance_verifications.attendance_user_id')
    ->join('attendance_codes', 'attendance_codes.id', '=', 'attendance_users.attendance_code_id')
    ->join('companies', 'companies.id', '=', 'attendance_codes.company_id')        
    ->join('users', 'users.id', '=', 'attendance_users.user_id')
    
    ->where('status_verification','Waiting');
            $params = "?rows=$rows";
            if($keyword) $params = "$params&keyword=$keyword";
            if($sort_by) $params = "$params&sort_by=$sort_by";
            if($sort_type) $params = "$params&sort_type=$sort_type";

            if($keyword) $attendance_verifications = $attendance_verifications->where('users.name', 'like', "%".$keyword."%");
            if($sort_by){
                if($sort_type === null) $sort_type = 'desc';
                if($sort_by === 'name') $attendance_verifications = $attendance_verifications->orderBy('users.name', $sort_type);
                else if($sort_by === 'company') $attendance_verifications = $attendance_verifications->orderBy('companies.name', $sort_type);
                else if($sort_by === 'created_at') $attendance_verifications = $attendance_verifications->orderBy('created_at', $sort_type);
                // else if($sort_by === 'count') $attendance_forms = $attendance_forms->orderBy('users_count', $sort_type);
            } else {
                // $attendance_forms = $attendance_forms->orderBy('users_count', 'desc');
            }

            $attendance_verifications = $attendance_verifications->paginate($rows);
            $attendance_verifications->withPath(env('APP_URL').'/getAttendanceVerifications'.$params);
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances Verification", "data" => $attendance_verifications, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    public function getAttendanceHistoryVerifications($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
             $attendance_verifications = AttendanceVerification::with(['supporting_file:link,description,fileable_id,fileable_type','attendanceUser.user', 'attendanceUser.attendanceCode.company',])->select('attendance_verifications.*')
            ->join('attendance_users', 'attendance_users.id', '=', 'attendance_verifications.attendance_user_id')
    ->join('attendance_codes', 'attendance_codes.id', '=', 'attendance_users.attendance_code_id')
    ->join('companies', 'companies.id', '=', 'attendance_codes.company_id')        
    ->join('users', 'users.id', '=', 'attendance_users.user_id')
    ->where('status_verification', '!=','Waiting');

            $params = "?rows=$rows";
            if($keyword) $params = "$params&keyword=$keyword";
            if($sort_by) $params = "$params&sort_by=$sort_by";
            if($sort_type) $params = "$params&sort_type=$sort_type";

            if($keyword) $attendance_verifications = $attendance_verifications->where('users.name', 'like', "%".$keyword."%");
            if($sort_by){
                if($sort_type === null) $sort_type = 'desc';
                if($sort_by === 'name') $attendance_verifications = $attendance_verifications->orderBy('users.name', $sort_type);
                else if($sort_by === 'company') $attendance_verifications = $attendance_verifications->orderBy('companies.name', $sort_type);
                else if($sort_by === 'created_at') $attendance_verifications = $attendance_verifications->orderBy('created_at', $sort_type);
                // $attendance_forms = $attendance_forms->orderBy('users_count', 'desc');
            }

            $attendance_verifications = $attendance_verifications->paginate($rows);
            $attendance_verifications->withPath(env('APP_URL').'/getAttendanceVerifications'.$params);
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances Verification", "data" => $attendance_verifications, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function approveAttendanceVerification($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_verification = AttendanceVerification::find($id);
        if($attendance_verification === null) return ["success" => false, "message" => "Attendance Verification Tidak Ditemukan", "status" => 400];
        if ($attendance_verification) {
            $attendance_verification->status_verification = 'Approved';
            $attendance_verification->save();
        }
        
        try{
            return ["success" => true, "message" => "Attendance Verification berhasil diapprove", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getLine(), "status" => 400];
        }
    }

      public function rejectAttendanceVerification($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_verification = AttendanceVerification::find($id);
        if($attendance_verification === null) return ["success" => false, "message" => "Attendance Verification Tidak Ditemukan", "status" => 400];
        if ($attendance_verification) {
            $attendance_verification->status_verification = 'Rejected';
            $attendance_verification->save();
        }
        
        try{
            return ["success" => true, "message" => "Attendance Verification berhasil direject", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getLine(), "status" => 400];
        }
    }
    
}
