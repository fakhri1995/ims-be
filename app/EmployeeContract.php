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

    public function role()
    {
        return $this->belongsTo(RecruitmentRole::class, "role_id")->select('id','name','alias');
    }

    public function contract_status()
    {
        return $this->belongsTo(RecruitmentRoleType::class, "contract_status_id");
    }

    public function contract_file()
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
