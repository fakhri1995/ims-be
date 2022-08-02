<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FirebaseAndroidToken extends Model
{
    protected $primaryKey = 'token';
    public $incrementing = false;
}
