<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDevice extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    public function inventory()
    {
        return $this->belongsTo(EmployeeInventory::class, "employee_inventory_id");
    }
}
