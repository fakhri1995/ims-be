<?php

namespace App;

use App\Services\ContractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $casts = ['extras' => 'array'];


    public function client(){
        return $this->hasOne(Company::class,'id','client_id')->select('id','name');
    } 

    public function requester(){
        return $this->hasOne(User::class,'id','requester_id')->select('id','name');;
    }

    public function services(){
        return $this->hasMany(ContractProduct::class,'contract_id')->select()->addSelect(DB::raw('pax*price as subtotal'));
    }

    public function invoice_template(){
        return $this->hasOne(ContractInvoiceTemplate::class,'contract_id');
    }

    public function service_template(){
        return $this->hasOne(ContractProductTemplate::class,'contract_id');
    }

}
