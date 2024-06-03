<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Resume extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function scopeOnlyMaskingName($query)
    {
        return $query->select('*', DB::raw("CONCAT(
            SUBSTRING_INDEX(name, ' ', 1),
            IF(LENGTH(name) - LENGTH(REPLACE(name, ' ', '')) > 0, ' ', ''),
            IF(LENGTH(name) - LENGTH(REPLACE(name, ' ', '')) > 0,
              SUBSTRING( SUBSTRING_INDEX(name, ' ', -1), 1, 1),
              ''
            )
          ) AS name"));
    }

    public function profileImage()
    {
        return $this->morphOne(File::class, 'fileable')->select('id', 'link', 'description', 'fileable_id', 'fileable_type')->latest('id')->withDefault([
            'id' => 0,
            'link' => env('APP_ENV') . '/Users/default_user.png',
            'description' => "profile_image"
        ]);
    }

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
        return $this->hasMany(ResumeSkill::class, 'resume_id')->orderBy("display_order", "asc");
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

    function recruitment(): HasOne
    {
        return $this->hasOne(Recruitment::class, 'owner_id', 'owner_id');
    }

    function talentPool(): HasOne
    {
        return $this->hasOne(TalentPool::class, 'resume_id', 'id');
    }

    function lastEducation(): HasOne
    {
        return $this->hasOne(ResumeEducation::class, 'resume_id')->where('display_order', 1)->latest('id');
    }

    function lastExperience(): HasOne
    {
        return $this->hasOne(ResumeExperience::class, 'resume_id')->where('display_order', 1)->latest('id');
    }

    function lastAssessment(): HasOne
    {
        return $this->hasOne(ResumeAssessment::class, 'id', 'assessment_id');
    }
}
