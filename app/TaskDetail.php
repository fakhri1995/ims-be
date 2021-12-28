<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskDetail extends Model
{
    public $timestamps = false;
    protected $casts = ['component' => 'object'];
    protected $fillable = ['task_id', 'task_type_work_id', 'component'];
    protected $with = ['users'];
    
    public function users()
    {
        return $this->belongsToMany(User::class)->select('users.id','users.name','users.profile_image');
    }

    public function task()
    {
        return $this->belongsTo(Task::class)->withTrashed();
    }
}
