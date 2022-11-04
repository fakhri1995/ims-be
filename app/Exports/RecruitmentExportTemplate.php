<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class RecruitmentExportTemplate implements FromView, WithTitle
{

    public function __construct()
    {
     
    }
    
    public function view(): View
    {
        return view('excel.recruitment_template');
    }

    public function title(): string
    {
        return 'Recruitment';
    }
}