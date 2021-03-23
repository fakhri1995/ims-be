<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PivotServiceItem extends Model
{
    public $timestamps = false;
    use SoftDeletes;
}
