<?php

use App\CareerV2;
use Illuminate\Database\Seeder;

class CareerV2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function addCareerDummy(){
        $data = [
            [
                "name" => "Back-End Engineer Golang",
                "career_role_type_id" => "1",
                "career_experience_id" => "4",
                "salary_min" => "10000000",
                "salary_max" => "13000000",
                "overview" => "this is overview",
                "description" => "this is desc",
                "qualification" => "this is qualification",
                "is_posted" => "1"
            ],
            [
                "name" => "Flutter Developer",
                "career_role_type_id" => "2",
                "career_experience_id" => "3",
                "salary_min" => "8000000",
                "salary_max" => "10000000",
                "overview" => "this is overview",
                "description" => "this is desc",
                "qualification" => "this is qualification",
                "is_posted" => "0"
            ],
            [
                "name" => "Software Engineer (Python)",
                "career_role_type_id" => "3",
                "career_experience_id" => "2",
                "salary_min" => "7000000",
                "salary_max" => "9000000",
                "overview" => "this is overview",
                "description" => "this is desc",
                "qualification" => "this is qualification",
                "is_posted" => "1"
            ],
            [
                "name" => "Product Manager",
                "career_role_type_id" => "4",
                "career_experience_id" => "1",
                "salary_min" => "6000000",
                "salary_max" => "10000000",
                "overview" => "this is overview",
                "description" => "this is desc",
                "qualification" => "this is qualification",
                "is_posted" => "0"
            ]
        ];

        $i = 1;
        foreach($data as $d){
            $career = new CareerV2();
            $career->name = $d["name"];
            $career->career_role_type_id = $d["career_role_type_id"];
            $career->career_experience_id = $d["career_experience_id"];
            $career->salary_min = $d["salary_min"];
            $career->salary_max = $d["salary_max"];
            $career->overview = $d["overview"];
            $career->description = $d["description"];
            $career->is_posted = $d["is_posted"];
            $career->created_at = Date('Y-m-d H:i:s');
            $career->updated_at = Date('Y-m-d H:i:s');
            $career->created_by = 1;
            $career->save();
            $i++;
        }
    }


    public function run()
    {
        $this->addCareerDummy();
    }
}
