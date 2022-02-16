<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;
    protected $hidden = [
        'parent_id',
    ];

    public function assetColumns()
    {
        return $this->hasMany(AssetColumn::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->select('id', 'name AS title', 'code AS key', 'code AS value', 'parent_id')->with('children');
    }

    public function fullName()
    {
        $asset_name = $this->name;
        if(strlen($this->code) > 3){
            $parent_model = substr($this->code, 0, 3);
            $parent_name = \App\Asset::where('code', $parent_model)->first();
            $parent_name = $parent_name === null ? "Asset Not Found" : $parent_name->name;
            $asset_name = $parent_name . " / ". $this->name;
        }
        return $asset_name;
    }

    public function getChildrenTreeList()
    {
        $assets = new Collection();
        foreach ($this->children as $asset) {
            $assets->push($asset);
            $assets = $assets->merge($asset->getChildrenTreeList());
        }
        return $assets;
    }

}
