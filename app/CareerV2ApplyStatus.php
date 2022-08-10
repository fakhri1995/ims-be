<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareerV2ApplyStatus extends Model
{
    public $timestamps = false;

    public function applicants()
    {
        return $this->hasMany(CareerV2Apply::class,"career_apply_status_id");
    }
}
