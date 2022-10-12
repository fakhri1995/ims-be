<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruitmentJalurDaftar extends Model
{
    public $timestamps = false;

    public function recruitments(){
        return $this->hasMany(Recruitment::class, 'recruitment_jalur_daftar_id');
    }
}
