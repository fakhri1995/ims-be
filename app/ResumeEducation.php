<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResumeEducation extends Model
{
    public $table = "resume_educations";
    public $timestamps = false;
    protected $appends = ['start_date_format', 'end_date_format'];

    function resume(): HasOne
    {
        return $this->hasOne(Resume::class, 'id', 'resume_id');
    }

    public function getStartDateFormatAttribute()
    {
        if (isset($this->attributes['start_date'])) {
            $data = $this->attributes['start_date'];
            return date('Y-m', strtotime($data));
        }
        return null;
    }
    public function getEndDateFormatAttribute()
    {
        if (isset($this->attributes['end_date'])) {
            $data = $this->attributes['end_date'];
            return date('Y-m', strtotime($data));
        }
        return null;
    }
}
