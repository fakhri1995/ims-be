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
use App\AttendanceProjectType;
use App\AttendanceProjectStatus;
use App\AttendanceProjectCategory;
use App\AttendanceTaskActivity;
use App\Company;
use App\Exports\AttendanceActivitiesExport;
use App\File;
use App\ProjectTask;
use App\Task;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class AttendanceService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    // Attendance Form
    public function getAttendanceForms($request, $route_name)
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
            $attendance_forms = AttendanceForm::select('id', 'name', 'description', 'updated_at')->withCount('users');

            $params = "?rows=$rows";
            if($keyword) $params = "$params&keyword=$keyword";
            if($sort_by) $params = "$params&sort_by=$sort_by";
            if($sort_type) $params = "$params&sort_type=$sort_type";

            if($keyword) $attendance_forms = $attendance_forms->where('name', 'like', "%".$keyword."%")->orWhere('description', 'like', "%".$keyword."%");
            if($sort_by){
                if($sort_type === null) $sort_type = 'desc';
                if($sort_by === 'name') $attendance_forms = $attendance_forms->orderBy('name', $sort_type);
                else if($sort_by === 'description') $attendance_forms = $attendance_forms->orderBy('description', $sort_type);
                else if($sort_by === 'updated_at') $attendance_forms = $attendance_forms->orderBy('updated_at', $sort_type);
                else if($sort_by === 'count') $attendance_forms = $attendance_forms->orderBy('users_count', $sort_type);
            } else {
                $attendance_forms = $attendance_forms->orderBy('users_count', 'desc');
            }

            $attendance_forms = $attendance_forms->paginate($rows);
            $attendance_forms->withPath(env('APP_URL').'/getAttendanceForms'.$params);
            if($attendance_forms->isEmpty()) return ["success" => true, "message" => "Attendance Forms Masih Kosong", "data" => $attendance_forms, "status" => 200];
            return ["success" => true, "message" => "Attendance Forms Berhasil Diambil", "data" => $attendance_forms, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $attendance_form = AttendanceForm::with(['users:id,name,position', 'users.profileImage', 'creator:id,name,position'])->find($id);
            if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Attendance Form Berhasil Diambil", "data" => $attendance_form, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $name = $request->get('name');
        $check_attendance_form_name = AttendanceForm::where('name', $name)->first();
        if($check_attendance_form_name) return ["success" => false, "message" => "Nama Form Aktivitas Sudah Ada", "status" => 400];
        $attendance_form = new AttendanceForm;
        $attendance_form->name = $name;
        $attendance_form->description = $request->get('description');
        $attendance_form->updated_at = date('Y-m-d H:i:s');
        $attendance_form->created_by = auth()->user()->id;
        $details = $request->get('details', []);
        try{
            $i = 1;
            if(count($details)){
                foreach($details as &$detail){
                    if(!isset($detail['required'])) return ["success" => false, "message" => "Detail form $i masih kosong pada required", "status" => 400];
                    if(gettype($detail['required']) !== "boolean") return ["success" => false, "message" => "Detail form $i pada required harus bertipe boolean", "status" => 400];
                    if(!isset($detail['name'])) return ["success" => false, "message" => "Detail form $i masih kosong pada name", "status" => 400];
                    if(gettype($detail['name']) !== "string") return ["success" => false, "message" => "Detail form $i pada name harus bertipe string", "status" => 400];
                    if(!isset($detail['description'])) return ["success" => false, "message" => "Detail form $i masih kosong pada description", "status" => 400];
                    if(gettype($detail['description']) !== "string") return ["success" => false, "message" => "Detail form $i pada description harus bertipe string", "status" => 400];
                    if(!isset($detail['type'])) return ["success" => false, "message" => "Detail form $i masih kosong pada type", "status" => 400];
                    if(gettype($detail['type']) !== "integer") return ["success" => false, "message" => "Detail form $i pada type harus bertipe integer", "status" => 400];
                    if(in_array($detail['type'], [3,5])){
                        if(!isset($detail['list'])) return ["success" => false, "message" => "Detail form $i masih kosong pada list", "status" => 400];
                        if(gettype($detail['list']) !== "array") return ["success" => false, "message" => "Detail form $i pada list harus bertipe array", "status" => 400];
                    }
                    $detail['key'] = Str::uuid()->toString();
                    $i++;
                }
            }
            $attendance_form->details = $details;
            $attendance_form->save();
            return ["success" => true, "message" => "Attendance Form Berhasil Ditambahkan", "id" => $attendance_form->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_form = AttendanceForm::find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $attendance_form->name = $request->get('name');
        $attendance_form->description = $request->get('description');
        $attendance_form->updated_at = date('Y-m-d H:i:s');
        try{
            $attendance_form->save();
            return ["success" => true, "message" => "Attendance Form Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_form = AttendanceForm::find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];

        try{
            $attendance_form->delete();
            return ["success" => true, "message" => "Attendance Form berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addUserAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $user_ids = $request->get('user_ids', []);
        $attendance_form = AttendanceForm::with('users')->find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if(!count($user_ids)) return ["success" => false, "message" => "user_ids masih kosong", "status" => 400];

        try{
            $users = User::with('attendanceForms:id,name')->whereIn('id', $user_ids)->get();
            foreach($users as $user) $user->attendanceForms()->detach();
            $attendance_form->users()->syncWithoutDetaching($user_ids);
            return ["success" => true, "message" => "User Attendance Form Berhasil Ditambah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function removeUserAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $user_ids = $request->get('user_ids', []);
        $attendance_form = AttendanceForm::with('users')->find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];

        try{
            $attendance_form->users()->detach($user_ids);
            return ["success" => true, "message" => "User Attendance Form Berhasil Dikeluarkan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Attendance Activity
    public function getAttendanceActivities($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $last_two_month = date("Y-m-d", strtotime("-2 months"));
            $today = date('Y-m-d');
            $login_id = auth()->user()->id;
            $today_attendance_activities = AttendanceActivity::where('user_id', $login_id)->whereDate('updated_at', '=', $today)->get();
            $last_two_month_attendance_activities = AttendanceActivity::where('user_id', $login_id)->whereDate('updated_at', '>', $last_two_month)->whereDate('updated_at', '<>', $today)->get();
            $data = (object)[
                "today_activities" => $today_attendance_activities,
                "last_two_month_activities" => $last_two_month_attendance_activities
            ];
            return ["success" => true, "message" => "Attendance Activities Berhasil Diambil", "data" => $data, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceActivitiesAdmin($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $rows = $request->rows ?? NULL;
            $id = $request->get('id');
            $user_attendance = AttendanceUser::find($id);
            if(!$user_attendance) return ["success" => false, "message" => "User Attendance Tidak Ditemukan" , "status" => 400];
            $data = AttendanceActivity::where('user_id', $user_attendance->user_id)->whereDate('updated_at', '=', date('Y-m-d', strtotime($user_attendance->check_in)));
            if($rows) $data = $data->paginate($rows);
            else $data = $data->get();
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendance", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceActivity($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $attendance_activity = AttendanceActivity::find($id);
            if($attendance_activity === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Attendance Activity Berhasil Diambil", "data" => $attendance_activity, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function addAttendanceActivityFile($id, $file, $description)
    {
        $fileService = new FileService;
        $add_file_response = $fileService->addFile($id, $file, 'App\AttendanceActivity', $description, 'AttendanceActivity', false);
        return $add_file_response;
    }

    public function addAttendanceActivity($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $login_id = auth()->user()->id;
        $attendance_form_id = $request->get('attendance_form_id');
        $attendance_form = AttendanceForm::find($attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Form Tidak Ditemukan", "status" => 400];
        $last_check_in = AttendanceUser::where('user_id', $login_id)->orderBy('check_in', 'desc')->first();
        if($last_check_in->check_out) return ["success" => false, "message" => "Silahkan Lakukan Check In Terlebih Dahulu", "status" => 400];
        $activity_details = $request->get('details', []);
        ksort($activity_details);
        $fileArray = []; // index => [ "key" : value, "value" : value ]
        foreach($attendance_form->details as $form_detail){
            $search = array_search($form_detail['key'], array_column($activity_details, 'key'));
            if($search === false) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum diisi" , "status" => 400];
            if($form_detail['type'] === 6){
                $file = $request->file("details.$search.value",NULL);
                $isFile = is_file($file);
                if($form_detail['required'] && !$isFile) return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe file" , "status" => 400];
                else if($isFile) {
                    $activity_details[$search]['value'] = true;
                }
                else $activity_details[$search]['value'] = NULL;

                $fileArray[$search] = [
                    "key" => $form_detail['key'],
                    "file" => $file
                ];
            }
            // if(!isset($activity_details[$search]['value']) || ($form_detail['required'] && $activity_details[$search]['value'] === "")) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum memiliki value" , "status" => 400];
            if(!isset($activity_details[$search]['value']) && $form_detail['required']) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum memiliki value" , "status" => 400];
            if($form_detail['type'] === 3){
                if(gettype($activity_details[$search]['value']) !== "array") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe array", "status" => 400];
            } else if($form_detail['type'] !== 6) {
                if(gettype($activity_details[$search]['value']) !== "string") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe string", "status" => 400];
            }
        }
        $attendance_activity = new AttendanceActivity;
        $attendance_activity->user_id = auth()->user()->id;
        $attendance_activity->attendance_form_id = $attendance_form_id;
        // $attendance_activity->attendance_project_id = $request->get('attendance_project_id');
        // $attendance_activity->attendance_project_status_id = $request->get('attendance_project_status_id');
        $attendance_activity->updated_at = date('Y-m-d H:i:s');
        $attendance_activity->details = $activity_details;

        try{
            $attendance_activity->save();

            $attendance_id = $attendance_activity->id;
            foreach($fileArray as $index => $value){
                if(!$activity_details[$index]['value']) continue;
                $uploadFile = $this->addAttendanceActivityFile($attendance_id, $value['file'], $value['key']);
                if($uploadFile['success']) $activity_details[$index]['value'] = $uploadFile['new_data']->link;
            }
            $attendance_activity->details = $activity_details;
            $attendance_activity->save();

            return ["success" => true, "message" => "Attendance Activity Berhasil Dibuat", "id" => $attendance_activity->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceActivity($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_activity = AttendanceActivity::find($id);
        if($attendance_activity === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($attendance_activity->user_id !== auth()->user()->id) return ["success" => false, "message" => "Aktivitas bukan milik user login", "status" => 400];
        if(date('Y-m-d', strtotime($attendance_activity->updated_at)) !== date('Y-m-d')) return ["success" => false, "message" => "Aktivitas selain hari ini tidak dapat dihapus", "status" => 400];
        $attendance_form = AttendanceForm::find($attendance_activity->attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Form Tidak Ditemukan", "status" => 400];
        $activity_details = $request->get('details', []);
        ksort($activity_details);
        $fileArray = []; // index => [ "key" : value, "value" : value ]
        $old_activity_details = $attendance_activity->details;
        foreach($attendance_form->details as $form_detail){
            $search = array_search($form_detail['key'], array_column($activity_details, 'key'));
            if($search === false) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum diisi" , "status" => 400];
            if($form_detail['type'] === 6){
                $file = $request->file("details.$search.value",NULL);
                $isFile = is_file($file);
                $search_old = array_search($form_detail['key'], array_column($old_activity_details, 'key'));
                // if($form_detail['required'] && !$isFile && $old_activity_details[$search_old]['value'] == NULL) return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe file" , "status" => 400];
                // else if($isFile) {
                //     $activity_details[$search]['value'] = true;
                // }else {
                //     if(!isset($activity_details[$search]['value'])) $activity_details[$search]['value'] = NULL;
                //     else $activity_details[$search]['value'] = $activity_details[$search]['value'] == $old_activity_details[$search_old]['value'] ? $activity_details[$search]['value'] : NULL;
                // }
                $activity_details[$search]['value'] = isset($activity_details[$search]['value']) && $activity_details[$search]['value'] ? $activity_details[$search]['value'] : NULL ;
                $isSameValue = $activity_details[$search]['value'] == $old_activity_details[$search_old]['value'];
                if($form_detail['required'] && !$isFile && (!$activity_details[$search]['value'] || !$isSameValue)) return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe file" , "status" => 400];
                else if($isFile) {
                    $activity_details[$search]['value'] = true;
                }else {
                    $activity_details[$search]['value'] = $isSameValue? $activity_details[$search]['value'] : NULL;
                }


                $fileArray[$search] = [
                    "key" => $form_detail['key'],
                    "file" => $file
                ];

            }
            // if(!isset($activity_details[$search]['value']) || ($form_detail['required'] && $activity_details[$search]['value'] === "")) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum memiliki value" , "status" => 400];
            if(!isset($activity_details[$search]['value']) && $form_detail['required']) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum memiliki value" , "status" => 400];
            if($form_detail['type'] === 3){
                if(gettype($activity_details[$search]['value']) !== "array") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe array", "status" => 400];
            } else if($form_detail['type'] !== 6) {
                if(gettype($activity_details[$search]['value']) !== "string") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe string", "status" => 400];
            }
        }
        $current_time = date('Y-m-d H:i:s');
        $attendance_activity->updated_at = $current_time;
        $attendance_activity->details = $activity_details;
        try{

            $id = $attendance_activity->id;

            foreach($fileArray as $index => $value){
                if($activity_details[$index]['value'] === true){
                    $old_activity_details[$index]['value'];
                    $deleteOldFile = File::where(['fileable_id' => $id, 'description' => $value['key']])
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $current_time]);
                    $uploadFile = $this->addAttendanceActivityFile($id, $value['file'], $value['key']);
                    if($uploadFile['success']) $activity_details[$index]['value'] = $uploadFile['new_data']->link;
                }else if($activity_details[$search]['value'] === NULL){
                    $deleteOldFile = File::where(['fileable_id' => $id, 'description' => $value['key']])
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $current_time]);
                }
            }
            $attendance_activity->details = $activity_details;
            $attendance_activity->save();
            return ["success" => true, "message" => "Attendance Activity Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceActivity($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_activity = AttendanceActivity::find($id);
        if($attendance_activity === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($attendance_activity->user_id !== auth()->user()->id) return ["success" => false, "message" => "Aktivitas bukan milik user login", "status" => 400];
        if(date('Y-m-d', strtotime($attendance_activity->updated_at)) !== date('Y-m-d')) return ["success" => false, "message" => "Aktivitas selain hari ini tidak dapat dihapus", "status" => 400];

        try{
            $attendance_activity->delete();
            return ["success" => true, "message" => "Attendance Activity berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function activityExport($from, $to, $attendance_form,$form_ids=[], $multiple = false, $user_ids = [])
    {
        ob_end_clean(); // this
        ob_start(); // and this
        return Excel::download(new AttendanceActivitiesExport($from, $to, $attendance_form, $form_ids, $multiple, $user_ids), 'AttendanceActivityReport.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        // return Excel::download(new AttendanceActivitiesExport($from, $to, $attendance_form, $form_ids, $multiple, $user_ids), 'AttendanceActivityReport.xlsx', \Maatwebsite\Excel\Excel::XLS);
    }

    public function exportAttendanceActivityUser($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $attendance_forms = auth()->user()->attendanceForms;
        if(!count($attendance_forms)) return ["success" => false, "message" => "User Belum Memiliki Form Kehadiran", "status" => 400];
        $excel = $this->activityExport($from, $to, $attendance_forms[0], $request->get('attendance_form_id'));
        return ["success" => true, "message" => "Berhasil Export Attendance Activity", "data" => $excel, "status" => 200];
    }

    public function exportAttendanceActivityUsers($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $attendance_form = AttendanceForm::find($request->get('attendance_form_id'));

        $user_ids = json_decode($request->get('user_ids', "[]"));
        if(!$attendance_form) return ["success" => false, "message" => "Attendance Form Tidak Ditemukan", "status" => 400];
        $excel = $this->activityExport($from, $to, $attendance_form, $request->get('attendance_form_id'), true, $user_ids);

        return ["success" => true, "message" => "Berhasil Export Attendance Activity", "data" => $excel, "status" => 200];
    }

    // Attendance User
    public function getAttendancesUsers($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $users_attendances = AttendanceUser::select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'is_late', 'checked_out_by_system')
            ->join('long_lat_lists AS check_in_list', function ($join) {
                $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
            })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
            })->whereHas('user', function($q){
                $q->where('role', 1);
            })->with('user:id,name')->whereDate('check_in', '=', date("Y-m-d"))->get();
            foreach($users_attendances as $user_attendance){
                $user_attendance->geo_loc_check_in = json_decode($user_attendance->geo_loc_check_in);
                $user_attendance->geo_loc_check_out = json_decode($user_attendance->geo_loc_check_out);
            }
            $attendance_user_ids = $users_attendances->pluck('user_id')->unique()->values();
            $absent_users = User::select('id','name', 'position')->with('attendanceForms:id,name', 'profileImage')->where('role', 1)->whereNotIn('id', $attendance_user_ids)->whereNull('deleted_at')->where('is_enabled', true)->get();
            $data = (object)[
                'users_attendances_count' => count($users_attendances),
                'absent_users_count' => count($absent_users),
                'users_attendances' => $users_attendances,
                'absent_users' => $absent_users,
            ];
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendancesClient($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $users_attendances = AttendanceUser::select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'is_late', 'checked_out_by_system')
            ->join('long_lat_lists AS check_in_list', function ($join) {
                $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
            })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
            })->whereHas('user', function($q){
                $q->where('role', 1)->where('company_id', auth()->user()->company_id);
            })->with('user:id,name')->whereDate('check_in', '=', date("Y-m-d"));

            $users_attendances = $users_attendances->get();

            $on_time_attendances = count($users_attendances->where('is_late', 0));
            $late_attendances = count($users_attendances->where('is_late', 1));

            $wfo_count = count($users_attendances->where('is_wfo', 1));
            $wfh_count = count($users_attendances->where('is_wfo', 0));

            foreach($users_attendances as $user_attendance){
                $user_attendance->geo_loc_check_in = json_decode($user_attendance->geo_loc_check_in);
                $user_attendance->geo_loc_check_out = json_decode($user_attendance->geo_loc_check_out);
            }
            $attendance_user_ids = $users_attendances->pluck('user_id')->unique()->values();
            $absent_users = User::select('id','name', 'position')->with('attendanceForms:id,name', 'profileImage')->where('role', 1)->where('company_id', auth()->user()->company_id)->whereNotIn('id', $attendance_user_ids)->whereNull('deleted_at')->where('is_enabled', true)->get();
            $data = (object)[
                'total_users' => count($users_attendances) + count($absent_users),
                'users_attendances_count' => count($users_attendances),
                'absent_users_count' => count($absent_users),
                'users_attendances' => $users_attendances,
                'absent_users' => $absent_users,
                'late_count' => $late_attendances,
                'on_time_count' => $on_time_attendances,
                'wfo_count' => $wfo_count,
                'wfh_count' => $wfh_count
            ];
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendancesUserMonthly($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $user_id = $request->user_id;
        $month = $request->month;
        $year = $request->year;

        $date_month = $year . '-' . $month;
        $startDate = Carbon::parse($date_month)->startOfMonth();
        $endDate = Carbon::parse($date_month)->endOfMonth();
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach($period as $date){
            $day = $date->format('w');
            $date_formatted = $date->format('Y-m-d');
            $attendances[]= [
                "day" => $day,
                "date" => $date_formatted,
                "attendance" => AttendanceUser::select('attendance_users.id', 'user_id', 'check_in','is_wfo', 'is_late')
                ->where('user_id', $user_id)->whereDate('check_in', $date)->first()
            ];
        }
        return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $attendances, "status" => 200];
    }

    public function getAttendancesUsersPaginate($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "is_hadir" => "boolean",
            "is_late" => "boolean",
            "sort_by" => "in:name",
            "sort_type" => "in:asc,desc"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $is_hadir = $request->is_hadir;

        $is_late = $request->is_late ?? NULL;
        $is_on_time = $request->is_on_time ?? NULL;

        $is_wfh = $request->is_wfh ?? NULL;
        $is_wfo = $request->is_wfo ?? NULL;

        $keyword = $request->keyword ?? NULL;
        $keyword_role = $request->keyword_role ?? NULL;
        $rows = $request->rows ?? NULL;
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type ?? NULL;
        $company_ids = $request->company_ids ? explode(",",$request->company_ids) : NULL;
        // try{
            $current_time = date('Y-m-d');
            $users_attendances = User::select("id","name", "company_id", "position")->with(["attendance_user" => function($q) use($current_time, $is_late){
                $q->select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'is_late', 'checked_out_by_system')
                ->whereDate("check_in","=",$current_time)
                ->join('long_lat_lists AS check_in_list', function ($join) {
                    $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
                })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                    $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
                });
            }, "profileImage", "company"])->withCount("project_tasks");

            //filter
            if($keyword) $users_attendances = $users_attendances->where("name","LIKE","%$keyword%");
            if($keyword_role) $users_attendances = $users_attendances->where("position","LIKE","%$keyword_role%");



            $users_attendances = $users_attendances->where(function($q) use($current_time, $is_late, $is_on_time, $is_wfh, $is_wfo, $is_hadir){
                if($is_late || $is_on_time || $is_wfh || $is_wfo || $is_hadir == 1){
                    $q->whereHas("attendance_user", function($q) use($current_time, $is_late, $is_on_time, $is_wfh, $is_wfo){
                        if($is_late && $is_on_time) ;
                        elseif($is_late != NULL) $q->where("is_late",$is_late);
                        elseif($is_on_time != NULL) $q->where("is_late",0);

                        if($is_wfh && $is_wfo);
                        elseif($is_wfh) $q->where("is_wfo", 0);
                        elseif($is_wfo)$q->where("is_wfo", 1);
                        $q->whereDate("check_in","=",$current_time);
                    });
                }
                elseif(!$is_hadir && $is_hadir !== NULL){
                    $q->orWhereDoesntHave("attendance_user", function($q) use($current_time){
                        $q->whereDate("check_in","=",$current_time);
                        return $q;
                    });
                }
                return $q;
            });
            // else $users_attendances = $users_attendances->whereDoesntHave("attendance_user", function($q) use($current_time){
            //     $q->whereDate("check_in","=",$current_time);
            //     return $q;
            // });



            if($company_ids) $users_attendances = $users_attendances->whereIn("company_id", $company_ids);

            //sort
            if($sort_by == "name") $users_attendances = $users_attendances->orderBy("name",$sort_type);

            // dd($users_attendances->get());
            $users_attendances = $users_attendances->paginate($rows);

            foreach($users_attendances as $user_attendance){
                if($user_attendance->attendance_user != NULL){
                    $user_attendance->attendance_user->geo_loc_check_in = json_decode($user_attendance->attendance_user->geo_loc_check_in);
                    $user_attendance->attendance_user->geo_loc_check_out = json_decode($user_attendance->attendance_user->geo_loc_check_out);
                }
            }
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $users_attendances, "status" => 200];
        // } catch(Exception $err){
        //     return ["success" => false, "message" => $err, "status" => 400];
        // }
    }

    public function getAttendancesUser($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;
            $rows = $request->get('rows', 10);
            $is_all = $request->get('is_all', 0);
            $is_wfo = $request->get('is_wfo', null);
            $is_late = $request->get('is_late', null);
            $sort_type = $request->get('sort_type', 0);
            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;

            // $user_attendances = AttendanceUser::where('user_id', $login_id)->whereDate('check_in', '>', date("Y-m-d", strtotime("-1 months")))->orderBy('check_in', 'asc')->get();
            $user_attendances = AttendanceUser::with('evidence:link,description,fileable_id,fileable_type')->select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'is_late', 'checked_out_by_system')
            ->join('long_lat_lists AS check_in_list', function ($join) {
                $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
            })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
            })->where('user_id', $login_id)
            ->whereDate('check_in', '>', date("Y-m-d", strtotime("-1 months")));

            if($is_all){
                $user_attendances = $user_attendances->orderBy('check_in', 'desc')->get();
            } else {
                $params = "?rows=$rows";
                if($is_wfo) $params = "$params&is_wfo=$is_wfo";
                if($is_late) $params = "$params&is_late=$is_late";
                if($sort_type) $params = "$params&sort_type=$sort_type";

                if($is_wfo !== null) $user_attendances = $user_attendances->where('is_wfo', $is_wfo);
                if($is_late !== null) $user_attendances = $user_attendances->where('is_late', $is_late);
                if($sort_type) $user_attendances = $user_attendances->orderBy('check_in', 'asc');
                else $user_attendances = $user_attendances->orderBy('check_in', 'desc');

                $user_attendances = $user_attendances->paginate($rows);
                $user_attendances->withPath(env('APP_URL').'/getAttendancesUser'.$params);
            }

            $is_late_count = 0;
            $on_time_count = 0;
            if($user_attendances->isEmpty()){
                $data = (object)[
                    "late_count" => $is_late_count,
                    "on_time_count" => $on_time_count,
                    "user_attendances" => $user_attendances
                ];
                return ["success" => true, "message" => "Data Attendances Masih Kosong", "data" => $data, "status" => 200];
            }

            $is_late_days = [];
            foreach($user_attendances as $user_attendance){
                $attendance_day = date('m-d', strtotime($user_attendance->check_in));
                $is_late_days[$attendance_day] = $user_attendance->is_late;
                $user_attendance->geo_loc_check_in = json_decode($user_attendance->geo_loc_check_in);
                $user_attendance->geo_loc_check_out = json_decode($user_attendance->geo_loc_check_out);
            }

            foreach($is_late_days as $is_late_day){
                if($is_late_day) $is_late_count++;
                else $on_time_count++;
            }

            $data = (object)[
                "late_count" => $is_late_count,
                "on_time_count" => $on_time_count,
                "user_attendances" => $user_attendances
            ];
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceLateCount($request, $route_name, $admin = false){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->get('id');

            $user_attendances = AttendanceUser::where('user_id', $id)->whereDate('check_in', '>', date("Y-m-d", strtotime("-1 months")))->get();

            $is_late_count = 0;
            $on_time_count = 0;
            if($user_attendances->isEmpty()){
                $data = (object)[
                    "late_count" => $is_late_count,
                    "on_time_count" => $on_time_count,
                ];
                return ["success" => true, "message" => "Data Attendances Masih Kosong", "data" => $data, "status" => 200];
            }

            $is_late_days = [];
            foreach($user_attendances as $user_attendance){
                $attendance_day = date('m-d', strtotime($user_attendance->check_in));
                $is_late_days[$attendance_day] = $user_attendance->is_late;
                $user_attendance->geo_loc_check_in = json_decode($user_attendance->geo_loc_check_in);
                $user_attendance->geo_loc_check_out = json_decode($user_attendance->geo_loc_check_out);
            }

            foreach($is_late_days as $is_late_day){
                if($is_late_day) $is_late_count++;
                else $on_time_count++;
            }

            $data = (object)[
                "late_count" => $is_late_count,
                "on_time_count" => $on_time_count
            ];
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    public function getAttendanceUserFunc($request, $route_name, $admin = false)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;

            $id = $request->get('id');
            $data_user = AttendanceUser::join('users', 'users.id', '=', 'attendance_users.user_id')->where('attendance_users.id',$request->get('id'))->select('users.name','users.position')->first();
            $name = $data_user->name;
            $user_attendance = AttendanceUser::with('evidence:link,description,fileable_id,fileable_type')->select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'is_late', 'checked_out_by_system')
            ->join('long_lat_lists AS check_in_list', function ($join) {
                $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
            })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
            })->find($id);
            if(!$user_attendance) return ["success" => false, "message" => "User Attendance Tidak Ditemukan" , "status" => 400];
            if($user_attendance->user_id !== $login_id && !$admin) return ["success" => false, "message" => "User Attendance Bukan Milik User Login" , "status" => 400];

            $user_attendance->geo_loc_check_in = json_decode($user_attendance->geo_loc_check_in);
            $user_attendance->geo_loc_check_out = json_decode($user_attendance->geo_loc_check_out);
            $user_attendance->name = $name;
            $attendance_activities = AttendanceActivity::with('attendanceForm:id,name,details')->where('user_id', $user_attendance->user_id)->whereDate('updated_at', '=', date('Y-m-d', strtotime($user_attendance->check_in)))->get();
            $attendance_task_activities = AttendanceTaskActivity::with(['task', 'taskExport'])->where('user_id', $user_attendance->user_id)->whereDate('updated_at', '=', date('Y-m-d', strtotime($user_attendance->check_in)))->get();
            $attendance_activities_count = count($attendance_activities);
            $attendance_task_activities_count = count($attendance_task_activities);
            $activities_count = $attendance_task_activities_count + $attendance_activities_count;
            $data = (object)[
                "user_attendance" => $user_attendance,
                "attendance_activities" => $attendance_activities,
                "attendance_task_activities" => $attendance_task_activities,
                "activities_count" => $activities_count
            ];
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendance", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceUser($request, $route_name){
        return $this->getAttendanceUserFunc($request, $route_name);
    }

    public function getAttendanceUserAdmin($request, $route_name){
        return $this->getAttendanceUserFunc($request, $route_name, true);
    }

    private function addCheckEvidence($id, $file, $description)
    {
        $fileService = new FileService;
        $table = 'App\AttendanceUser';
        $folder_detail = 'UserAttendances';
        $add_file_response = $fileService->addFile($id, $file, $table, $description, $folder_detail, true);
    }

    private function check_is_late($current_timestamp, $login_id)
    {
        $date_time_split = explode(' ', $current_timestamp);
        $today_user_attendance = AttendanceUser::select('id', 'user_id', 'check_in', 'is_late')->where('user_id', $login_id)->whereDate('check_in', '=', $date_time_split[0])->first();
        $user_company_id = auth()->user()->company_id;
        $company = Company::find($user_company_id);

        //* get check in time
        if($company->id == 1) $check_in_time = $company->check_in_time;
        else if($company->role == 3) {
            $top_parent_company = Company::find(1);
            $check_in_time = $top_parent_company->check_in_time;
        }
        else{
            $top_parent_id = $company->getTopParent()->id;
            if($top_parent_id == null) $check_in_time = $company->check_in_time;
            else {
                $top_parent_company = Company::find($top_parent_id);
                $check_in_time = $top_parent_company->check_in_time;
            }
        }

        if($today_user_attendance) return $today_user_attendance->is_late;
        else {
            if($check_in_time == null || $check_in_time == '00:00:00') return false;
            else if($date_time_split[1] > $check_in_time) return true;
            else return false;
        }
    }

    public function setAttendanceToggle($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;
            $lat = $request->get('lat');
            $long = $request->get('long');
            if(!$request->hasFile('evidence')) return ["success" => false, "message" => "Evidence belum diisi", "status" => 400];
            if(!$lat) return ["success" => false, "message" => "Lat belum diisi", "status" => 400];
            if(!$long) return ["success" => false, "message" => "Long belum diisi", "status" => 400];
            $user_attendance = AttendanceUser::where('user_id', $login_id)->orderBy('check_in', 'desc')->first();
            $lat = number_format($lat, 4);
            $long = number_format($long, 4);

            $long_lat = LongLatList::where('longitude', $long)->where('latitude', $lat)->first();
            if(!$long_lat) $long_lat = LongLatList::create(['longitude' => $long, 'latitude' => $lat, 'attempts' => 0]);

            $file = $request->file('evidence');
            $current_timestamp = date('Y-m-d H:i:s');
            if(!$user_attendance || $user_attendance->check_out) {
                $is_late = $this->check_is_late($current_timestamp, $login_id);
                $user_attendance = new AttendanceUser;
                $user_attendance->user_id = $login_id;
                $user_attendance->long_check_in = $long;
                $user_attendance->lat_check_in = $lat;
                $user_attendance->check_in = $current_timestamp;
                $user_attendance->is_wfo = $request->get('wfo', false);
                $user_attendance->is_late = $is_late;
                $user_attendance->checked_out_by_system = false;
                $user_attendance->save();
                $this->addCheckEvidence($user_attendance->id, $file, "check_in_evidence");
                return ["success" => true, "message" => "Berhasil Check In", "status" => 200];
            } else {
                $today_attendance_activities = AttendanceActivity::where('user_id', $login_id)->whereDate('updated_at', '=', date("Y-m-d"))->get();
                $today_attendance_task = AttendanceTaskActivity::where('user_id', $login_id)->whereDate('updated_at', '=', date("Y-m-d"))->get();
                if(!count($today_attendance_activities) && !count($today_attendance_task)) return ["success" => false, "message" => "Tidak Bisa Melakukan Check Out Saat Aktivitas Belum Terisi" , "status" => 400];
                $user_attendance->check_out = $current_timestamp;
                $user_attendance->long_check_out = $long;
                $user_attendance->lat_check_out = $lat;
                $user_attendance->save();
                $this->addCheckEvidence($user_attendance->id, $file, "check_out_evidence");
                return ["success" => true, "message" => "Berhasil Check Out", "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Attendance Project
    public function getAttendanceProjects($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;
            $projects = AttendanceProject::where('user_id', $login_id)->get();
            if($projects->isEmpty()) return ["success" => true, "message" => "Project Belum dibuat", "data" => [], "status" => 200];
            return ["success" => true, "message" => "Projects Berhasil Diambil", "data" => $projects, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceProject($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $project = new AttendanceProject;
        $project->user_id = auth()->user()->id;
        $project->name = $request->get('name');
        $project->description = $request->get('description');
        $project->project_code = $request->get('project_code');
        $project->attendance_project_category = $request->get('attendance_project_category');
        $project->attendance_project_type = $request->get('attendance_project_type');
        try{
            $project->save();
            return ["success" => true, "message" => "Project berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceProject($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $login_id = auth()->user()->id;
        $project = AttendanceProject::find($id);
        if($project === null || $project->user_id !== $login_id) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $project->name = $request->get('name');
        $project->description = $request->get('description');
        $project->project_code = $request->get('project_code');
        $project->attendance_project_category = $request->get('attendance_project_category');
        $project->attendance_project_type = $request->get('attendance_project_type');
        try{
            $project->save();
            return ["success" => true, "message" => "Project berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceProject($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project = AttendanceProject::find($id);
        if($project === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $project->delete();
            return ["success" => true, "message" => "Project berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Attendance Project Type
    public function getAttendanceProjectTypes($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $project_types = AttendanceProjectType::get();
            if($project_types->isEmpty()) return ["success" => true, "message" => "Project Type Belum dibuat", "data" => [], "status" => 200];
            return ["success" => true, "message" => "Project Types Berhasil Diambil", "data" => $project_types, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceProjectType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $project_type = new AttendanceProjectType;
        $project_type->name = $request->get('name');
        try{
            $project_type->save();
            return ["success" => true, "message" => "Project Type berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceProjectType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project_type = AttendanceProjectType::find($id);
        if($project_type === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $project_type->name = $request->get('name');
        try{
            $project_type->save();
            return ["success" => true, "message" => "Project Type berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceProjectType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project_type = AttendanceProjectType::find($id);
        if($project_type === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $project_type->delete();
            return ["success" => true, "message" => "Project Type berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Attendance Project Status
    public function getAttendanceProjectStatuses($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $project_statuses = AttendanceProjectStatus::get();
            if($project_statuses->isEmpty()) return ["success" => true, "message" => "Project Status Belum dibuat", "data" => [], "status" => 200];
            return ["success" => true, "message" => "Project Statuses Berhasil Diambil", "data" => $project_statuses, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceProjectStatus($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $project_status = new AttendanceProjectStatus;
        $project_status->name = $request->get('name');
        try{
            $project_status->save();
            return ["success" => true, "message" => "Project Status berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceProjectStatus($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project_status = AttendanceProjectStatus::find($id);
        if($project_status === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $project_status->name = $request->get('name');
        try{
            $project_status->save();
            return ["success" => true, "message" => "Project Status berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceProjectStatus($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project_status = AttendanceProjectStatus::find($id);
        if($project_status === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $project_status->delete();
            return ["success" => true, "message" => "Project Status berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Attendance Project Category
    public function getAttendanceProjectCategories($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $project_categories = AttendanceProjectCategory::get();
            if($project_categories->isEmpty()) return ["success" => true, "message" => "Project Category Belum dibuat", "data" => [], "status" => 200];
            return ["success" => true, "message" => "Project Categories Berhasil Diambil", "data" => $project_categories, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceProjectCategory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $project_category = new AttendanceProjectCategory;
        $project_category->name = $request->get('name');
        try{
            $project_category->save();
            return ["success" => true, "message" => "Project Category berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceProjectCategory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project_category = AttendanceProjectCategory::find($id);
        if($project_category === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $project_category->name = $request->get('name');
        try{
            $project_category->save();
            return ["success" => true, "message" => "Project Category berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceProjectCategory($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $project_category = AttendanceProjectCategory::find($id);
        if($project_category === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $project_category->delete();
            return ["success" => true, "message" => "Project Category berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getAndroidActivities($request, $route_name){
        $login_id = auth()->user()->id;
        $today = date('Y-m-d');

        $attendance_activities = AttendanceActivity::where('user_id', $login_id)->whereDate('updated_at', '=', $today)->get();
        $attendance_task_activities = AttendanceTaskActivity::with(['task', 'taskExport'])->where('user_id', $login_id)->whereDate('updated_at', '=', $today)->get();

        $data = (object)[
            "attendance_activities" => $attendance_activities,
            "attendance_task_activities" => $attendance_task_activities
        ];
        return ["success" => true, "message" => "Attendance Activities Berhasil Diambil", "data" => $data, "status" => 200];
    }

    public function getAttendanceTaskActivity($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $task_activity = AttendanceTaskActivity::with(['task', 'taskExport'])->find($id);
        if($task_activity === null) return ["success" => false, "message" => "Task Activity Tidak Ditemukan", "status" => 400];
        try{
            return ["success" => true, "message" => $task_activity, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getLine(), "status" => 400];
        }
    }

    public function getAttendanceTaskActivities($request, $route_name, $admin = false){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id') ?? NULL;
            $last_two_month = date("Y-m-d", strtotime("-2 months"));
            $today = date('Y-m-d');
            $login_id = auth()->user()->id;
            if($admin && $id) $login_id = $id;
            $today_attendance_activities = AttendanceTaskActivity::with(['task', 'taskExport'])->where('user_id', $login_id)->whereDate('updated_at', '=', $today)->get();
            $last_two_month_attendance_activities = AttendanceTaskActivity::with(['task', 'taskExport'])->where('user_id', $login_id)->whereDate('updated_at', '>', $last_two_month)->whereDate('updated_at', '<>', $today)->get();
            $data = (object)[
                "today_activities" => $today_attendance_activities,
                "last_two_month_activities" => $last_two_month_attendance_activities
            ];
            return ["success" => true, "message" => "Attendance Activities Berhasil Diambil", "data" => $data, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getUserAttendanceTaskActivitiesAdmin($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->getAttendanceTaskActivities($request, $route_name, true);
    }

    public function getAttendanceTaskActivitiesAdmin($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $rows = $request->rows ?? NULL;
            $id = $request->get('id');
            $user_attendance = AttendanceUser::find($id);
            if(!$user_attendance) return ["success" => false, "message" => "User Attendance Tidak Ditemukan" , "status" => 400];
            $data = AttendanceTaskActivity::with(['task','taskExport'])->where('user_id', $user_attendance->user_id)->whereDate('updated_at', '=', date('Y-m-d', strtotime($user_attendance->check_in)));
            if($rows) $data = $data->paginate($rows);
            else $data = $data->get();
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendance", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceTaskActivity($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $task_id = $request->get('task_id');
        $task = ProjectTask::find($task_id);
        if($task === null) return ["success" => false, "message" => "Task Tidak Ditemukan", "status" => 400];

        $login_id = auth()->user()->id;
        $today = date('Y-m-d');
        $activity_exists = AttendanceTaskActivity::with('task')->where('user_id', $login_id)->whereDate('updated_at', '=', $today)
        ->where('task_id', $task_id)->get();
        if(count($activity_exists) != 0) return ["success" => false, "message" => "Task sudah berada di list aktivitas hari ini", "status" => 400];

        try{
            $task_activity = new AttendanceTaskActivity();
            $task_activity->user_id = $login_id;
            $task_activity->task_id = $task_id;
            $task_activity->updated_at = date('Y-m-d H:i:s');
            $task_activity->activity = $task->name;
            $task_activity->save();
            return ["success" => true, "message" => "Task Activity berhasil di Import", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

    }

    public function addAttendanceTaskActivitySubmit($id){
        $task_id = $id;
        $task = Task::find($task_id);
        if($task === null) return ["success" => false, "message" => "Task Tidak Ditemukan", "status" => 400];

        $login_id = auth()->user()->id;
        $today = date('Y-m-d');
        $activity_exists = AttendanceTaskActivity::with('taskExport')->where('user_id', $login_id)->whereDate('updated_at', '=', $today)
        ->where('task_export_id', $task_id)->get();
        if(count($activity_exists) != 0) return ["success" => false, "message" => "Task sudah berada di list aktivitas hari ini", "status" => 400];

        try{
            $task_activity = new AttendanceTaskActivity();
            $task_activity->user_id = $login_id;
            $task_activity->task_export_id= $task_id;
            $task_activity->updated_at = date('Y-m-d H:i:s');
            $task_activity->activity = $task->name;
            $task_activity->save();
            return ["success" => true, "message" => "Task Activity berhasil di Import", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

    }

    public function addAttendanceTaskActivities($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $task_ids = $request->get('task_ids', []);
        try{
            foreach($task_ids as $task_id){
                $task = ProjectTask::find($task_id);
                if($task === null) return ["success" => false, "message" => "Task Tidak Ditemukan", "status" => 400];
                $login_id = auth()->user()->id;
                $today = date('Y-m-d');
                $activity_exists = AttendanceTaskActivity::with('task')->where('user_id', $login_id)->whereDate('updated_at', '=', $today)
                ->where('task_id', $task_id)->get();
                if(count($activity_exists) != 0) return ["success" => false, "message" => "Task Sudah Ditambahkan Hari ini", "status" => 400];
                $task_activity = new AttendanceTaskActivity();
                $task_activity->user_id = $login_id;
                $task_activity->task_id = $task_id;
                $task_activity->updated_at = date('Y-m-d H:i:s');
                $task_activity->activity = $task->name;
                $task_activity->save();
            }
            return ["success" => true, "message" => "Task Activities berhasil di Import", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

    }

    public function updateAttendanceTaskActivity($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $task_activity = AttendanceTaskActivity::find($id);
        if($task_activity === null) return ["success" => false, "message" => "Task Activity Tidak Ditemukan", "status" => 400];
        try{
            $task_activity->updated_at = date('Y-m-d H:i:s');
            $task_activity->activity = $request->get('activity');
            $task_activity->save();
            return ["success" => true, "message" => "Task berhasil di Update", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getLine(), "status" => 400];
        }
    }

    public function deleteAttendanceTaskActivity($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $task_activity = AttendanceTaskActivity::find($id);
        if($task_activity === null) return ["success" => false, "message" => "Task Tidak Ditemukan", "status" => 400];
        try{
            $task_activity->delete();
            return ["success" => true, "message" => "Task berhasil di Hapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getLine(), "status" => 400];
        }
    }
}
