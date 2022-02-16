<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseQC extends Model
{
    public $timestamps = false;

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseQCDetail()
    {
        return $this->hasMany(PurchaseQCDetail::class)->with('modelInventories:id,name', 'purchaseQCDetailParts', 'purchaseQCDetailAttributes');
    }
}
