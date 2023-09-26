<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayslip extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    protected $casts = [
        'gaji_pokok' => DBEncryption::class,
        'bpjs_ks' => DBEncryption::class,
        'bpjs_tk_jht' => DBEncryption::class,
        'bpjs_tk_jkk' => DBEncryption::class,
        'bpjs_tk_jkm' => DBEncryption::class,
        'bpjs_tk_jp' => DBEncryption::class,
        'pph21' => DBEncryption::class,
        'total_gross_penerimaan' => DBEncryption::class,
        'total_gross_pengurangan' => DBEncryption::class,
        'take_home_pay' => DBEncryption::class,
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class, "employee_payslip_id");
    }
}
