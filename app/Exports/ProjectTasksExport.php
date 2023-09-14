<?php

namespace App\Exports;

use App\ProjectTask;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class ProjectTasksExport implements FromView, WithTitle
{

    public function __construct($from, $to)
    {
      $this->tasks = ProjectTask::with('project', 'status', 'task_staffs', 'categories')->whereBetween('end_date', [$from, $to])->whereNotNull("project_id")->orderBy("project_id")->get();
    }

    public function view(): View
    {
      return view('excel.project_tasks', [
        'tasks' => $this->tasks
      ]);
    }

    public function title(): string
    {
        return 'Tugas - Proyek';
    }
}