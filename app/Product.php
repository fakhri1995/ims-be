<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $table = 'products';

    public function attachment_product()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    
}