<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruitmentStage extends Model
{
    public $timestamps = false;

    public function recruitments(){
        return $this->hasMany(Recruitment::class, 'recruitment_stage_id');
    }
}
