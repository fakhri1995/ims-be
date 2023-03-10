<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeBenefit extends Model
{
    protected $fillable = [
        'employee_contract_id',
        'employee_salary_column_id',
        'value'
    ];

    public function column()
    {
        return $this->hasOne(EmployeeSalaryColumn::class, "id", "employee_salary_column_id",);
    }
}
