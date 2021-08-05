<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelInventoryValue extends Model
{
    use SoftDeletes;
    //  LogsActivity;

    // protected static $logAttributes = ['inventory_id', 'inventory_column_id', 'value'];
    // protected static $logName = "Inventory Value";

    public $timestamps = false;
}
