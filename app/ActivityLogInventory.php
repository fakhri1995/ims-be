<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogInventory extends Model
{
    public $timestamps = false;
    protected $casts = [
        'properties' => 'object'
    ];
}
