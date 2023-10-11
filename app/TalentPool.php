<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TalentPool extends Model
{
    use SoftDeletes;

    function resume(): HasOne
    {
        return $this->hasOne(Resume::class, 'id', 'resume_id');
    }

    function category(): HasOne
    {
        return $this->hasOne(TalentPoolCategoryList::class, 'id', 'talent_pool_category_id');
    }
}
