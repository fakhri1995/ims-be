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
        return $this->hasOne(EmployeeContract::class, "id", "last_contract_id");
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

    public function user(){
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function last_month_payslip(){
        $lastDate = explode("-",date("Y-m",strtotime("-1 month")));
        $year = $lastDate[0]; //current month - 1
        $month = $lastDate[1];
        return $this->hasOne(EmployeePayslip::class, "employee_id", "id")->where(["year" => $year, "month" => $month]);
    }
}
