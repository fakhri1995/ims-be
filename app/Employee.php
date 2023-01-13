<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    public function contract()
    {
        return $this->hasOne(EmployeeContract::class, "employee_id", "id")->orderBy('contract_start_at','desc');
    }

    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class, "employee_id", "id");
    }

    public function inventories()
    {
        return $this->hasMany(EmployeeInventory::class, "employee_id", "id");
    }

    public function id_card_photo()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    
    public function payslips(){
        return $this->hasMany(EmployeePayslip::class, "employee_id", "id");
    }
}
