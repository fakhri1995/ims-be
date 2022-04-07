<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LongLatList extends Model
{
    public $timestamps = false;
    protected $casts = ['geo_location' => 'object'];
    protected $fillable = ['longitude', 'latitude', 'attempts'];
}
