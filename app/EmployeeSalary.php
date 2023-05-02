<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    protected $fillable = [
        'employee_payslip_id',
        'employee_salary_column_id',
        'value',
        'is_amount_for_bpjs'
    ];

    public function column()
    {
        return $this->hasOne(EmployeeSalaryColumn::class, "id", "employee_salary_column_id",)->withTrashed();
    }
}
