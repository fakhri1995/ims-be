<?php

namespace App;

use App\Casts\DBEncryption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContract extends Model
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
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }

    public function employee_last_contract()
    {
        return $this->hasOne(Employee::class, "last_contract_id", "id");
    }

    public function role()
    {
        return $this->belongsTo(RecruitmentRole::class, "role_id")->select('id','role','alias');
    }

    public function contract_status()
    {
        return $this->belongsTo(RecruitmentRoleType::class, "contract_status_id");
    }

    public function contract_files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeBenefit::class, "employee_contract_id");
    }
}
