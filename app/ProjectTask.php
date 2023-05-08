<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function task_staffs()
    {
        return $this->belongsToMany(User::class, "project_tasks_staffs");
    }

    public function status()
    {
        return $this->hasOne(ProjectStatus::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }
}
