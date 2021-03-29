<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessFeature extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logName = "Access Feature";
    protected static $logOnlyDirty = true;
    protected static $logAttributes = ['name', 'description', 'key'];

    public $timestamps = false;
}
