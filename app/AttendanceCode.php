<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceCode extends Model
{
    public function chargeCode(){
        return $this->belongsTo(ChargeCode::class);
    }
}
