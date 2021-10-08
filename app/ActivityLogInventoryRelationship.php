<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogInventoryRelationship extends Model
{
    public $timestamps = false;
    protected $casts = [
        'properties' => 'object'
    ];
}
