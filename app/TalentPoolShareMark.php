<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TalentPoolShareMark extends Model
{
    use SoftDeletes;

    function requester() : HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
