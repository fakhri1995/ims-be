<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceTaskActivity extends Model
{
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
