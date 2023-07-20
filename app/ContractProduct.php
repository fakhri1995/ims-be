<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractProduct extends Model
{   

    public $timestamps = false;
    
    public function product(){
        return $this->hasOne(ProductInventory::class,'id','product_id')->select("id","name");
    }
}
