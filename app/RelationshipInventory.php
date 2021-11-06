<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelationshipInventory extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    public function relationshipAsset()
    {
        return $this->belongsTo(RelationshipAsset::class)->withDefault([
            'id' => 0,
            'subject_id' => 0,
            'relationship_id' => 0,
            'is_inverse' => false,
            'type_id' => 0,
            'connected_id' => 0,
            'deleted_at' => null,
            'relationship' => (object)[
                'id' => 0,
                'relationship_type' => 'Relationship Asset Tidak Ditemukan',
                'inverse_relationship_type' => 'Relationship Asset Tidak Ditemukan',
                'description' => '-',
                'deleted_at' => null
            ]
        ])->with('relationship');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'subject_id')->withDefault([
            'id' => 0,
            'inventory_name' => 'Inventory Tidak Ditemukan',
            'deleted_at' => null
        ])->withTrashed()->select('id', 'inventory_name', 'deleted_at');
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
