<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceVerification extends Model
{
    public function attendanceUser()
    {
        return $this->belongsTo(AttendanceUser::class);
    }
   public function supporting_file()
    {
        return $this->morphMany('App\File', 'fileable');
    }
}
