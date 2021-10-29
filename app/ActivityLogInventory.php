<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogInventory extends Model
{
    public $timestamps = false;
    protected $casts = ['properties' => 'object'];
    protected $with = ['causer'];

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id', 'user_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan'
        ])->select('user_id', 'fullname');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'subject_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan'
        ])->select('id', 'model_id')->with('modelInventory:id,name');
    }
}
