<?php

namespace App\Exports;

use App\AttendanceTaskActivity;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
class TaskActivitiesExport implements FromView, WithTitle
{

    public function __construct($from, $to, $multiple, $user_ids)
    {
        if($multiple){
            $this->activities = AttendanceTaskActivity::with('user:id,name')->whereIn('user_id', $user_ids)->whereBetween('updated_at', [$from, $to])->orderBy('updated_at', 'asc')->get();
        } else $this->activities = AttendanceTaskActivity::with('user:id,name')->where('user_id', auth()->user()->id)->whereBetween('updated_at', [$from, $to])->orderBy('updated_at', 'asc')->get();
        $this->multiple = $multiple;
    }

    public function view(): View
    {

        if($this->multiple) {
            return view('excel.task_activities', [
            'activities' => $this->activities,
        ]);
        }
        else {
            return view('excel.task_activities', [
            'activities' => $this->activities,
        ]); 
        }
        
    }

    public function title(): string
    {
        return 'Task Activities';
    }
}