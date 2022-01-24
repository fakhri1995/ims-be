<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    public $timestamps = false;
    protected $hidden = ['pivot'];
    protected $fillable = ['model_id', 'vendor_id', 'status_condition', 'status_usage', 'location', 'deskripsi', 'manufacturer_id', 'mig_id', 'serial_number'];
    
    public function modelInventory()
    {
        return $this->belongsTo(ModelInventory::class, 'model_id')->withDefault([
            'id' => 0,
            'name' => 'Model Tidak Ditemukan',
            'asset_id' => 0,
            'required_sn' => false,
            'deleted_at' => null,
            'asset' => (object)[
                'id' => 0,
                'name' => 'Asset Tidak Ditemukan',
                'deleted_at' => null
            ]
        ])->withTrashed()->select('id', 'name', 'asset_id', 'required_sn', 'deleted_at');
    }

    public function statusCondition()
    {
        return $this->belongsTo(StatusConditionInventory::class, 'status_condition');
    }

    public function statusUsage()
    {
        return $this->belongsTo(StatusUsageInventory::class, 'status_usage');
    }

    public function locationInventory()
    {
        return $this->belongsTo(Company::class, 'location')->withDefault([
            'id' => 0,
            'name' => 'Perusahaan Tidak Ditemukan'
        ])->withTrashed()->select('id', 'name', 'top_parent_id');
    }

    public function additionalAttributes()
    {
        return $this->belongsToMany(ModelInventoryColumn::class, 'inventory_values', 'inventory_id', 'model_inventory_column_id')->select('model_inventory_columns.id as id', 'model_inventory_columns.name', 'inventory_values.value as value', 'model_inventory_columns.data_type')->withTrashed();
    }

    public function inventoryPart()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_inventory_pivots', 'parent_id', 'child_id')->select('inventories.id', 'inventories.model_id', 'inventories.mig_id', 'inventories.status_condition', 'inventories.status_usage', 'inventories.serial_number', 'inventories.location', 'inventories.deleted_at')->with('statusCondition', 'statusUsage', 'modelInventory.asset');
    }

    public function inventoryParts()
    {
        return $this->inventoryPart()->with('inventoryParts')->withTrashed();
    }

    public function inventoryAddableParts()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_inventory_pivots', 'parent_id', 'child_id')->select('inventories.id', 'inventories.model_id', 'inventories.mig_id', 'inventories.deleted_at')->with('modelInventory.asset', 'inventoryAddableParts');
    }

    public function inventoryReplacementParts()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_inventory_pivots', 'parent_id', 'child_id')->select('inventories.id')->with('inventoryReplacementParts');
    }

    public function inventoryParent()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_inventory_pivots', 'child_id', 'parent_id')->select('inventories.id', 'inventories.model_id', 'inventories.mig_id', 'inventories.status_condition', 'inventories.status_usage');
    }

    public function inventoryRelationshipsWithoutInventory()
    {
        return $this->hasMany(RelationshipInventory::class, 'subject_id')->where('type_id', '<>', -4);
    }

    public function inventoryRelationships()
    {
        return $this->hasMany(RelationshipInventory::class, 'subject_id')->with(['relationship']);
    }

    public function associations()
    {
        return $this->hasMany(Incident::class, 'inventory_id')->with(['ticket.task:id,status'])->select('id','inventory_id');
    }
}