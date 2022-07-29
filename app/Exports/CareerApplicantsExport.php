<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class CareerApplicantsExport implements FromView, WithTitle
{

    public function __construct($data, $column)
    {
        $this->data = $data;
        $this->column = $column;
    }
    
    public function view(): View
    {
        return view('excel.career_applicants', [
            'applicants' => $this->data,
            'column' => $this->column
        ]);
    }

    public function title(): string
    {
        return 'Applicants';
    }
}