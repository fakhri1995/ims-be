<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function educations(){
        return $this->hasMany(ResumeEducation::class,'cv_id');
    }

    public function achievements(){
        return $this->hasMany(ResumeAchievement::class,'cv_id');
    }

    public function certificates(){
        return $this->hasMany(ResumeCertificate::class,'cv_id');
    }

    public function experiences(){
        return $this->hasMany(ResumeExperience::class,'cv_id');
    }

    public function projects(){
        return $this->hasMany(ResumeProject::class,'cv_id');
    }

    public function skills(){
        return $this->hasMany(ResumeSkill::class,'cv_id');
    }

    public function trainings(){
        return $this->hasMany(ResumeTraining::class,'cv_id');
    }

}
