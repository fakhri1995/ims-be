<?php

namespace App\Exports;

use App\Services\GlobalService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectsExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;
    
    public function __construct($from = null, $to = null)
    {

        if(!$from){
            $from = date("Y-m-d", strtotime("-1 months"));
        }
        if(!$to){
            $to = date("Y-m-d", strtotime('+1 day'));
        }
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ProjectsDetailExport($this->from, $this->to);
        $sheets[] = new ProjectTasksExport($this->from, $this->to);
        $sheets[] = new ProjectTasksFreeExport($this->from, $this->to);
        return $sheets;
    }
}