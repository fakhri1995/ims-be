<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerV2Question extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    protected $hidden = ['pivot'];
    protected $casts = ['details' => 'array'];
    
    function career(){
        return $this->belongsTo(CareerV2::class);
    }

    public function applies()
    {
        return $this->belongsToMany(CareerV2Apply::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->with('profileImage');
    }
}
