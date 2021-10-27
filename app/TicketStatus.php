<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    public $timestamps = false;

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'status_id');
    }

    public function clientTickets()
    {
        $company_user_login_id = auth()->user()->company_id;
        return $this->hasMany(Ticket::class, 'status_id')->whereHas('requester.company', function($q) use ($company_user_login_id){
            $q->where('companies.company_id', $company_user_login_id);
        });
    }
}
