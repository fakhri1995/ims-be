<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractInvoice extends Model
{
    protected $casts = ['invoice_attribute' => 'object', 'service_attribute' => 'object'];
    public $timestamps = false;

    public function contract_template(){
       return $this->hasOne(Contract::class,'id','contract_template_id');
    }

    public function invoice_services(){
        return $this->hasMany(ContractInvoiceProduct::class,'contract_invoice_id','id');
     }
}
