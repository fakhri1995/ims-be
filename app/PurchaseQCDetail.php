<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseQCDetail extends Model
{
    public $timestamps = false;

    public function modelInventories()
    {
        return $this->belongsTo(ModelInventory::class, 'model_id');
    }

    public function purchaseQCDetailParts(){
        return $this->hasMany(self::class, 'parent_id', 'id')->with('purchaseQCDetailParts', 'purchaseQCDetailAttributes');
    }

    public function purchaseQCDetailAttributes()
    {
        return $this->belongsToMany(ModelInventoryColumn::class, 'purchase_q_c_detail_attributes', 'purchase_q_c_detail_id', 'model_inventory_column_id')->select('model_inventory_columns.id as id', 'model_inventory_columns.name', 'purchase_q_c_detail_attributes.is_checked as is_checked')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->select('id', 'purchase_q_c_id', 'parent_id');
    }

    public function getTopParent()
    {
        if($this->parent_id === null) return $this;
        $top_company = $this->parent;
        return $top_company->getTopParent();
    }
}