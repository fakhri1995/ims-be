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
        return $this->belongsTo(CareerV2::class, 'career_id')->select([
            "id",
            "name",
            "slug",
            "career_role_type_id",
            "career_experience_id",
            "salary_min",
            "salary_max",
            "overview",
            "description",
            "qualification",
            "is_posted"
        ]);
    }

    public function status(){
        return $this->belongsTo(CareerV2ApplyStatus::class, 'career_apply_status_id');
    }
    

}
