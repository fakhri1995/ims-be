<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruitmentStatus extends Model
{
    public $timestamps = false;

    public function recruitments(){
        return $this->hasMany(Recruitment::class, 'recruitment_status_id');
    }
}
