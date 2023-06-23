<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInventoryPriceOption extends Model
{
    public $timestamps = false;
    
    public function priceOptions()
    {
        return $this->hasMany(ProductInventory::class, 'price_option_id');
    }
}
