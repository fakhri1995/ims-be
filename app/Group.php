<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logAttributes = ["*"];
    protected static $logName = "Group";
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Group data has been {$eventName}.";
    }

    public $timestamps = false;
}
