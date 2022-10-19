<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceActivitiesExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;
    
    public function __construct($from = null, $to = null, $attendance_form,$form_ids, $multiple, $user_ids)
    {
        if(!$from){
            $from = date("Y-m-d", strtotime("-1 months"));
        }
        if(!$to){
            $to = date("Y-m-d", strtotime('+1 day'));
        }
        $this->from = $from;
        $this->to = $to;
        $this->attendance_form = $attendance_form;
        $this->multiple = $multiple;
        $this->user_ids = $user_ids;
        $this->form_ids = $form_ids;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ActivitiesExport($this->from, $this->to, $this->attendance_form, $this->form_ids, $this->multiple, $this->user_ids);
        $sheets[] = new AttendancesExport($this->from, $this->to, $this->multiple, $this->user_ids);
        Log::info($sheets);
        return $sheets;
    }
}