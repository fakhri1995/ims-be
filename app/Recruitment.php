<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruitment extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $casts = [
        'lampiran' => 'array',
    ];
    

    public function role()
    {
        return $this->belongsTo(RecruitmentRole::class, 'recruitment_role_id');
    }

    public function jalur_daftar()
    {
        return $this->belongsTo(RecruitmentJalurDaftar::class, 'recruitment_jalur_daftar_id');
    }

    public function stage()
    {
        return $this->belongsTo(RecruitmentStage::class, 'recruitment_stage_id');
    }

    public function status()
    {
        return $this->belongsTo(RecruitmentStatus::class, 'recruitment_status_id');
    }

    public function resume()
    {
        return $this->hasOne(Resume::class, 'owner_id', 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id')->select(['id','is_enabled','email']);
    }
}

