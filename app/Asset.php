<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;
    protected $hidden = [
        'parent_id',
    ];

    public function assetColumns()
    {
        return $this->hasMany(AssetColumn::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->select('id', 'name AS title', 'code AS key', 'code AS value', 'parent_id')->with('children');
    }

}
