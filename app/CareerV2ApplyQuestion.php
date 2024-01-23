<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareerV2ApplyQuestion extends Model
{
    protected $casts = ["details" => "array"];
    public $timestamps = false;

    public function question(){
        return $this->belongsTo(CareerV2Question::class, 'career_question_id');
    }
}
