<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    protected $casts = [
        'files' => 'array'
    ];
    protected $with = ['location'];

    public function location()
    {
        return $this->belongsTo(Company::class, 'location_id')->select('company_id', 'company_name');
    }

    public function productType()
    {
        return $this->belongsTo(IncidentProductType::class, 'product_type');
    }
}
