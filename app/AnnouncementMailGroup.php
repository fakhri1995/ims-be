<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnnouncementMailGroup extends Model
{
    protected $table = 'announcement_mail_groups';

    public function groups()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }
}
