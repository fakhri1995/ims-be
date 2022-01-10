<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $timestamps = false;
    protected $hidden = ['ticketable_type'];
    
    // public function getRaisedAtAttribute($value){
    //     $time_difference = Carbon::parse($value)->diffForHumans(null, false, false, 2);
    //     $splits = explode(" ", $value); 
    //     return $splits[0]." ($time_difference)";
    // }
    
    public function type()
    {
        return $this->belongsTo(TicketType::class, 'ticketable_type', 'table_name');
    }

    public function task()
    {
        return $this->belongsTo(Task::class)->withTrashed();
    }

    public function ticketable(){
        return $this->morphTo()->withDefault([
            'id' => 0,
            'location_id' => 0,
            'location' => (object)[
                'id' => 0,
                'name' => '-'
            ]
        ]);
    }

    // public function assignable(){
    //     return $this->morphTo()->withDefault([
    //         'id' => 0,
    //         'name' => '-'
    //     ])->select('id', 'name');
    // }

    // public function requester()
    // {
    //     return $this->belongsTo(User::class, 'requester_id')->withDefault([
    //         'id' => 0,
    //         'name' => '-',
    //         'company_id'=> 0,
    //         'company'=> (object) [
    //             'id'=> 0,
    //             'name'=> '-',
    //             'full_name'=> '-'
    //         ]
    //     ])->select('id', 'name','company_id')->with(['company:id,name,top_parent_id', 'company.topParent']);
    // }
}
