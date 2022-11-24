<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContract extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }
}
