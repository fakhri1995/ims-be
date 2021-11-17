<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelInventory extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;
    protected $hidden = ['pivot'];

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withDefault([
            'id' => 0,
            'name' => 'Asset Tidak Ditemukan',
            'code' => '000',
            'deleted_at' => null
        ])->withTrashed()->select('id', 'name', 'code', 'deleted_at');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'model_id');
    }

    public function modelParts()
    {
        return $this->belongsToMany(ModelInventory::class, 'model_model_pivots', 'parent_id', 'child_id')->with('asset:id,name,code,required_sn,deleted_at','modelColumns','modelParts')->withTrashed();
    }

    public function modelParent()
    {
        return $this->belongsToMany(self::class, 'model_model_pivots', 'child_id', 'parent_id');
    }

    public function modelColumns()
    {
        return $this->hasMany(ModelInventoryColumn::class, 'model_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class)->withDefault([
            'id' => 0,
            'name' => 'Manufacturer Tidak Ditemukan',
            'deleted_at' => null
        ])->withTrashed();
    }
}
