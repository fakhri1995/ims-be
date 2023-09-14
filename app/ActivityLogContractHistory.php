<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogContractHistory extends Model
{
    public $timestamps = false;
    protected $casts = ['properties' => 'object'];
    protected $with = ['causer', 'causer.profileImage'];

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan',
            'roles' => [
                "name" => "-"
            ]
        ])->select('id', 'name')->with('roles:name');
    }

    public function contract_history()
    {
        return $this->belongsTo(ContractHistory::class, 'contract_history_id');
    }
}
