<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractInvoiceTemplate extends Model
{
    protected $casts = ['details' => 'object'];
    public $timestamps = false;

    public function service_template(){
        return $this->hasOne(ContractProductTemplate::class, 'contract_id', 'contract_id');
    }
}
