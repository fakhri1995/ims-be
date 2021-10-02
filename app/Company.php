<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'company_id';

    protected $hidden = [
        'created_time'
    ];

    public $timestamps = false;
}
