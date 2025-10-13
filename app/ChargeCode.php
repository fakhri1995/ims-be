<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChargeCode extends Model
{
    public function attendanceCodes(){
        return $this->hasMany(AttendanceCode::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
