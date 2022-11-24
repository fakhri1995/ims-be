<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class, "employee_id", "id");
    }

    public function inventories()
    {
        return $this->hasMany(EmployeeInventory::class, "employee_id", "id");
    }
    
}
