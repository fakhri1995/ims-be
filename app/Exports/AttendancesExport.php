<?php

namespace App\Exports;

use App\AttendanceUser;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class AttendancesExport implements FromView, WithTitle
{

    public function __construct($from, $to, $multiple, $user_ids)
    {
        $current_timestamp = date("Y-m-d");
        if($multiple){
            $this->attendances = AttendanceUser::with('user:id,name')->select('attendance_users.id', 'user_id', 'check_in', 'check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo')->whereIn('user_id', $user_ids);
        } else $this->attendances = AttendanceUser::with('user:id,name')->select('attendance_users.id', 'user_id', 'check_in', 'check_out', 'check_in_list.geo_location as geo_loc_check_in', 'check_out_list.geo_location as geo_loc_check_out', 'is_wfo')->where('user_id', auth()->user()->id);
        $this->attendances = $this->attendances->whereBetween('check_in', [$from, $to])
        ->join('long_lat_lists AS check_in_list', function ($join) {
            $join->on('attendance_users.long_check_in', '=', 'check_in_list.longitude')->on('attendance_users.lat_check_in', '=', 'check_in_list.latitude');
        })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
            $join->on('attendance_users.long_check_out', '=', 'check_out_list.longitude')->on('attendance_users.lat_check_out', '=', 'check_out_list.latitude');
        })->orderBy('check_in', 'desc')->get();

        foreach($this->attendances as $user_attendance){
            $user_attendance->geo_loc_check_in = json_decode($user_attendance->geo_loc_check_in);
            $user_attendance->geo_loc_check_out = json_decode($user_attendance->geo_loc_check_out);
        }
    }

    public function view(): View
    {
        return view('excel.attendances', [
            'attendances' => $this->attendances
        ]);
    }

    public function title(): string
    {
        return 'Attendances';
    }
}