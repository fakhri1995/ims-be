<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskType extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function works()
    {
        return $this->hasMany(TaskTypeWork::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function ticketTaskType()
    {
        return $this->hasMany(TicketTaskType::class);
    }
}
