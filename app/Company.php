<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $hidden = [
        'created_time', 'top_parent_id'
    ];

    public $timestamps = false;


    public function child()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name', 'parent_id');
    }

    public function allChild()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name as title', 'id as key', 'id as value', 'parent_id');
    }

    public function subChild()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->where('role', 4);
    }

    public function noSubChild()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->where('role', '<>', 4);
    }

    public function branchChild()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->where('role', 3);
    }

    public function clientChild()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->where('role', 2);
    }

    public function clientWithSubChild()
    {
        return $this->hasMany(self::class, 'parent_id')->select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->where('role', '<>', 3);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->select('id', 'name', 'parent_id');
    }

    public function subParent()
    {
        return $this->belongsTo(self::class, 'parent_id')->select('id', 'name', 'parent_id', 'role')->where('role', 4);
    }

    public function topParent()
    {
        return $this->belongsTo(self::class, 'top_parent_id')->select('id', 'name', 'parent_id');
    }

    public function allChildren()
    {
        return $this->allChild()->withCount('allChild')->with('allChildren');
    }
    
    public function noSubChildren()
    {
        return $this->noSubChild()->withCount('noSubChild')->with('noSubChildren');
    }

    public function branchChildren()
    {
        return $this->branchChild()->withCount('branchChild')->with('branchChildren');
    }

    public function clientChildren()
    {
        return $this->clientChild()->with('clientChildren');
    }

    public function clientWithSubChildren()
    {
        return $this->clientWithSubChild()->with('clientWithSubChildren');
    }

    public function relation()
    {
        return $this->hasMany(RelationshipInventory::class, 'connected_id')->with('relationshipAsset')->select('relationship_asset_id', 'connected_id', 'is_inverse', DB::raw('count(*) as total'))->whereHas('relationshipAsset', function($q){
            $q->where('relationship_assets.type_id', -3);
        })->groupBy('relationship_asset_id');
    }

    public function relationDirect()
    {
        return $this->hasMany(RelationshipInventory::class, 'detail_connected_id')->with('relationshipAsset')->select('relationship_asset_id', 'connected_id', 'is_inverse', DB::raw('count(*) as total'))->whereHas('relationshipAsset', function($q){
            $q->where('relationship_assets.type_id', -3);
        })->groupBy('relationship_asset_id');
    }

    // ->groupBy('relationship_assets.relationship_id', 'relationship_assets.is_inverse')
    // ->select('relationship_asset_id', DB::raw('count(*) as total'))
    public function getAllChildrenList()
    {
        $companies = new Collection();

        foreach ($this->child as $company) {
            $companies->push($company);
            $companies = $companies->merge($company->getAllChildrenList());
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

    public function fullName()
    {
        if($this->topParent){
            $name = $this->topParent->name.' - '.$this->name;
        } else {
            $name = $this->name;
        }
        return $name;
    }
}
