<?php

use App\CareerV2ApplyStatus;
use Illuminate\Database\Seeder;

class CareerV2ApplyStatusSeeder extends Seeder
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

    public function run()
    {
        $this->addApplyStatus();
    }
}
