<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CareerV2 extends Model
{
    use SoftDeletes;
    protected $table = 'career_v2';
    public $timestamps = false;
    public function experience(){
        return $this->belongsTo(CareerV2Experience::class, 'career_experience_id')
            ->select('id','min','max','str');
    }

    public function roleType(){
        return $this->belongsTo(CareerV2RoleType::class, 'career_role_type_id')
            ->select('id','name');
    }

    function question(){
        return $this->hasOne(CareerV2Question::class, 'career_id');
    }

    public function apply(){
        return $this->hasMany(CareerV2Apply::class, 'career_id');
    }

    function recruitmentRole(){
        return $this->belongsTo(RecruitmentRole::class, 'recruitment_role_id');
    }
}
