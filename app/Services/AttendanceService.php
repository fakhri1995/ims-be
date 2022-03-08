<?php 

namespace App\Services;
use App\User;
use Exception;
use App\AttendanceForm;
use App\AttendanceUser;
use App\AttendanceProject;
use App\AttendanceActivity;
use Illuminate\Support\Str;
use App\AttendanceProjectType;
use App\AttendanceProjectStatus;
use App\AttendanceProjectCategory;

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
            
            if($keyword) $attendance_forms = $attendance_forms->where('name', 'like', "%".$keyword."%");
            if($sort_by){
                if($sort_type === null) $sort_type = 'desc';
                if($sort_by === 'name') $attendance_forms = $attendance_forms->orderBy('name', $sort_type);
                else if($sort_by === 'description') $attendance_forms = $attendance_forms->orderBy('description', $sort_type);
                else if($sort_by === 'updated_at') $attendance_forms = $attendance_forms->orderBy('updated_at', $sort_type);
                else if($sort_by === 'count') $attendance_forms = $attendance_forms->orderBy('users_count', $sort_type);
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
            $attendance_form = AttendanceForm::with(['users:id,name,profile_image,position', 'creator:id,name,profile_image,position'])->find($id);
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

        $attendance_form = new AttendanceForm;
        $attendance_form->name = $request->get('name');
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
            $today_attendance_activities = AttendanceActivity::whereDate('updated_at', '=', $today)->get();
            $last_two_month_attendance_activities = AttendanceActivity::whereDate('updated_at', '>', $last_two_month)->whereDate('updated_at', '<>', $today)->get();
            $data = (object)[
                "today_activities" => $today_attendance_activities,
                "last_two_month_activities" => $last_two_month_attendance_activities
            ];
            return ["success" => true, "message" => "Attendance Activities Berhasil Diambil", "data" => $data, "status" => 200];

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

    public function addAttendanceActivity($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $attendance_form_id = $request->get('attendance_form_id');
        $attendance_form = AttendanceForm::find($attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Form Tidak Ditemukan", "status" => 400];
        $activity_details = $request->get('details', []);
        foreach($attendance_form->details as $form_detail){
            $search = array_search($form_detail['key'], array_column($activity_details, 'key'));
            if($search === false) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum diisi" , "status" => 400];
            if(!isset($activity_details[$search]['value'])) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum memiliki value" , "status" => 400];
            if($form_detail['type'] === 3){
                if(gettype($activity_details[$search]['value']) !== "array") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe array", "status" => 400];
            } else {
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
        $attendance_form = AttendanceForm::find($attendance_activity->attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Form Tidak Ditemukan", "status" => 400];
        $activity_details = $request->get('details', []);
        foreach($attendance_form->details as $form_detail){
            $search = array_search($form_detail['key'], array_column($activity_details, 'key'));
            if($search === false) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum diisi" , "status" => 400];
            if(!isset($activity_details[$search]['value'])) return ["success" => false, "message" => "Detail aktivitas dengan nama ".$form_detail['name']." belum memiliki value" , "status" => 400];
            if($form_detail['type'] === 3){
                if(gettype($activity_details[$search]['value']) !== "array") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe array", "status" => 400];
            } else {
                if(gettype($activity_details[$search]['value']) !== "string") return ["success" => false, "message" => "Value pada detail aktivitas dengan nama ".$form_detail['name']." harus bertipe string", "status" => 400];
            }
        }
        $attendance_activity->updated_at = date('Y-m-d H:i:s');
        $attendance_activity->details = $activity_details;
        try{
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
    
    // Attendance User
    public function getAttendancesUser($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;
            $user_attendances = AttendanceUser::where('user_id', $login_id)->orderBy('check_in', 'desc')->get();
            return ["success" => true, "message" => "Berhasil Mengambil Data Attendances", "data" => $user_attendances, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
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
            $geo_loc = $request->get('geo_loc');
            $evidence = $request->get('evidence');
            if(!$evidence) return ["success" => false, "message" => "Evidence belum diisi", "status" => 400];
            $user_attendance = AttendanceUser::where('user_id', $login_id)->orderBy('check_in', 'desc')->first();
            if(!$user_attendance || $user_attendance->check_out) {
                $evidence = (object) [
                    "check_in_evidence" => $evidence,
                    "check_out_evidence" => null
                ];
                $user_attendance = new AttendanceUser;
                $user_attendance->user_id = $login_id;
                $user_attendance->long_check_in = $long;
                $user_attendance->lat_check_in = $lat;
                $user_attendance->geo_loc_check_in = $geo_loc;
                $user_attendance->check_in = date('Y-m-d H:i:s');
                $user_attendance->is_wfo = $request->get('wfo', false);
                $user_attendance->evidence = $evidence;
                $user_attendance->save();
                return ["success" => true, "message" => "Berhasil Check In", "status" => 200];
            } else {
                $today_attendance_activities = AttendanceActivity::whereDate('updated_at', '=', date("Y-m-d"))->get();
                if(!count($today_attendance_activities)) return ["success" => false, "message" => "Tidak Bisa Melakukan Check Out Saat Aktivitas Belum Terisi" , "status" => 400];
                $evidence_temp = $user_attendance->evidence;
                $evidence_temp->check_out_evidence = $evidence;
                $user_attendance->check_out = date('Y-m-d H:i:s');
                $user_attendance->long_check_out = $long;
                $user_attendance->lat_check_out = $lat;
                $user_attendance->geo_loc_check_out = $geo_loc;
                $user_attendance->evidence = $evidence_temp;
                $user_attendance->save();
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
}