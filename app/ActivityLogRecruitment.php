<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLogRecruitment extends Model
{
    public $timestamps = false;
    protected $casts = ['properties' => 'object'];
    protected $with = ['causer'];

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
}
