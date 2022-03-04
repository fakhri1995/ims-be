<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceProject extends Model
{
    use SoftDeletes;

    public $timestamps = false;
}
