<?php

namespace App\Exports;

use App\AttendanceActivity;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class ActivitiesExport implements FromView, WithTitle
{

    public function __construct($from, $to, $attendance_form, $multiple, $user_ids)
    {
        if($multiple){
            $this->activities = AttendanceActivity::with('user:id,name')->whereIn('user_id', $user_ids)->whereBetween('updated_at', [$from, $to])->where('attendance_form_id', $attendance_form->id)->orderBy('updated_at', 'desc')->get();
        } else $this->activities = AttendanceActivity::with('user:id,name')->where('user_id', auth()->user()->id)->whereBetween('updated_at', [$from, $to])->where('attendance_form_id', $attendance_form->id)->orderBy('updated_at', 'desc')->get();
        $this->attendance_form = $attendance_form;
    }

    public function view(): View
    {
        return view('excel.activities', [
            'activities' => $this->activities,
            'attendance_form' => $this->attendance_form
        ]);
    }

    public function title(): string
    {
        return 'Activities';
    }
}