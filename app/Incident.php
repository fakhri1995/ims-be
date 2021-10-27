<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    public $timestamps = false;
    protected $casts = [
        'files' => 'array'
    ];
    use SoftDeletes;

    public function location()
    {
        return $this->belongsTo(Company::class, 'location')->select('company_id', 'company_name');
    }

    public function productType()
    {
        return $this->belongsTo(IncidentProductType::class, 'product_type');
    }
}
