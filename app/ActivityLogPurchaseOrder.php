<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogPurchaseOrder extends Model
{
    public $timestamps = false;
    protected $hidden = ['connectable_id', 'connectable_type'];
    
    public function connectable(){
        return $this->morphTo();
    }
}
