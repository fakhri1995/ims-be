<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryValue extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logAttributes = ["*"];
    protected static $logName = "Inventory Value";
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Inventory Value data has been {$eventName}.";
    }

    public $timestamps = false;
}
