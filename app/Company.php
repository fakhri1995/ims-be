<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $hidden = [
        'created_time', 'top_parent_id'
    ];

    public $timestamps = false;

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->select('id', 'name', 'parent_id');
    }

    public function topParent()
    {
        return $this->belongsTo(self::class, 'top_parent_id')->select('id', 'name', 'parent_id');
    }

    public function getAllChildren()
    {
        $companies = new Collection();

        foreach ($this->children as $company) {
            $companies->push($company);
            $companies = $companies->merge($company->getAllChildren());
        }

        return $companies;
    }

    public function getTopParent()
    {
        if($this->parent_id === null) return null;
        if($this->parent_id === 1) return $this;
        $top_company = $this->parent;
        if($top_company->parent_id === 1) return $top_company;
        else return $top_company->getTopParent();
    }
}
