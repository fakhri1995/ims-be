<?php

use App\Task;
use App\Ticket;
use App\Incident;
use App\TicketTaskType;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addTask($ticket_task_id, $location_id, $current_timestamp)
    {
        $task = new Task;
        $task->name = 'INCIDENT - Tipe Tiket Task'.$ticket_task_id;
        $task->description = "-";
        $task->task_type_id = $ticket_task_id;
        $task->location_id = $location_id;
        $task->reference_id = null;
        $task->created_by = $ticket_task_id;
        $task->deadline = null;
        $task->first_deadline = null;
        $task->created_at = $current_timestamp;
        $task->group_id = null;
        $task->is_replaceable = true;
        $task->is_uploadable = true;
        $task->end_repeat_at = null;
        $task->repeat = null;
        $task->is_from_ticket = true;
        $task->files = [];
        $task->is_visible = true;
        $task->status = 2;
        
        $task->save();
        return $task;
    }

    private function makeBulkTicket()
    {
        $random_name = [
            "Andi", "Budi", "Charly", "Deddy", "Edo", "Fristy", "Gorgc", "Herman", "Indi",
            "Jery", "Kelly", "Leshrac", "Meydi", "Nono", "Oprah", "Pep", "Quincy", "Rey", "Sule", 
            "Tita", "Uvi", "Victoria"
        ];

        $current_timestamp = date("Y-m-d H:i:s");
        $ticketable_type = 'App\Incident';
        for($i = 1; $i < 100; $i++){
            $random_int = random_int(1,20);
            $location_id = random_int(1,100);
            $product_id = 900 + $location_id;
            
            $files = [];
            $new_task = $this->addTask($random_int, $location_id, $current_timestamp);
            $incident = new Incident;
            $incident->product_type = $random_int;
            $incident->product_id = $product_id;
            $incident->pic_name = $random_name[$random_int];
            $incident->pic_contact = "081234567".$product_id;
            $incident->location_id = $location_id;
            $incident->problem = "Problem $i";
            $incident->incident_time = $current_timestamp;
            $incident->files = $files;
            $incident->description = "Description $i";
            $incident->save();
            
            $ticket = new Ticket;
            $ticket->task_id = $new_task->id;
            $ticket->ticketable_id = $incident->id;
            $ticket->ticketable_type = $ticketable_type;
            $ticket->save();
    
            $new_task->reference_id = $ticket->id;
            $new_task->save();
        }
    }

    private function makeBulkTicketTaskType()
    {
        for($i = 1; $i < 21; $i++){
            $ticket_task_type = new TicketTaskType;
            $ticket_task_type->name = "Tipe Tiket Task $i";
            $ticket_task_type->task_type_id = $i;
            $ticket_task_type->ticket_type_id = 1;
            $ticket_task_type->description = "Deksripsi Tipe Tiket Task $i";
            $ticket_task_type->save();
        }
    }

    public function run()
    {
        $this->makeBulkTicketTaskType();
        $this->makeBulkTicket();
    }

}