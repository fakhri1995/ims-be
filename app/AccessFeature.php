<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessFeature extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;
    protected $hidden = ['pivot', 'feature_id', 'feature_key', 'deleted_at'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_feature_pivots', 'feature_id', 'role_id');
    }
}
