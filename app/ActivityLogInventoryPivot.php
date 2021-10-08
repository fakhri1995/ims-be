<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogInventoryPivot extends Model
{
    public $timestamps = false;
    protected $casts = [
        'properties' => 'object'
    ];
}
