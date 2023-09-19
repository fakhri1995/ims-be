<?php

namespace App\Exports;

use App\Project;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
class ProjectsDetailExport implements FromView, WithTitle
{

    public function __construct()
    {
      $this->projects = Project::with('status','categories', 'project_staffs')->withCount('tasks')->get();
    }

    public function view(): View
    {
      return view('excel.projects', [
        'projects' => $this->projects
      ]);
    }

    public function title(): string
    {
        return 'Proyek';
    }
}