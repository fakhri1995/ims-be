<?php

use App\RecruitmentRoleType;
use Illuminate\Database\Seeder;

class RecruitmentRoleTypeSeeder extends Seeder
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
            $careerRoleType = new RecruitmentRoleType();
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
