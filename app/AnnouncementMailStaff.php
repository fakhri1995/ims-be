<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnnouncementMailStaff extends Model
{
    protected $table = 'announcement_mail_staffs';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
