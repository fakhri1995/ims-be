<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryColumn extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logAttributes = ["*"];
    protected static $logName = "Inventory Column";
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Inventory Column data has been {$eventName}.";
    }

    public $timestamps = false;
}
