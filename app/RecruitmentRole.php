<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruitmentRole extends Model
{
    public $timestamps = false;

    public function recruitments(){
        return $this->hasMany(Recruitment::class, 'recruitment_role_id');
    }

    public function type(){
        return $this->belongsTo(RecruitmentRoleType::class, 'recruitment_role_type_id');
    }
}
