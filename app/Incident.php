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
    protected $with = ['location', 'productType'];

    public function location()
    {
        return $this->belongsTo(Company::class, 'location_id')->withDefault([
            'id' => 0,
            'name' => 'Perusahaan Tidak Ditemukan'
        ])->select('id', 'name', 'top_parent_id')->with('topParent');
    }

    public function productType()
    {
        return $this->belongsTo(IncidentProductType::class, 'product_type');
    }

    public function ticket()
    {
        return $this->morphOne(Ticket::class, 'ticketable')->select('id', 'ticketable_id', 'ticketable_type', 'status_id')->with('status', 'type');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class)->with(['statusCondition', 'statusUsage', 'locationInventory', 'modelInventory.asset', 'additionalAttributes']);
    }
}
