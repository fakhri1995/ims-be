<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryInventoryPivot extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logName = "Inventory Pivot";
    protected static $logOnlyDirty = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['deleted_at'];

    public $timestamps = false;
}
