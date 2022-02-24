<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceForm extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    protected $hidden = ['pivot'];
    protected $casts = ['details' => 'array'];
    
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
