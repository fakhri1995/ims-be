<?php

namespace App\Exports;

use App\ProjectTask;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class ProjectTasksExport implements FromView, WithTitle
{

    public function __construct()
    {
      $tasks = ProjectTask::with('project', 'status', 'task_staffs', 'categories')->whereNotNull("project_id")->orderBy("project_id")->get();
      $this->tasks = [];
      foreach($tasks as $task){
        $temp_task = $task;
        $temp_task->description = strip_tags(strval($task->description));
        $this->tasks[] = $temp_task;
      }
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