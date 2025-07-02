<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResumeEvaluation extends Model
{
    public $table = "resume_evaluations";
    public $timestamps = false;

    function resume(): HasOne
    {
        return $this->hasOne(Resume::class, 'id', 'resume_id');
    }

    function user(): HasOne
    {
        return $this->hasOne(Resume::class, 'id', 'evaluated_by');
    }
}
