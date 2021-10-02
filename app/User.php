<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;
    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'fullname', 'image_profile', 'phone_number', 'company_id', 'role', 'is_active', 'created_time'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public $timestamps = false;

    public function company(){
        return $this->hasOne(Company::class, 'company_id', 'company_id');
    }

    public function featureRoles(){
        return $this->hasMany(UserRolePivot::class, 'user_id', 'user_id')->select(['role_id']);
    }
}
