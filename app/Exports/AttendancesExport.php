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
            $this->attendances = AttendanceUser::with('user:id,name')->select('id', 'user_id', 'check_in', 'check_out', 'geo_loc_check_in', 'geo_loc_check_out', 'is_wfo')->whereIn('user_id', $user_ids);
        } else $this->attendances = AttendanceUser::with('user:id,name')->select('id', 'user_id', 'check_in', 'check_out', 'geo_loc_check_in', 'geo_loc_check_out', 'is_wfo')->where('user_id', auth()->user()->id);
        $this->attendances = $this->attendances->whereBetween('check_in', [$from, $to])->orderBy('check_in', 'desc')->get();
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