<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractProduct extends Model
{

    public $timestamps = false;

    public function product(){
        return $this->hasOne(ProductInventory::class,'id','product_id')->select("id","name");
    }

    public function service_template_value(){
        return $this->hasOne(ContractProductTemplateValue::class,'contract_product_id','id');
    }
}
