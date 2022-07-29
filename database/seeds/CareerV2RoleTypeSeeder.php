<?php

use App\CareerV2RoleType;
use Illuminate\Database\Seeder;

class CareerV2RoleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function addRoleType(){
        $data = [
            [ "name" => "Full Time" ],
            [ "name" => "Internship" ],
            [ "name" => "Contract" ],
            [ "name" => "Part Time" ],
        ];

        $i = 1;
        foreach($data as $d){
            $careerRoleType = new CareerV2RoleType();
            $careerRoleType->id = $i;
            $careerRoleType->name = $d['name'];
            $careerRoleType->save();
            $i++;
        }
    }


    public function run()
    {
        $this->addRoleType();
    }
}
