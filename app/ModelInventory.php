<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelInventory extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;
}
