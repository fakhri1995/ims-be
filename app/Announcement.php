<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    public function thumbnailImage()
    {
        return $this->morphOne(File::class, 'fileable')->select('id', 'link', 'description', 'fileable_id', 'fileable_type')->latest('id')->withDefault([
            'id' => 0,
            'link' => env('APP_ENV') . '/Announcement/mig-announce-logo.png',
            'description' => "thumbnail_image"
        ]);
    }

    function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
