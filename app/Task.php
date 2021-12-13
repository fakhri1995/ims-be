<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function reference()
    {
        return $this->belongsTo(Ticket::class, 'reference_id');
    }

    public function location()
    {
        return $this->belongsTo(Company::class, 'location_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class)->select('users.id','users.name','users.profile_image');
    }

    public function inventories()
    {
        return $this->belongsToMany(Inventory::class)->select('inventories.id', 'inventories.model_id', 'inventories.mig_id')->withPivot('is_from_task', 'is_in');
    }

    public function taskDetails()
    {
        return $this->hasMany(TaskDetail::class)->orderBy('id');
    }
}
