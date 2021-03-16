<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logName = "Inventory";
    protected static $logOnlyDirty = true;
    protected static $logAttributes = ['asset_id', 'vendor_id', 'asset_code', 'mig_number', 'serial_number', 'model', 'invoice_label', 'status',
        'kepemilikan', 'kondisi', 'tanggal_beli', 'harga_beli', 'tanggal_efektif', 'depresiasi', 'nilai_sisa', 'nilai_buku', 'masa_pakai', 'lokasi',
        'departmen', 'service_point', 'gudang', 'used_by', 'managed_by'];
}
