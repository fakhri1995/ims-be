<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    public $with = ['attachments'];

    public function location()
    {
        return $this->belongsTo(Company::class, 'location_id')->withDefault([
            'id' => 0,
            'name' => '-'
        ])->select('id','name','parent_id','top_parent_id','role')->with('topParent')->withTrashed();
    }

    public function assetType()
    {
        return $this->belongsTo(TicketDetailType::class, 'product_type')->with('taskType')->withTrashed();
    }

    // public function productType()
    // {
    //     return $this->belongsTo(IncidentProductType::class, 'product_type');
    // }

    public function ticket()
    {
        return $this->morphOne(Ticket::class, 'ticketable')->select('id', 'ticketable_id', 'ticketable_type', 'status')->with('type');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class)->with(['statusCondition', 'statusUsage', 'modelInventory.asset', 'additionalAttributes', 'locationInventory']);
    }

    public function attachments()
    {
        return $this->morphMany('App\File', 'fileable');
    }
}
