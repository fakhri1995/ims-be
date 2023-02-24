<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskReport extends Model
{   
    use SoftDeletes;
    public $timestamps = false;

    public function task()
    {
        return $this->hasOne(Task::class,'id','task_id');
    }
}
