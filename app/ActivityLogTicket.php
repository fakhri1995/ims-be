<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogTicket extends Model
{
    public $timestamps = false;
    protected $casts = [
        'properties' => 'object'
    ];
}
