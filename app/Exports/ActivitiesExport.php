<?php

namespace App\Exports;

use App\AttendanceActivity;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
class ActivitiesExport implements FromView, WithTitle
{

    public function __construct($from, $to, $attendance_form,$form_ids, $multiple, $user_ids)
    {
        if($multiple){
            $this->activities = AttendanceActivity::with('user:id,name')->whereIn('user_id', $user_ids)->whereBetween('updated_at', [$from, $to])->whereIn('attendance_form_id', $form_ids)->orderBy('updated_at', 'asc')->get();
        } else $this->activities = AttendanceActivity::with('user:id,name')->where('user_id', auth()->user()->id)->whereBetween('updated_at', [$from, $to])->where('attendance_form_id', $attendance_form->id)->orderBy('updated_at', 'asc')->get();
        $this->attendance_form = $attendance_form;
        $this->multiple = $multiple;
    }

    public function view(): View
    {

        if($this->multiple) {
            return view('excel.activities', [
            'activities' => $this->activities,
            'attendance_form' => $this->attendance_form
        ]);
        }
        else {
            return view('excel.activities2', [
            'activities' => $this->activities,
            'attendance_form' => $this->attendance_form
        ]); 
        }
        
    }

    public function title(): string
    {
        return 'Activities';
    }
}