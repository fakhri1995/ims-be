<?php

use App\TicketType;
use App\TicketStatus;
use App\IncidentProductType;
use Illuminate\Database\Seeder;

class TicketManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function makeStatusConditions()
    {
        $status_names = ['Overdue', 'Open', 'On Progress', 'On Hold', 'Completed', 'Closed', 'Canceled'];
        foreach($status_names as $status_name){
            $status = new TicketStatus;
            $status->name = $status_name;
            $status->save();
        }
    }

    private function makeTypes()
    {
        $status_names = [['name' =>'Incident', 'code' => 'INC', 'table_name' => 'App\Incident']];
        foreach($status_names as $status_name){
            $status = new TicketType;
            $status->name = $status_name['name'];
            $status->code = $status_name['code'];
            $status->table_name = $status_name['table_name'];
            $status->save();
        }
    }

    public function run()
    {
        $this->makeStatusConditions();
        $this->makeTypes();
    }

}
