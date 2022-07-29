<?php

use App\CareerV2Experience;
use Illuminate\Database\Seeder;

class CareerV2ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    
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

    public function run()
    {
        $this->addExperience();
    }
}