<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;   
    protected $hidden = ['fileable_id', 'fileable_type', 'uploaded_by', 'created_at', 'updated_at', 'deleted_at'];
}
