<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    public function modelInventories()
    {
        return $this->belongsToMany(ModelInventory::class)->withPivot('quantity', 'price', 'warranty_period', 'warranty_descripition');
    }

    public function modelOrders(){
        return $this->modelInventories()->with('modelParts', 'modelColumns');
    }

    public function activityLogPurchaseOrders()
    {
        return $this->hasMany(ActivityLogPurchaseOrder::class)->with('connectable:id,name');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }
    
}
