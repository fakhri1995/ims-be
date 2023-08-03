<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractProductTemplate extends Model
{
    protected $casts = ['details' => 'object'];
    public $timestamps = false;

    public function service_template_values(){
        return $this->hasMany(ContractProductTemplateValue::class,"contract_id","contract_id");
    }
}
