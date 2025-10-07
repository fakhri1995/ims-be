<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Workday extends Model
{
    protected $casts = [
        'schedule' => 'array',
    ];

    public function holidays(){
        return $this->belongsToMany(PublicHoliday::class, "workday_public_holiday_pivots", "workday_id", "holiday_id")->select('date','name','is_cuti');
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function workdayHolidays(){
        return $this->hasMany(WorkdayHoliday::class)->select('name','from','to','workday_id');
    }
}
