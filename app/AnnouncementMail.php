<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnnouncementMail extends Model
{
    public $appends = ['purposes'];

    public function announcement()
    {
        return $this->hasOne(Announcement::class, 'id', 'announcement_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function staff()
    {
        return $this->hasMany(AnnouncementMailStaff::class, 'announcement_mail_id', 'id');
    }

    public function group()
    {
        return $this->hasMany(AnnouncementMailGroup::class, 'announcement_mail_id', 'id');
    }

    public function result()
    {
        return $this->hasOne(AnnouncementMailResult::class, 'announcement_mail_id', 'id');
    }

    public function getPurposesAttribute(){
        $purposes = null;
        if(count($this->staff))   {
            foreach($this->staff as $item){
                $purposes[] = $item->user->name;
            }
        }
        else if(count($this->group))   {
            foreach($this->group as $item){
                $purposes[] = $item->groups->name;
            }
        }

        return $purposes;
    }

}
