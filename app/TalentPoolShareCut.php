<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TalentPoolShareCut extends Model
{
    use SoftDeletes;

    function talent() : HasOne {
        return $this->hasOne(TalentPool::class, 'id', 'talent_pool_id');
    }
}
