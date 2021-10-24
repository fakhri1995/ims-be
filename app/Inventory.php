<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    protected $hidden = ['pivot'];
    
    public function modelInventory()
    {
        return $this->belongsTo(ModelInventory::class, 'model_id')->withDefault([
            'id' => 0,
            'name' => 'Model Tidak Ditemukan',
            'asset_id' => 0,
            'deleted_at' => null,
            'asset' => (object)[
                'id' => 0,
                'name' => 'Asset Tidak Ditemukan',
                'deleted_at' => null
            ]
        ])->withTrashed()->select('id', 'name', 'asset_id', 'deleted_at');
    }

    public function statusCondition()
    {
        return $this->belongsTo(statusConditionInventory::class, 'status_condition');
    }

    public function statusUsage()
    {
        return $this->belongsTo(statusUsageInventory::class, 'status_usage');
    }

    public function locationInventory()
    {
        return $this->belongsTo(Company::class, 'location')->withDefault([
            'company_id' => 0,
            'company_name' => 'Perusahaan Tidak Ditemukan'
        ])->withTrashed()->select('company_id', 'company_name');
    }

    public function additionalAttributes()
    {
        return $this->belongsToMany(ModelInventoryColumn::class, 'inventory_values', 'inventory_id', 'model_inventory_column_id')->select('model_inventory_columns.id as id', 'model_inventory_columns.name', 'inventory_values.value as value')->withTrashed();
    }

    public function inventoryPart()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_inventory_pivots', 'parent_id', 'child_id')->select('inventories.id', 'inventories.model_id', 'inventories.inventory_name', 'inventories.mig_id', 'inventories.status_condition', 'inventories.status_usage', 'inventories.deleted_at')->with('statusCondition', 'statusUsage', 'modelInventory.asset');
    }

    public function inventoryParts()
    {
        return $this->inventoryPart()->with('inventoryParts')->withTrashed();
    }

    public function inventoryParent()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_inventory_pivots', 'child_id', 'parent_id')->select('inventories.id', 'inventories.model_id', 'inventories.inventory_name', 'inventories.mig_id', 'inventories.status_condition', 'inventories.status_usage');
    }

    public function inventoryRelationshipsWithoutInventory()
    {
        return $this->hasMany(RelationshipInventory::class, 'subject_id')->with(['relationshipAsset'])->whereHas('relationshipAsset', function($q){
            $q->where('relationship_assets.type_id', '<>', -4);
        });
    }

    public function inventoryRelationships()
    {
        return $this->hasMany(RelationshipInventory::class, 'subject_id')->with(['relationshipAsset']);
    }
}

// with(['relationshipAsset'=> function ($query) {
//             $query->where('relationship_assets.type_id', '<>', -4);
//         }]);

// ->whereHas('modelInventory', function($q) use ($asset_id){
//                 $q->where('model_inventories.asset_id', $asset_id);
//             });