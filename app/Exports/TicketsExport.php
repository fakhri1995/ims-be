<?php

namespace App\Exports;

use App\Ticket;
use App\Services\GeneralService;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class TicketsExport implements FromView
{

    public function __construct($from = null, $to = null, $group = null, $engineer = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->group = $group;
        $this->engineer = $engineer;
         
        if($this->from === null || $this->to === null){

            $month_seconds = 2592000;
        
            $generalService = new GeneralService;
            $current_timestamp = $generalService->getTimeNow();
            $current_timestamp_times = strtotime($current_timestamp);
            $last_month_timestamp_times = $current_timestamp_times - $month_seconds;
            $last_month = date("Y-m-d", $last_month_timestamp_times);
            $this->to = $current_timestamp;
            $this->from = $last_month;
        }
    }

    public function view(): View
    {
        return view('tickets', [
            'tickets' => Ticket::with('requester.company')->get()
        ]);
    }

    // public function collection()
    // {
    //     $tickets = Ticket::select('*');
    //     if($this->group !== null){
    //         $tickets = $tickets->where('assignable_id', $this->group)->where('assignable_type', 'App\Group');
    //     }
    //     if($this->engineer !== null){
    //         $tickets = $tickets->where('assignable_id', $this->engineer)->where('assignable_type', 'App\User');
    //     }
    //     $visible = [];
    //     if(1)$visible[] = 'assignable_id';
    //     // if(1)$visible[] = 'ticketable_id';
    //     // if(1)$visible[] = 'assignable_type';
    //     // if(1)$visible[] = 'ticketable_type';
    //     // if(1)$visible[] = 'requester_id';
    //     if(1)$visible[] = 'status_id';
    //     $tickets = $tickets->whereBetween('raised_at', [$this->from, $this->to])->get();
    //     $tickets->makeVisible($visible);
    //     return $tickets;
    // }
}