<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerV2Apply extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function resume()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function role()
    {
        return $this->belongsTo(CareerV2::class, 'career_id');
    }

    public function status(){
        return $this->belongsTo(CareerV2ApplyStatus::class, 'career_apply_status_id');
    }
    

}
