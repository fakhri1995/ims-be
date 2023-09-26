<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * This Model for Employee Payslip Without Encrypiton
 *! Depreciated
 */

class EmployeePayslipOld extends Model
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
