<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSolutionDetail extends Model
{
    use SoftDeletes;

    public function formsolution()
    {
        return $this->belongsTo(FormSolution::class, "form_solution_id");
    }
}