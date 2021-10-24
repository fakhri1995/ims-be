<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relationship extends Model
{
    use SoftDeletes;

    protected $fillable = ['relationship_type', 'inverse_relationship_type', 'description'];
    public $timestamps = false;

    public function relationshipAssets()
    {
        return $this->hasMany(RelationshipAsset::class);
    }
}
