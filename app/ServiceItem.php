<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceItem extends Model
{
    public $timestamps = false;
    use SoftDeletes;
}

