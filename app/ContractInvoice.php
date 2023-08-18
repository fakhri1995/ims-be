<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractInvoice extends Model
{
    protected $casts = ['invoice_attribute' => 'object', 'service_attribute' => 'object'];
    public $timestamps = false;
    use SoftDeletes;

    public function contract_template(){
       return $this->hasOne(Contract::class,'id','contract_template_id');
    }

    public function service_attribute_values(){
        return $this->hasMany(ContractInvoiceProduct::class,'contract_invoice_id','id');
    }  
}
