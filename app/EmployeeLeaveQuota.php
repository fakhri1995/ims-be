<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeaveQuota extends Model
{
    public $timestamps = false;
    use SoftDeletes;
}
