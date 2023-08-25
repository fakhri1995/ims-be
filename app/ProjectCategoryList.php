<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCategoryList extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    protected $hidden = [
        'pivot'
    ];

    public function companies(){
        return $this->belongsToMany(Company::class, "project_category_lists_companies")->select("companies.id","companies.name");
    }
}
