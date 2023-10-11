<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function educations()
    {
        return $this->hasMany(ResumeEducation::class, 'resume_id')->orderBy("display_order", "asc");
    }

    public function achievements()
    {
        return $this->hasMany(ResumeAchievement::class, 'resume_id')->orderBy("display_order", "asc");
    }

    public function certificates()
    {
        return $this->hasMany(ResumeCertificate::class, 'resume_id')->orderBy("display_order", "asc");
    }

    public function experiences()
    {
        return $this->hasMany(ResumeExperience::class, 'resume_id')->orderBy("display_order", "asc");
    }

    public function projects()
    {
        return $this->hasMany(ResumeProject::class, 'resume_id')->orderBy("display_order", "asc");
    }

    public function skills()
    {
        return $this->hasMany(ResumeSkill::class, 'resume_id');
    }

    public function trainings()
    {
        return $this->hasMany(ResumeTraining::class, 'resume_id')->orderBy("display_order", "asc");
    }

    public function assessment()
    {
        return $this->belongsTo(ResumeAssessment::class, 'assessment_id');
    }

    public function assessmentResults()
    {
        return $this->hasMany(ResumeAssessmentResult::class, 'resume_id');
    }

    public function summaries()
    {
        return $this->hasOne(ResumeSummary::class, 'resume_id');
    }

    function talentPool(): HasOne
    {
        return $this->hasOne(TalentPool::class, 'resume_id', 'id');
    }

    function lastEducation(): HasOne
    {
        return $this->hasOne(ResumeEducation::class, 'resume_id')->orderBy("display_order", "asc")->latest('id');
    }

    function lastExperience(): HasOne
    {
        return $this->hasOne(ResumeExperience::class, 'resume_id')->orderBy("display_order", "asc")->latest('id');
    }

    function lastAssessment(): HasOne
    {
        return $this->hasOne(ResumeAssessment::class, 'id');
    }
}
