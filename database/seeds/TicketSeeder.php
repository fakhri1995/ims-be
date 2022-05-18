<?php

use App\Task;
use App\Ticket;
use App\Incident;
use App\TicketDetailType;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addTask($ticket_task_id, $location_id, $current_timestamp, $ticket_id, $created_by_id)
    {
        for($index = 0; $index < $ticket_task_id; $index++)
        {
            $task = new Task;
            $index_name = $index+1;
            $task->name = 'INCIDENT - Ticket '.$ticket_id.' '.$index_name;
            $task->description = "-";
            $task->task_type_id = $ticket_task_id;
            $task->location_id = $location_id;
            $task->reference_id = $ticket_id;
            $task->created_by = $created_by_id;
            $task->deadline = null;
            $task->first_deadline = null;
            $task->created_at = $current_timestamp;
            $task->group_id = null;
            $task->is_replaceable = true;
            $task->is_uploadable = true;
            $task->end_repeat_at = null;
            $task->repeat = null;
            $task->is_from_ticket = true;
            $task->is_visible = true;
            $task->status = 2;
            
            $task->save();
        }
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
            $random_int = random_int(1,3);
            $random_name_int = random_int(0,21);
            $location_id = random_int(1,100);
            $random_status = random_int(1,6);
            $product_id = 900 + $location_id;
            
            $incident = new Incident;
            $incident->product_type = $random_int;
            $incident->product_id = $product_id;
            $incident->pic_name = $random_name[$random_name_int];
            $incident->pic_contact = "081234567".$product_id;
            $incident->location_id = $location_id;
            $incident->problem = "Problem $i";
            $incident->incident_time = $current_timestamp;
            $incident->description = "Description $i";
            $incident->save();
            
            $ticket = new Ticket;
            // $ticket->task_id = $new_task->id;
            $ticket->status = $random_status;
            $ticket->created_by = $random_int;
            $ticket->raised_at = $current_timestamp;
            $ticket->ticketable_id = $incident->id;
            $ticket->ticketable_type = $ticketable_type;
            $ticket->save();

            $new_task = $this->addTask($random_int, $location_id, $current_timestamp, $ticket->id, $random_name_int);
    
            // $new_task->reference_id = $ticket->id;
            // $new_task->save();
        }
    }

    private function makeBulkTicketDetailType()
    {
        $default_incident_types = [
            "PC", "ATM", "UPS"
        ];

        foreach($default_incident_types as $data){
            $ticket_detail_type = new TicketDetailType;
            $ticket_detail_type->name = $data;
            $ticket_detail_type->ticket_type_id = 1;
            $ticket_detail_type->description = "Deksripsi $data";
            $ticket_detail_type->save();
        }
    }

    public function run()
    {
        $this->makeBulkTicketDetailType();
        $this->makeBulkTicket();
    }

}