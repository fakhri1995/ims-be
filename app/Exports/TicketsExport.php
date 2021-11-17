<?php

namespace App\Exports;

use App\Ticket;
use App\Services\GeneralService;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class TicketsExport implements FromView
{

    public function __construct($from = null, $to = null, $group = null, $engineer = null, $type = null, $is_history = false, $core_attributes, $secondary_attributes)
    {
        $generalService = new GeneralService;
        $current_timestamp = $generalService->getTimeNow();
        $this->type = $type;
        $this->core_attributes = $core_attributes;
        $this->secondary_attributes = $secondary_attributes;

        if($type == 1){
            $this->tickets = Ticket::with(['type','status', 'requester', 'ticketable.location','assignable']);
        } else {
            $this->tickets = Ticket::with(['type','status', 'requester','assignable']);
        }
        if($engineer || $group){
            if($engineer) $this->tickets = $this->tickets->where('assignable_id', $engineer)->where('assignable_type', 'App\User');
            else $this->tickets->where('assignable_id', $group)->where('assignable_type', 'App\Group');
        }

        if($is_history) $this->tickets = $this->tickets->where('status_id', 5);

        if(!$from){
            $last_month_timestamp_times = strtotime($current_timestamp) - 2592000;
            $from = date("Y-m-d", $last_month_timestamp_times);
        }
        if(!$to){
            $current_timestamp_times = strtotime($current_timestamp);
            $to = date("Y-m-d", $current_timestamp_times);
        }

        $this->tickets = $this->tickets->whereBetween('raised_at', [$from, $to])->get();
    }

    public function view(): View
    {
        if($this->type == 1){
            return view('excel.incident_tickets', [
                'tickets' => $this->tickets,
                'core_attributes' => $this->core_attributes,
                'secondary_attributes' => $this->secondary_attributes
            ]);
        } else {
            return view('excel.tickets', [
                'tickets' => $this->tickets,
                'core_attributes' => $this->core_attributes
            ]);
        }
        
    }
}