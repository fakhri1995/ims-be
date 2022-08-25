<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResumeAssessment extends Model
{
    

    public function details(){
        return $this->hasMany(ResumeAssessmentDetail::class, 'assessment_id');
    }
}
