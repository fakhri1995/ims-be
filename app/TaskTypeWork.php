<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskTypeWork extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'type', 'task_type_id', 'details'];
    protected $casts = ['details' => 'object'];
}
