<?php 

namespace App\Services;
use Exception;
use App\Task;
use App\Ticket;
use App\AttendanceUser;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AndroidService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function sendPushNotification($registrations_ids, $notification)
    {
        $this->client = new Client();
        $headers = [
            'Authorization' => 'key ='.env('KEY_ANDROID_FIREBASE'),
            'content-type' => 'application/json'
        ];

        $body = [
            'registration_ids' => $registrations_ids,
            'notification' => $notification
        ];
        
        $response = $this->client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
            'headers'  => $headers,
            'json' => $body
        ]);
    }

    public function getMainAndroid($request)
    {
        $status_tickets = Ticket::selectRaw('status, count(*) as status_count');
        $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $status_tickets = $status_tickets->groupBy('status')->get();
        // $status_tickets = Ticket::selectRaw('status, count(*) as status_count')->where("status","<","5")->groupBy('status')->get();
        // $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $sum_ticket = $status_tickets->sum('status_count');
        $list = [];
        for($i = 1; $i < 8; $i++){
            if($i === 5) continue;
            $search = $status_tickets->search(function($query) use($i){
                return $query->status == $i;
            });
            if($search !== false){
                $temp_list = $status_tickets[$search]; 
                $temp_list->status_name = $statuses[$i];
                $list[] = $temp_list;
            } else {
                $list[] = (object)["status" => $i, "status_count" => 0, "status_name" => $statuses[$i]]; 
            }
        }
            
        $ticket_statuses = (object)[
            "statuses" => $list,
            "sum_ticket" => $sum_ticket
        ];

        // $last_three_tickets = Ticket::select("*")->with(['type', 'ticketable:id,location_id', 'ticketable.location'])->where('status', '<', 5)->whereNotNull('deadline')->orderBy('deadline', 'asc')->limit(3)->get();
        $last_three_tickets = Ticket::select("*")->with(['type', 'ticketable:id,location_id', 'ticketable.location'])->where('status', '<', 5)->orderByRaw('ISNULL(deadline), raised_at ASC, deadline ASC')->limit(3)->get();
        foreach($last_three_tickets as $ticket){
            $ticket->status_name = $statuses[$ticket->status];
            $ticket->name = $ticket->type->code.'-'.$ticket->ticketable_id;
            $ticket->ticketable->location->full_location = $ticket->ticketable->location->fullSubNameWParentTopParent();
            $ticket->time_left = ucwords(Carbon::parse($ticket->deadline)->diffForHumans(null, true, false, 2));
            $ticket->makeHidden('type');
            $ticket->ticketable->location->makeHidden('topParent', 'role', 'parent', 'parent_id');
            if($ticket->deadline > date("Y-m-d H:i:s")) $ticket->passed_deadline = false;
            else $ticket->passed_deadline = true;
        }

        $login_id = auth()->user()->id;
        $task_ids = DB::table('task_user')->where('user_id', $login_id)->pluck('task_id');
        $status_list = DB::table('tasks')->whereIn('id', $task_ids)->select(DB::raw('status, count(*) as status_count'))->groupBy('status')->get();
        $status_list_name = ["-", "Overdue", "Open", "On progress", "On hold", "Completed", "Closed"];
        
        $list = new Collection();
        $active_task = 0;
        $sum_task = $status_list->sum('status_count');
        for($i = 1; $i < 7; $i++){
            $search = $status_list->search(function($query) use($i){
                return $query->status == $i;
            });

            if($search !== false){
                $temp_list = $status_list[$search]; 
                $temp_list->status_name = $status_list_name[$i];
                $temp_list->percentage = $sum_task !== 0 ? round(($status_list[$search]->status_count / $sum_task * 100), 2) : 0;
                $list->push($temp_list);
                if($i < 5) $active_task += $temp_list->status_count;
            } else {
                $list->push((object)["status" => $i, "status_count" => 0, "status_name" => $status_list_name[$i], "percentage" => 0]); 
            }

        }
        $task_statuses = (object)[
            "status_list" => $list,
            "sum_task" => $sum_task,
            "active_task" => $active_task,
        ];

        $last_three_tasks = Task::select('id', 'name', 'status', 'reference_id', 'created_at', 'deadline')->with(['reference:id,ticketable_id,ticketable_type', 'reference.type'])
            ->where(function ($query) use($login_id, $task_ids){
                // $query->where('created_by', $login_id)
                // ->orWhereIn('id', $task_ids);
                $query->WhereIn('id', $task_ids);
            })->where('status', '<', 5)->orderByRaw('ISNULL(deadline), deadline ASC')->limit(3)->get();

        if(count($last_three_tasks)){
            foreach($last_three_tasks as $task){
                $task->status_name = $statuses[$task->status];
                $task->time_left = ucwords(Carbon::parse($task->deadline)->diffForHumans(null, true, false, 2));
                if($task->reference) $task->reference_name = $task->reference->type->code.'-'.$task->reference->ticketable_id;
                else $task->reference_name = "-";
                if($task->deadline > date("Y-m-d H:i:s")) $task->passed_deadline = false;
                else $task->passed_deadline = true;
                $task->makeHidden('reference');
            } 
        }
        
        $user_attendance_form_ids = DB::table('attendance_form_user')->where('user_id', $login_id)->pluck('attendance_form_id');
        $attendance_forms = DB::table('attendance_forms')->select('id', 'name', 'description', 'details', 'updated_at')->whereIn('id', $user_attendance_form_ids)->get();
        $last_check_in = AttendanceUser::with('evidence')->where('user_id', $login_id)->orderBy('check_in', 'desc')->select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'checked_out_by_system')
        ->join('long_lat_lists AS check_in_list', function ($join) {
            $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
        })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
            $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
        })->where('user_id', $login_id)
        ->whereDate('check_in', '>', date("Y-m-d", strtotime("-1 months")))->orderBy('check_in', 'asc')->first();

        if(count($attendance_forms)) foreach($attendance_forms as $form) $form->details = json_decode($form->details);
        if($last_check_in) {
            $last_check_in->geo_loc_check_in = json_decode($last_check_in->geo_loc_check_in);
            $last_check_in->geo_loc_check_out = json_decode($last_check_in->geo_loc_check_out);
        } else {
            $last_check_in = (object)[
                "id" => 0,
                "user_id" => 0,
                "check_in" => "0000-00-00 00:00:00",
                "check_out" => "0000-00-00 00:00:00",
                "long_check_in" => "0.0000",
                "lat_check_in" => "0.0000",
                "long_check_out" => null,
                "lat_check_out" => null,
                "geo_loc_check_in" => null,
                "geo_loc_check_out" => null,
                "evidence" => (object)[],
                "is_wfo" => null,
                "checked_out_by_system" => 0
            ];
        }

        $attendance_activity_count = DB::table('attendance_activities')->where('user_id', $login_id)->whereDate('updated_at', date('Y-m-d'))->count();
        $attendance_task_activity_count = DB::table('attendance_task_activities')->where('user_id', $login_id)->whereDate('updated_at', date('Y-m-d'))->count();

        //Attendance Late & Present
        $user_attendances = AttendanceUser::with('evidence:link,description,fileable_id,fileable_type')->select('attendance_users.id', 'user_id', 'check_in', 'check_out','long_check_in', 'lat_check_in', 'long_check_out', 'lat_check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo', 'is_late', 'checked_out_by_system')
            ->join('long_lat_lists AS check_in_list', function ($join) {
                $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
            })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
            })->where('user_id', $login_id)
            ->whereDate('check_in', '>', date("Y-m-d", strtotime("-1 months"))
        )->get();

        $is_late_count = 0;
        $on_time_count = 0;

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

        $attendance_count = $is_late_count + $on_time_count;

        $data = (object)[
            "ticket" => $ticket_statuses,
            "last_three_tickets" => $last_three_tickets,
            "task" => $task_statuses,
            "last_three_tasks" => $last_three_tasks,
            "user" => (object)[
                "id" => auth()->user()->id,
                "image_profile" => auth()->user()->profileImage,
                "name" => auth()->user()->name,
                "company_id" => auth()->user()->company_id,
                "company_name" => auth()->user()->company->name,
            ],
            "attendanceForm" => $attendance_forms,
            "attendance_activity_count" => $attendance_activity_count,
            "attendance_task_activity_count" => $attendance_task_activity_count,
            "last_check_in" => $last_check_in,
            "attendance_count" => $attendance_count,
            "is_late_count" => $is_late_count,
            "on_time_count" => $on_time_count
        ];
        
        return ["success" => true, "message" => "Main Data Berhasil Diambil", "data" => $data, "status" => 200];
    }
}