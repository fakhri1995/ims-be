<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Workday extends Model
{
    protected $casts = [
        'schedule' => 'array',
    ];

    public function holidays(){
        return $this->belongsToMany(PublicHoliday::class, "workday_public_holiday_pivots", "workday_id", "holiday_id");
    }
}
