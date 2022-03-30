<?php

use App\Inventory;
use App\ModelInventory;
use App\ModelInventoryColumn;
use Illuminate\Database\Seeder;

class ModelInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function createModel($data)
    {
        $model = new ModelInventory;
        $model->asset_id = $data['asset_id'];
        $model->name = $data['name'];
        $model->description = "-";
        $model->is_consumable = false;
        $model->manufacturer_id = null;
        $model->required_sn = true;
        $model->save();
        $model_column_a = new ModelInventoryColumn([
            'name' => 'Kapasitas', 
            'data_type' => 'String',
            'default' => '4 GB',
            'required' => false
        ]);
        $model_column_b = new ModelInventoryColumn([
            'name' => 'Processor', 
            'data_type' => 'String',
            'default' => null,
            'required' => false
        ]);
        $model->modelColumns()->saveMany([$model_column_a, $model_column_b]);
        foreach($data['model_parts'] as $model_part){
            $model->modelParts()->attach($model_part['id'], ['quantity' => $model_part['quantity']]);
        }
        // return $model;
    }

    private function makeDefaultModels()
    {
        $datas = $this->defaultModels();
        foreach($datas as $data){
            $this->createModel($data);
        }
    }

    private function generateRandomString($length = 5) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString.'-';
    }

    private function generateRandomNumber($length = 6) {
        $characters = '123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function saveInventoryParts($parent_inventory, $data, $status_condition, $status_usage, $location)
    {
        $inventory = $this->createInventory($data, $status_condition, $status_usage, $location);
        $parent_inventory->inventoryParts()->attach($inventory->id);
        
        if(count($data["inventory_parts"])){
            foreach($data["inventory_parts"] as $inventory_part){
                $this->saveInventoryParts($inventory, $inventory_part, $status_condition, $status_usage, $location);
            }
        }
    }

    private function createInventory($data, $status_condition, $status_usage, $location)
    {
        $serial_number = "";
        for($i = 0; $i < 5; $i++) $serial_number .= $this->generateRandomString();
        $serial_number = substr_replace($serial_number ,"", -1);
        $inventory = new Inventory;
        $inventory->model_id = $data["model_id"];
        $inventory->vendor_id = null;
        $inventory->status_condition = $status_condition;
        $inventory->status_usage = $status_usage;
        $inventory->location = $location;
        $inventory->deskripsi = "-";
        $inventory->manufacturer_id = null;
        $inventory->mig_id = $this->generateRandomNumber();
        $inventory->serial_number = $serial_number;
        $inventory->is_consumable = false;
        $inventory->save();
        
        if(count($data["inventory_values"])){
            foreach($data["inventory_values"] as $inventory_value){
                $inventory->additionalAttributes()->attach($inventory_value['id'], ['value' => $inventory_value['value']]);
            }
        }
        
        if(count($data["inventory_parts"])){
            foreach($data["inventory_parts"] as $inventory_part){
                for($i = 0; $i < $inventory_part["quantity"]; $i++)
                    $this->saveInventoryParts($inventory, $inventory_part, $status_condition, $status_usage, $location);
            }
        }

        return $inventory;
    }
    
    private function makeDefaultInventories()
    {
        $datas = $this->defaultInventories();
        $location = 1;
        for($i = 0; $i < 5; $i++){
            foreach($datas as $data){
                $inventory = $this->createInventory($data, 1, 1, $location);
            }
        }
        foreach($datas as $data){
            $inventory = $this->createInventory($data, 3, 2, $location);
            $inventory = $this->createInventory($data, 3, 3, $location);
        } 
        for($i = 0; $i < 2; $i++){
            foreach($datas as $data) $inventory = $this->createInventory($data, 2, 1, $location);
        }
        for($i = 0; $i < 7; $i++){
            $location += rand(1,20);
            foreach($datas as $data){
                $inventory = $this->createInventory($data, 1, 1, $location);
                $inventory = $this->createInventory($data, 3, 2, $location);
                $inventory = $this->createInventory($data, 3, 3, $location);
                $inventory = $this->createInventory($data, 2, 1, $location);
            }
        }
    }

    public function run()
    {
        $this->makeDefaultModels();
        $this->makeDefaultInventories();
    }

    private function defaultModels()
    {
        $models = [
            [
                "asset_id" => 7,
                "name" => "GRG CDM CONTROLLER YT7.820.0677",
                "model_parts" => []
            ],
            [
                "asset_id" => 8,
                "name" => "GRG CASESETTE FRAME YT4.029.0782",
                "model_parts" => []
            ],
            [
                "asset_id" => 9,
                "name" => "GRG CASH DISPENSER MODULE YT2.291.2088",
                "model_parts" => [
                    [
                        "id" => 8,
                        "quantity" => 1
                    ],
                    [
                        "id" => 4,
                        "quantity" => 1
                    ],
                    [
                        "id" => 1,
                        "quantity" => 1
                    ],
                    [
                        "id" => 5,
                        "quantity" => 1
                    ],
                    [
                        "id" => 6,
                        "quantity" => 4
                    ],
                    [
                        "id" => 2,
                        "quantity" => 4
                    ],
                    [
                        "id" => 7,
                        "quantity" => 4
                    ]
                ]
            ],
            [
                "asset_id" => 10,
                "name" => "GRG NOTE PRESENTER YT4.029.0783",
                "model_parts" => []
            ],
            [
                "asset_id" => 11,
                "name" => "GRG REJECT VAULT CASSETTE YT4.100.2172",
                "model_parts" => []
            ],
            [
                "asset_id" => 12,
                "name" => "GRG NOTE CASSETTE YT4.100.2158",
                "model_parts" => []
            ],
            [
                "asset_id" => 13,
                "name" => "GRG NOTE FEEDER YT4.029.0778",
                "model_parts" => []
            ],
            [
                "asset_id" => 14,
                "name" => "GRG NOTE TRANSPORT YT4.109.3409",
                "model_parts" => []
            ],
            [
                "asset_id" => 15,
                "name" => "GRG EPP004 KEY PAD YT2.232.0301",
                "model_parts" => []
            ],
            [
                "asset_id" => 16,
                "name" => "GRG RECEIPT PRINTER YT2.241.0311",
                "model_parts" => []
            ]
        ];
        return $models;
    }

    private function defaultInventories()
    {
        $inventories = [
            [
                "model_id" => 3,
                "inventory_parts" => [
                    [
                        "model_id" => 8,
                        "quantity" => 1,
                        "inventory_values" => [
                            [
                                "id" => 15,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 16,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ],
                    [
                        "model_id" => 4,
                        "quantity" => 1,
                        "inventory_values" => [
                            [
                                "id" => 7,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 8,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ],
                    [
                        "model_id" => 1,
                        "quantity" => 1,
                        "inventory_values" => [
                            [
                                "id" => 1,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 2,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ],
                    [
                        "model_id" => 5,
                        "quantity" => 1,
                        "inventory_values" => [
                            [
                                "id" => 9,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 10,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ],
                    [
                        "model_id" => 6,
                        "quantity" => 4,
                        "inventory_values" => [
                            [
                                "id" => 11,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 12,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ],
                    [
                        "model_id" => 2,
                        "quantity" => 4,
                        "inventory_values" => [
                            [
                                "id" => 3,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 4,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ],
                    [
                        "model_id" => 7,
                        "quantity" => 4,
                        "inventory_values" => [
                            [
                                "id" => 13,
                                "value" => "Processor"
                            ],
                            [
                                "id" => 14,
                                "value" => "Kapasitas"
                            ]
                        ],
                        "inventory_parts" => []
                    ]
                    
                ],
                "inventory_values" => [
                    [
                        "id" => 5,
                        "value" => "Processor"
                    ],
                    [
                        "id" => 6,
                        "value" => "Kapasitas"
                    ]
                ]
            ],
            [
                "model_id" => 9,
                "inventory_parts" => [],
                "inventory_values" => [
                    [
                        "id" => 17,
                        "value" => "Processor"
                    ],
                    [
                        "id" => 18,
                        "value" => "Kapasitas"
                    ]
                ]
            ],
            [
                "model_id" => 10,
                "inventory_parts" => [],
                "inventory_values" => [
                    [
                        "id" => 19,
                        "value" => "Processor"
                    ],
                    [
                        "id" => 20,
                        "value" => "Kapasitas"
                    ]
                ]
            ]
        ];
        return $inventories;
    }
}
