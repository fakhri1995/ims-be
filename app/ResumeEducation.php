<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResumeEducation extends Model
{
    public $table = "resume_educations";
    public $timestamps = false;

    function resume(): HasOne
    {
        return $this->hasOne(Resume::class, 'id', 'resume_id');
    }
}
