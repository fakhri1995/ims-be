<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInventoryCategory extends Model
{
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(ProductInventory::class, 'category_id', 'id');
    }
}
