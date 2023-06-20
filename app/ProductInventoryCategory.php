<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInventoryCategory extends Model
{
    public function category()
    {
        return $this->hasMany(ProductInventory::class, 'category_id');
    }
}
