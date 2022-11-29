<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeInventory extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }

    public function devices()
    {
        return $this->hasMany(EmployeeDevice::class, "employee_inventory_id");
    }

    public function delivery_file()
    {
        return $this->morphOne(File::class, 'fileable')->where("description","employee_delivery_file");
    }

    public function return_file()
    {
        return $this->morphOne(File::class, 'fileable')->where("description","employee_return_file");;
    }
}
