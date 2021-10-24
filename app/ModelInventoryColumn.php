<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelInventoryColumn extends Model
{
    use SoftDeletes;
    protected $hidden = ['pivot'];

    public $timestamps = false;
}
