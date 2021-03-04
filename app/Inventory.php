<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logAttributes = ["*"];
    protected static $logName = "Inventory";
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Inventory data has been {$eventName}.";
    }
}
