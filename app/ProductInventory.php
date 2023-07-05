<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductInventory extends Model
{
    use SoftDeletes;

    public function modelInventory()
    {
        return $this->belongsTo(ModelInventory::class, 'model_id')->withDefault([
            'id' => 0,
            'name' => 'Model Tidak Ditemukan',
            'asset_id' => 0,
            'required_sn' => false,
            'deleted_at' => null,
            'asset' => (object)[
                'id' => 0,
                'name' => 'Asset Tidak Ditemukan',
                'deleted_at' => null
            ]
        ])->withTrashed()->select('id', 'name', 'asset_id', 'required_sn', 'deleted_at')->withCount('inventories');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'model_id', 'model_id');
    }
}
