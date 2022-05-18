<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    public $timestamps = false;
    protected $fillable = ['status'];
    protected $casts = ['files' => 'array'];

    public function taskType()
    {
        return $this->belongsTo(TaskType::class)->withTrashed();
    }

    public function reference()
    {
        return $this->belongsTo(Ticket::class, 'reference_id');
    }

    public function location()
    {
        return $this->belongsTo(Company::class, 'location_id')->withTrashed();
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->with('profileImage')->withDefault([
            'id' => 0,
            'name' => '-',
            'company_id' => 0,
        ]);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class)->with('profileImage')->select('users.id','users.name','users.position', 'task_user.check_in', 'task_user.check_out', 'task_user.lat_check_in', 'task_user.long_check_in', 'task_user.lat_check_out', 'task_user.long_check_out')->withPivot('check_in', 'check_out');
    }

    public function inventories()
    {
        return $this->belongsToMany(Inventory::class)->withPivot('is_from_task', 'is_in', 'user_id', 'connect_id');
    }

    public function taskDetails()
    {
        return $this->hasMany(TaskDetail::class)->orderBy('id');
    }

    public function attachments()
    {
        return $this->morphMany('App\File', 'fileable');
    }
}
