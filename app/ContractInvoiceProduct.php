<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractInvoiceProduct extends Model
{
    public $casts = ["details" => "object"];
    public $timestamps = false;
    public $fillable = ["contract_invoice_id","product_id","pax","price","unit","details","created_at","updated_at"];

    public function product(){
        return $this->hasOne(ProductInventory::class,'id','product_id')->select("id","name");
    }
}
