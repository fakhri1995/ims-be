<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectStatus extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function projects(){
        return $this->hasMany(Project::class, 'status_id');
    }

    public function project_tasks(){
        return $this->hasMany(ProjectTask::class, 'status_id');
    }
}
