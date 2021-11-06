<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelationshipAsset extends Model
{
    use SoftDeletes;
    protected $fillable = ['subject_id','relationship_id','is_inverse','type_id','connected_id'];
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

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'subject_id')->withDefault([
            'id' => 0,
            'name' => 'Asset Tidak Ditemukan',
            'deleted_at' => null
        ])->withTrashed()->select('id', 'name', 'deleted_at');
    }

    public function assetConnected()
    {
        return $this->belongsTo(Asset::class, 'connected_id')->withDefault([
            'id' => 0,
            'name' => 'Asset Tidak Ditemukan',
            'deleted_at' => null
        ])->withTrashed()->select('id', 'name', 'deleted_at');
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

    public function relationshipInventories()
    {
        return $this->hasMany(RelationshipInventory::class);
    }
}
