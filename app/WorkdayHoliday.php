<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkdayHoliday extends Model
{
    public function workday(){
        return $this->belongsTo(Workday::class);
    }
}
