<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelationshipInventory extends Model
{
    public $timestamps = false;

    public function relationship()
    {
        return $this->belongsTo(Relationship::class, 'relationship_id')->withDefault([
            'id' => 0,
            'relationship_type' => 'Relationship Tidak Ditemukan',
            'inverse_relationship_type' => 'Relationship Tidak Ditemukan',
            'description' => '-',
            'deleted_at' => null
        ]);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'subject_id')->withDefault([
            'id' => 0,
            'inventory_name' => 'Inventory Tidak Ditemukan',
            'deleted_at' => null
        ])->with('modelInventory:id,name','locationInventory:id,name,parent_id,role')->withTrashed()->select('id', 'model_id', 'location','deleted_at');
    }

    public function inventoryConnected()
    {
        return $this->belongsTo(Inventory::class, 'connected_id')->withDefault([
            'id' => 0,
            'name' => 'Inventory Tidak Ditemukan',
            'deleted_at' => null
        ])->withTrashed()->select('id', 'inventory_name', 'deleted_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'connected_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan',
            'role' => 0
        ])->withTrashed()->select('id', 'name', 'role');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'connected_id')->withDefault([
            'id' => 0,
            'name' => 'Perusahaan Tidak Ditemukan',
            'role' => 0,
        ])->withTrashed()->select('id', 'name', 'role');
    }
}
