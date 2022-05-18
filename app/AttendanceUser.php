<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceUser extends Model
{
    protected $casts = ['location' => 'object', 'evidence' => 'object'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class)->with('profileImage');
    }

    public function evidence()
    {
        return $this->morphMany('App\File', 'fileable');
    }
}
