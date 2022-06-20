<?php 

namespace App\Services;
use Exception;
use App\Ticket;
use App\AttendanceUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AndroidService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function getMainAndroid($request)
    {
        $status_tickets = Ticket::select(DB::raw('status, count(*) as status_count'));
        // if(!$admin){
        //     $company_user_login_id = auth()->user()->company_id;
        //     $status_tickets = $status_tickets->whereHas('creator', function($query) use ($company_user_login_id){
        //         $query->where('users.company_id', $company_user_login_id);
        //     });
        //     $statuses = ['-','Dalam Proses', 'Menunggu Staff', 'Dalam Proses', 'Dalam Proses', 'Completed', 'Selesai', 'Dibatalkan'];
        // } else 
        $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $status_tickets = $status_tickets->groupBy('status')->get();
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

        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $assigned_only = $request->get('assigned_only', null);
        $location = $request->get('location', null);
            
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
        $data = (object)[
            "ticket" => $ticket_statuses,
            "task" => $task_statuses,
            "user" => (object)[
                "id" => auth()->user()->id,
                "image_profile" => auth()->user()->profileImage,
                "name" => auth()->user()->name,
                "company_id" => auth()->user()->company_id,
                "company_name" => auth()->user()->company->name,
            ],
            "attendanceForm" => $attendance_forms,
            "attendance_activity_count" => $attendance_activity_count,
            "last_check_in" => $last_check_in
        ];
        
        return ["success" => true, "message" => "Main Data Berhasil Diambil", "data" => $data, "status" => 200];
    }
}