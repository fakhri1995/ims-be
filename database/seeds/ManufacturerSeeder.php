<?php

use App\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultManufacturers()
    {   
        $datas = $this->defaultManufacturers();
        foreach($datas as $data){
            $manufacturer = new Manufacturer;
            $manufacturer->name = $data['name'];
            $manufacturer->save();
        }
    }

    public function run()
    {
        $this->addDefaultManufacturers();
    }

    public function defaultManufacturers()
    {
        $data = [
            [
                "name" => "GRG Banking",
            ],
            [
                "name" => "Lenovo",
            ],
            [
                "name" => "HP",
            ],
            [
                "name" => "Enerplus",
            ],
            [
                "name" => "Delta",
            ],
            [
                "name" => "Micro-Star International",
            ],
            [
                "name" => "Mitsuboshi",
            ],
            [
                "name" => "Diebold Banking",
            ]
        ];
        return $data;
    }
}