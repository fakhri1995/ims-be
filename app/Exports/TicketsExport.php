<?php

namespace App\Exports;

use App\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TicketsExport implements FromView
{

    public function __construct($from = null, $to = null, $group = null, $engineer = null, $type = null, $is_history = false, $core_attributes, $secondary_attributes)
    {
        $current_timestamp = date("Y-m-d H:i:s");
        $this->type = $type;
        $this->core_attributes = $core_attributes;
        $this->secondary_attributes = $secondary_attributes;

        if($type == 1){
            $this->tickets = Ticket::select('tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.created_by', 'tickets.closed_at', 'tickets.resolved_times', 'tickets.status', 'tickets.raised_at')
            ->with(['creator:id,name,company_id', 'creator.company:id,name,top_parent_id', 'creator.company.topParent:id,name', 'type:id,code,table_name', 'ticketable.assetType:id,name', 'ticketable.location:id,name,parent_id,top_parent_id,role'])
            ->where('tickets.ticketable_type', 'App\Incident');
        } else {
            $this->tickets = Ticket::select('tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.created_by', 'tickets.closed_at', 'tickets.resolved_times', 'tickets.status', 'tickets.raised_at')
            ->with(['creator:id,name,company_id', 'creator.company:id,name,top_parent_id', 'creator.company.topParent:id,name', 'type:id,code,table_name']);
        }

        if($is_history) $this->tickets = $this->tickets->where('status', 6);

        if(!$from){
            $last_month_timestamp_times = strtotime($current_timestamp) - 2592000;
            $from = date("Y-m-d", $last_month_timestamp_times);
        }
        if(!$to){
            $current_timestamp_times = strtotime($current_timestamp);
            $to = date("Y-m-d", $current_timestamp_times);
        }

        $this->tickets = $this->tickets->whereBetween('raised_at', [$from, $to]);
        if($group){
            $ticket_ids = DB::table('tasks')->select('id', 'reference_id', 'group_id')->where('tasks.group_id', $group)->pluck('reference_id')->unique()->values();
            $this->tickets = $this->tickets->whereIn('id', $ticket_ids);
        } else if($engineer){
            $task_ids = DB::table('task_user')->select('task_id', 'user_id')->where('task_user.user_id', $engineer)->pluck('task_id')->unique()->values();
            $ticket_ids = DB::table('tasks')->select('id', 'reference_id', 'group_id')->whereIn('tasks.id', $task_ids)->pluck('reference_id')->unique()->values();
            $this->tickets = $this->tickets->whereIn('id', $ticket_ids);
        }
        
        $this->tickets = $this->tickets->get();
        $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        
        if($type == 1){
            foreach($this->tickets as $ticket){
                $this->preProcessingTicketData($ticket, $statuses, $group, $engineer);
                $ticket->ticketable->full_location = $ticket->ticketable->location->fullNameWParentTopParent();
            }
        } else {
            foreach($this->tickets as $ticket){
                $this->preProcessingTicketData($ticket, $statuses, $group, $engineer);
            }
        }
        
    }

    private function preProcessingTicketData($ticket, $statuses, $group, $engineer){
        if($ticket->creator->id && $ticket->creator->company->id) $ticket->creator->company->full_name = $ticket->creator->company->fullName();
        else $ticket->creator->company->full_name = "-";
        $ticket->resolved_times = $this->diffForHuman($ticket->resolved_times);
        $ticket->status = $statuses[$ticket->status];
        $ticket->name = $ticket->type->code .'-'. $ticket->ticketable_id;
        if($group){
            $ticket->assignment_operator = DB::table('groups')->select('id', 'name')->find($group)->name ?? "-";
            if($this->core_attributes[0] || $this->core_attributes[8]) $this->core_attributes[8] = "Nama Group";
        } else if($engineer){
            $ticket->assignment_operator = DB::table('users')->select('id', 'name')->find($engineer)->name ?? "-";
            if($this->core_attributes[0] || $this->core_attributes[8]) $this->core_attributes[8] = "Nama User";
        } else {
            $this->core_attributes[8] = 0;
        }
    }

    private function diffForHuman($times){
        // 60 - minute
        // 3600 - hour
        // 86400 - day
        // 2592000 - month
        if($times === null) return "-";
        else if($times > 2591999) {
            $months = floor($times / 2592000);
            $remainder = $times % 2592000;
            if($remainder === 0) return "$months Bulan";
            if($remainder > 86399){
                $days = floor($remainder / 86400);
                return "$months Bulan $days Hari";
            } else if($remainder > 3599){
                $hours = floor($remainder / 3600);
                return "$months Bulan $hours Jam";
            } else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$months Bulan $minutes Menit";
            } else return "$months Bulan $remainder Detik";
        } else if($times > 86399) {
            $days = floor($times / 86400);
            $remainder = $times % 86400;
            if($remainder === 0) return "$days Hari";
            else if($remainder > 3599){
                $hours = floor($remainder / 3600);
                return "$days Hari $hours Jam";
            } else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$days Hari $minutes Menit";
            } else return "$days Hari $remainder Detik";
        } else if($times > 3599) {
            $hours = floor($times / 3600);
            $remainder = $times % 3600;
            if($remainder === 0) return "$hours Jam";
            else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$hours Jam $minutes Menit";
            } else return "$hours Jam $remainder Detik";
        } else if($times > 59) {
            $minutes = floor($times / 60);
            $remainder = $times % 60;
            if($remainder === 0) return "$minutes Menit";
            else return "$minutes Menit $remainder Detik";
        } else return "$times Detik";
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