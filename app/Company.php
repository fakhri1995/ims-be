<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes, LogsActivity;

    public $timestamps = false;
    protected static $logName = "Company";
    protected static $logOnlyDirty = true;
    protected static $logAttributes = ['singkatan', 'tanggal_pkp', 'penanggung_jawab', 'alamat', 'fax', 'email', 'website'];
}
