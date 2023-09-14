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
        return $this->belongsToMany(User::class, "project_tasks_staffs")->select("users.id","users.name","users.position");;
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }
    public function categories(){
        return $this->belongsToMany(ProjectCategoryList::class, "project_task_categories")->select("project_category_lists.id","project_category_lists.name");
    }
}
