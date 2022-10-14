<?php

use App\CareerV2;
use App\CareerV2ApplyStatus;
use App\CareerV2Experience;
use App\CareerV2RoleType;
use Illuminate\Database\Seeder;

class CareerV2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function addApplyStatus(){
        $data = [
            [
                "name" =>  "Unprocessed",
                "display_order" =>  1,
            ],
            [
                "name" =>  "Shortlisted",
                "display_order" =>  2,
            ],
            [
                "name" =>  "Rejected",
                "display_order" =>  3,
            ],
        ];

        $i = 1;
        foreach($data as $d){
            $careerExperience = new CareerV2ApplyStatus();
            $careerExperience->id = $i;
            $careerExperience->name = $d['name'];
            $careerExperience->display_order = $d['display_order'];
            $careerExperience->save();
            $i++;
        }
    }

    public function addExperience(){
        $data = [
            [
                "min" =>  0,
                "max" =>  1,
                "str" =>  "0 - 1 Tahun",
            ],
            [
                "min" =>  1,
                "max" =>  3,
                "str" =>  "1 - 3 Tahun",
            ],
            [
                "min" =>  3,
                "max" =>  5,
                "str" =>  "3 - 5 Tahun",
            ],
            [
                "min" =>  5,
                "max" =>  NULL,
                "str" =>  "Lebih dari 5 Tahun",
            ]
        ];

        $i = 1;
        foreach($data as $d){
            $careerExperience = new CareerV2Experience();
            $careerExperience->id = $i;
            $careerExperience->min = $d['min'];
            $careerExperience->max = $d['max'];
            $careerExperience->str = $d['str'];
            $careerExperience->save();
            $i++;
        }
    }

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
        $this->addApplyStatus();
        $this->addExperience();
        $this->addRoleType();

    }
}
