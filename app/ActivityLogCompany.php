<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogCompany extends Model
{
    public $timestamps = false;

    protected $with = ['ticketable', 'causer'];

    public function ticketable(){
        return $this->morphTo()->withDefault([
            'id' => 0,
            'name' => '-'
        ])->select('id', 'name')->withTrashed();
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan'
        ])->select('id', 'name');
    }
}
