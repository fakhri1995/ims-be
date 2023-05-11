<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogProjectTask extends Model
{
    public $timestamps = false;
    protected $table = "activity_log_projects_tasks";
    protected $casts = ['properties' => 'object'];
    protected $with = ['causer','causer.imageProfile'];

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan',
            'roles' => [
                "name" => "-"
            ]
        ])->select('id', 'name')->with('roles:name');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
