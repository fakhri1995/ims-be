<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    public function document()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function status(){
        return $this->belongsTo(OvertimeStatus::class, 'status_id');
    }

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->with('user');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
