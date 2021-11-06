<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;
    public $timestamps = false;
    protected $hidden = ['pivot'];
    protected $fillable = ['name', 'description'];

    public function features()
    {
        return $this->belongsToMany(AccessFeature::class, 'role_feature_pivots', 'role_id', 'feature_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role_pivots')->select('users.id','users.name');
    }
}
