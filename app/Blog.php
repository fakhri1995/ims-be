<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;
    protected $table = 'blogs';

    public function attachment_article()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    
}