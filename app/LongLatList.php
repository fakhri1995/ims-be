<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LongLatList extends Model
{
    public $timestamps = false;

    protected $casts = [
        'geo_location'        => 'object',
        'is_nearby_processed' => 'boolean',
        'raw_geo_location'    => 'object',
    ];

    protected $fillable = [
        'longitude',
        'latitude',
        'geo_location',
        'attempts',
        'is_nearby_processed',
        'parent_id',
        'raw_latitude',
        'raw_longitude',
        'raw_geo_location',
    ];

    public function parent()
    {
        return $this->belongsTo(LongLatList::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LongLatList::class, 'parent_id');
    }
}
