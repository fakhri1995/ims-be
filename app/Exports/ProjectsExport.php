<?php

namespace App\Exports;

use App\Services\GlobalService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectsExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;
    
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ProjectsDetailExport();
        $sheets[] = new ProjectTasksExport();
        $sheets[] = new ProjectTasksFreeExport();
        return $sheets;
    }
}