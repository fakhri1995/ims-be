<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceActivity extends Model
{
    protected $casts = ["details" => "array"];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceForm()
    {
        return $this->belongsTo(AttendanceForm::class);
    }

    public function attendanceProject()
    {
        return $this->belongsTo(AttendanceProject::class);
    }

    public function attendanceProjectStatus()
    {
        return $this->belongsTo(AttendanceProjectStatus::class);
        
    }
}
