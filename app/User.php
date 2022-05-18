<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'name', 'image_profile', 'phone_number', 'company_id', 'role', 'is_active', 'created_time'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'pivot'
    ];

    public $timestamps = false;

    public function company(){
        return $this->belongsTo(Company::class)->withDefault([
            'id' => 0,
            'name' => '-',
            'full_name'=> '-'
        ]);
    }

    public function profileImage()
    {
        return $this->morphOne(File::class, 'fileable')->select('link', 'description', 'fileable_id', 'fileable_type')->withDefault([
            'link' => "-",
            'description' => "profile_image"
        ]);;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role_pivots');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->select('groups.id','groups.name');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function attendanceForms()
    {
        return $this->belongsToMany(AttendanceForm::class, 'attendance_form_user');
    }
}
