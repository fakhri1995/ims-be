<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TalentPoolShare extends Model
{
    use SoftDeletes;

    function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function category() : HasOne {
        return $this->hasOne(TalentPoolCategoryList::class, 'id', 'talent_pool_category_id');
    }
}
