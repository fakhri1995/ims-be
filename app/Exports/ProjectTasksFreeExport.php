<?php

namespace App\Exports;

use App\ProjectTask;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
class ProjectTasksFreeExport implements FromView, WithTitle
{

    public function __construct($from, $to)
    {
      $this->tasks = ProjectTask::with('status', 'task_staffs', 'categories')->whereBetween('end_date', [$from, $to])->whereNull("project_id")->get();
    }

    public function view(): View
    {
      return view('excel.project_tasks_free', [
        'tasks' => $this->tasks
      ]);
    }

    public function title(): string
    {
        return 'Tugas Bebas';
    }
}