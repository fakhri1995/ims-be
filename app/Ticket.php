<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $timestamps = false;
    protected $hidden = ['ticketable_type', 'requester_id', 'status_id', 'assignable_id', 'ticketable_id', 'assignable_type'];
    
    public function getRaisedAtAttribute($value){
        $time_difference = Carbon::parse($value)->diffForHumans();
        $splits = explode(" ", $value); 
        return $splits[0]." ($time_difference)";
    }
    
    public function type()
    {
        return $this->belongsTo(TicketType::class, 'ticketable_type', 'table_name');
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function ticketable(){
        return $this->morphTo()->withDefault([
            'id' => 0,
            'location_id' => 0,
            'location' => (object)[
                'company_id' => 0,
                'company_name' => 'Perusahaan Tidak Ditemukan'
            ]
        ]);
    }

    public function assignable(){
        return $this->morphTo()->withDefault([
            'id' => 0,
            'name' => 'Penugasan Tidak Ditemukan'
        ]);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id')->withDefault([
            'user_id' => 0,
            'fullname' => 'User Tidak Ditemukan'
        ])->select('user_id', 'fullname','company_id');
    }
}
