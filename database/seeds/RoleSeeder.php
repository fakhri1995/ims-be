<?php

use App\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultRoles()
    {   
        $datas = $this->defaultRoles();
        foreach($datas as $data){
            $role = new Role;
            $role->name = $data['name'];
            $role->description = $data['description'];
            $role->save();
        }
    }

    public function run()
    {
        $this->addDefaultRoles();
    }

    public function defaultRoles()
    {
        $data = [
            [
                "name" => "Super Admin",
                "description" => "The Highest Role Access for Users",
            ],
            [
                "name" => "Attendance Member",
                "description" => "For manage own attendance"
            ],
            [
                "name" => "Attendance Manager",
                "description" => "For manage form activity and also monitoring attendances"
            ],
            [
                "name" => "Technician Member",
                "description" => "For overall about own task and replacement sparepart"
            ],
            [
                "name" => "Task Manager",
                "description" => "For manage all related to the task"
            ],
            [
                "name" => "Ticket Manager",
                "description" => "For manage ticket and also manage the assigment"
            ],
            [
                "name" => "Asset Operator",
                "description" => "For manage all about tipe aset, model, inventory, vendor, manufacturer, relationship type"
            ],
            [
                "name" => "IT Support",
                "description" => "For manage control access about role and module"
            ],
            [
                "name" => "Human Resource",
                "description" => "For manage CMS including message and career in Company Profile and also Manage User"
            ],
            [
                "name" => "Location Manager",
                "description" => "Have full access to Company, client, and location"
            ],
            [
                "name" => "Procurement Manager",
                "description" => "For Manage all about purcashing and quality control"
            ],
            [
                "name" => "Contract Manager",
                "description" => "For Manage Full access to Contract Module"
            ],
        ];
        return $data;
    }
}










	
