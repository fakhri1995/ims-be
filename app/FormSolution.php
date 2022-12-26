<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSolution extends Model
{
    use SoftDeletes;
    public function details()
    {
        return $this->hasMany(FormSolutionDetail::class,"form_solution_id", "id");
    }
}