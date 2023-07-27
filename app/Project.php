<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{   
    use SoftDeletes;
    public $timestamps = false;
    public function project_staffs()
    {
        return $this->belongsToMany(User::class, "projects_staffs")->select("users.id","users.name","users.position");
    }

    public function proposed_bys()
    {
        return $this->belongsToMany(User::class, "projects_proposes")->select("users.id","users.name","users.position");
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class);
    }

    public function tasks(){
        return $this->hasMany(ProjectTask::class);
    }

    public function categories(){
        return $this->belongsToMany(ProjectCategoryList::class, "project_categories")->select("project_category_lists.id","project_category_lists.name");
    }
    
}
