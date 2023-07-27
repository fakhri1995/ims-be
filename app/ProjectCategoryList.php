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
}
