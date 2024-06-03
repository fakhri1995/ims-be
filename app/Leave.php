<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    public function document()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function type(){
        return $this->belongsTo(LeaveType::class, 'type');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->with('contract', 'contract.role');
    }

    public function delegate()
    {
        return $this->belongsTo(Employee::class, 'delegate_id')->with('contract', 'contract.role');
    }
}
