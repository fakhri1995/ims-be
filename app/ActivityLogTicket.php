<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogTicket extends Model
{
    public $timestamps = false;
    protected $casts = ['properties' => 'object'];
    protected $with = ['causer'];

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id')->with('profileImage')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan',
            'role' => 2,
        ])->select('id', 'name', 'role');
    }
}
