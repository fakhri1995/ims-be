<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceUser extends Model
{
    protected $casts = ['location' => 'object', 'evidence' => 'object'];
    public $timestamps = false;
}
