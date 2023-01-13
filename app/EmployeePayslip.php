<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayslip extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class, "employee_payslip_id");
    }
}
