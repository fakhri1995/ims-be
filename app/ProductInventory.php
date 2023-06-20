<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
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
        return $this->belongsTo(ProductInventoryCategory::class, 'category_id');
    }

    public function priceOptions()
    {
        return $this->belongsTo(ProductInventoryPriceOption::class, 'price_option_id');
    }
}
