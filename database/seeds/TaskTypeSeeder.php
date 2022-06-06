<?php

use App\TaskType;
use App\TaskTypeWork;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

    private function makeBulkTaskTypes()
    {
        $datas = $this->defaultTaskTypes();
        foreach($datas as $data){
            $task_type = new TaskType;
            $task_type->name = $data['name'];
            $task_type->description = $data['description'];
            $task_type->save();
            foreach($data['components'] as $component){
                $task_type_work = new TaskTypeWork;
                $task_type_work->name = $component['name'];
                $task_type_work->description = $component['description'];
                $task_type_work->type = $component['type'];
                $task_type_work->details = $component['details'];
                $task_type_work->task_type_id = $task_type->id;
                $task_type_work->save();
            }
        }
    }

    public function run()
    {
        $this->makeBulkTaskTypes();
    }

    // Single Textbox
    // Paragraf
    // Checkbox
    // Matrix checkbox
    // Number
    // Dropdown

    private function defaultTaskTypes()
    {
        $data = [
            [
                "name" => "SAR ATM",
                "description" => "ATM Service Activity Report",
                "components" => [
                    [
                        "name" => "Jenis Layanan",
                        "description" => null,
                        "type" => 6,
                        "details" => [
                            "lists" => ["PM 1", "PM 2", "PM 3","Error/Support"],
                            "dropdown_name" => "Jenis Layanan"
                        ]
                    ],
                    [
                        "name" => "Jenis Kerusakan",
                        "description" => null,
                        "type" => 1,
                        "details" => (object)[]
                    ],
                    [
                        "name" => "Catatan",
                        "description" => null,
                        "type" => 2,
                        "details" => (object)[]
                    ],
                    [
                        "name" => "Kondisi Lingkungan",
                        "description" => null,
                        "type" => 5,
                        "details" => [
                            "lists" => [
                                ["type" => "Vac", "description" => "Tegangan Listrik"], 
                                ["type" => "C", "description" => "Suhu Ruang"]]
                        ]
                    ],
                    [
                        "name" => "Upper Compartment",
                        "description" => null,
                        "type" => 4,
                        "details" => [
                            "rows" => ["Card Reader", "Consumer Printer", "Monitor", "Keyboard"], 
                            "columns" => ["Diagnosic", "Check", "Clean", "Lubrication", "Adjustiment", "Replacement"], 
                            "is_general" => true
                        ]
                    ],
                    [
                        "name" => "Lower Compartment",
                        "description" => null,
                        "type" => 4,
                        "details" => [
                            "rows" => ["Presenter", "Stacker Module", "Feed Module", "CCA Dispenser", "Floopy Disk", "Hard Disk", "Power Unit", "Fan/Filter/Cassette", "Other"], 
                            "columns" => ["Diagnosic", "Check", "Clean", "Lubrication", "Adjustiment", "Replacement"], 
                            "is_general" => true
                        ]
                    ]
                ]
            ],
            [
                "name" => "SAR PC",
                "description" => "PC Service Activity Report",
                "components" => [
                    [
                        "name" => "Jenis Layanan",
                        "description" => null,
                        "type" => 6,
                        "details" => [
                            "lists" => ["Preventive Maintenance", "Corrective Maintenance", "Support"],
                            "dropdown_name" => "Jenis Layanan"
                        ]
                    ],
                    [
                        "name" => "Kondisi Lingkungan",
                        "description" => null,
                        "type" => 5,
                        "details" => [
                            "lists" => [
                                ["type" => "Volt", "description" => "Voltage (220 V)"], 
                                ["type" => "Volt", "description" => "Voltage (220 V)"]]
                        ]
                    ],
                    [
                        "name" => "Pekerjaan",
                        "description" => null,
                        "type" => 4,
                        "details" => [
                            "rows" => ["Cover", "Chipset", "Prosessor", "Memory DDR SDRAM", "Hard Drive SATA", "Optical Storage", "Card Reader", "Input Device (Keyboard & Mouse)", "Monitor"], 
                            "columns" => ["Checking", "Cleaning", "Replacement"], 
                            "is_general" => true
                        ]
                    ]
                ]
            ],
            [
                "name" => "SAR UPS",
                "description" => "UPS Service Activity Report",
                "components" => [
                    [
                        "name" => "Jenis Layanan",
                        "description" => null,
                        "type" => 6,
                        "details" => [
                            "lists" => ["PM", "Error/Support", "Other"],
                            "dropdown_name" => "Jenis Layanan"
                        ]
                    ],
                    [
                        "name" => "Analisa Setelah Kunjungan",
                        "description" => null,
                        "type" => 2,
                        "details" => (object)[]
                    ],
                    [
                        "name" => "Perbaikan yang dilakukan",
                        "description" => null,
                        "type" => 2,
                        "details" => (object)[]
                    ],
                    [
                        "name" => "Pemeriksaan Sumber Listrik",
                        "description" => "AC Input",
                        "type" => 5,
                        "details" => [
                            "lists" => [
                                ["type" => "Volt", "description" => "N---R"], 
                                ["type" => "Volt", "description" => "N---S"], 
                                ["type" => "Volt", "description" => "N---T"], 
                                ["type" => "Volt", "description" => "N---G"], 
                                ["type" => "Volt", "description" => "N---L"], 
                                ["type" => "Volt", "description" => "N---G"], 
                                ["type" => "Volt", "description" => "G---L"]
                            ]
                        ]
                    ],
                    [
                        "name" => "Pemeriksaan Sumber Listrik",
                        "description" => "AC Output",
                        "type" => 5,
                        "details" => [
                            "lists" => [
                                ["type" => "Volt", "description" => "N---R"], 
                                ["type" => "Volt", "description" => "N---S"], 
                                ["type" => "Volt", "description" => "N---T"], 
                                ["type" => "Volt", "description" => "N---G"], 
                                ["type" => "Volt", "description" => "N---L"], 
                                ["type" => "Volt", "description" => "N---G"], 
                                ["type" => "Volt", "description" => "G---L"]
                            ]
                        ]
                    ],
                    [
                        "name" => "Kondisi Lingkungan",
                        "description" => null,
                        "type" => 5,
                        "details" => [
                            "lists" => [
                                ["type" => "Volt", "description" => "Voltase Baterai"], 
                                ["type" => "C", "description" => "Temperatur Ruangan (Selesai)"]]
                        ]
                    ],
                    [
                        "name" => "Kesimpulan",
                        "description" => null,
                        "type" => 2,
                        "details" => (object)[]
                    ]
                ]
            ]
        ];

        return $data;
    }
}

