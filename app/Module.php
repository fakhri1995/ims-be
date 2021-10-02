<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;
    protected $casts = [
        'features' => 'array'
    ];
}
