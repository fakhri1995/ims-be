<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $timestamps = false;
    protected $hidden = ['ticketable_type', 'requester_id', 'status_id', 'assignable_id', 'ticketable_id', 'assignable_type'];
    
    public function getRaisedAtAttribute($value){
        $time_difference = Carbon::parse($value)->diffForHumans(null, true, false, 3);
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
                'id' => 0,
                'name' => 'Perusahaan Tidak Ditemukan'
            ]
        ]);
    }

    public function assignable(){
        return $this->morphTo()->withDefault([
            'id' => 0,
            'name' => 'Penugasan Tidak Ditemukan'
        ])->select('id', 'name');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id')->withDefault([
            'id' => 0,
            'name' => 'User Tidak Ditemukan',
            'company_id'=> 0,
            'company'=> (object) [
                'id'=> 0,
                'name'=> 'Perusahaan Tidak Ditemukan',
                'full_name'=> 'Perusahaan Tidak Ditemukan'
            ]
        ])->select('id', 'name','company_id')->with(['company:id,name,top_parent_id', 'company.topParent']);
    }
}
