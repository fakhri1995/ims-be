<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceActivitiesExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;
    
    public function __construct($from = null, $to = null, $attendance_form, $multiple, $user_ids)
    {
        $current_timestamp = date("Y-m-d");
        if(!$from){
            $last_month_timestamp_times = strtotime($current_timestamp) - 2592000;
            $from = date("Y-m-d", $last_month_timestamp_times);
        }
        if(!$to){
            $current_timestamp_times = strtotime('+1 day');
            $to = date("Y-m-d", $current_timestamp_times);
        }
        $this->from = $from;
        $this->to = $to;
        $this->attendance_form = $attendance_form;
        $this->multiple = $multiple;
        $this->user_ids = $user_ids;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ActivitiesExport($this->from, $this->to, $this->attendance_form, $this->multiple, $this->user_ids);
        $sheets[] = new AttendancesExport($this->from, $this->to, $this->multiple, $this->user_ids);

        return $sheets;
    }
}