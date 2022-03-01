<?php

use App\Asset;
use App\Inventory;
use App\AssetColumn;
use App\Relationship;
use App\ModelInventory;
use App\ModelInventoryColumn;
use App\StatusUsageInventory;
use App\RelationshipInventory;
use Illuminate\Database\Seeder;
use App\StatusConditionInventory;

class AssetManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function makeDefaultAssets()
    {
        $datas = $this->defaultAsset();
        foreach($datas as $data){
            $asset = new Asset;
            $asset->parent_id = $data['parent_id'];
            $asset->name = $data['name'];
            $asset->description = "-";
            $asset->required_sn = true;
            $asset->code = $data['code'];
            $asset->save();
            $asset_column_a = new AssetColumn([
                'name' => 'Kapasitas', 
                'data_type' => 'String',
                'default' => '4 GB',
                'required' => false
            ]);
            $asset_column_b = new AssetColumn([
                'name' => 'Processor', 
                'data_type' => 'String',
                'default' => null,
                'required' => false
            ]);
            $asset->assetColumns()->saveMany([$asset_column_a, $asset_column_b]);
        }
    }

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

    private function makeStatusConditions()
    {
        $status_names = ['Good', 'Grey', 'Bad'];
        foreach($status_names as $status_name){
            $status = new StatusConditionInventory;
            $status->name = $status_name;
            $status->save();
        }
    }

    private function makeStatusUsages()
    {
        $status_names = ['In Used', 'In Stock', 'Replacement'];
        foreach($status_names as $status_name){
            $status = new StatusUsageInventory;
            $status->name = $status_name;
            $status->save();
        }
    }

    private function makeRelationships()
    {
        $defaults = [
            ["name" => "Menggunakan", "inverse_name" => "Digunakan"],
            ["name" => "Memiliki", "inverse_name" => "Dimiliki"],
            ["name" => "Meminjam", "inverse_name" => "Dipinjam"],
            ["name" => "Memesan", "inverse_name" => "Dipesan"],
            ["name" => "Membeli", "inverse_name" => "Dibeli"]
        ];

        foreach($defaults as $default){
            $relationship = new Relationship;
            $relationship->relationship_type = $default['name'];
            $relationship->inverse_relationship_type = $default['inverse_name'];
            $relationship->description = "-";
            $relationship->save();
        }
    }

    public function run()
    {
        $this->makeStatusConditions();
        $this->makeStatusUsages();
        $this->makeDefaultAssets();
        $this->makeDefaultModels();
        $this->makeDefaultInventories();
        $this->makeRelationships();
        // $this->makeBulkAssets();
        // $this->makeBulkAssetColumns();
        // $this->makeBulkModels();
        // $this->makeBulkModelInventoryColumns();
        // $this->makeBulkInventories();
        // $this->makeRelationships();
        // $this->makeRelationshipInventories();
    }

    private function defaultAsset()
    {
        $assets = [
            [
                "parent_id" => null,
                "name" => "Rotable",
                "code" => "001"
            ],
            [
                "parent_id" => null,
                "name" => "IT Asset",
                "code" => "002"
            ],
            [
                "parent_id" => null,
                "name" => "Non-IT Asset",
                "code" => "003"
            ],
            [
                "parent_id" => null,
                "name" => "Component",
                "code" => "004"
            ],
            [
                "parent_id" => 1,
                "name" => "SPAREPART ATM",
                "code" => "001.001"
            ],
            [
                "parent_id" => 5,
                "name" => "ROTABLE PART",
                "code" => "001.001.001"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG CDM CONTROLLER",
                "code" => "001.001.001.001"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG CASESETTE FRAME",
                "code" => "001.001.001.002"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG CASH DISPENSER MODULE",
                "code" => "001.001.001.003"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG NOTE PRESENTER",
                "code" => "001.001.001.004"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG REJECT VAULT CASSETTE",
                "code" => "001.001.001.005"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG NOTE CASSETTE",
                "code" => "001.001.001.006"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG NOTE FEEDER",
                "code" => "001.001.001.007"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG NOTE TRANSPORT",
                "code" => "001.001.001.008"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG EPP004 KEY PAD",
                "code" => "001.001.001.009"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG RECEIPT PRINTER",
                "code" => "001.001.001.010"
            ],
            [
                "parent_id" => 6,
                "name" => "GRG SECURED CARD SLOT MODULE (SCM-001)",
                "code" => "001.001.001.011"
            ]
        ];
        return $assets;
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

    // private function makeBulkAssets()
    // {
    //     $name_asset = "Asset ";
    //     $asset_name = ['', 'IT Asset', 'Non-IT Asset', 'Rotable', 'Component'];
    //     for($i = 1; $i < 5; $i++){
    //         $code = "00$i";  
    //         $asset = new Asset;
    //         $asset->parent_id = null;
    //         $asset->name = $asset_name[$i];
    //         $asset->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
    //         $asset->required_sn = true;
    //         $asset->code = $code;
    //         $asset->save();
    //         for($j = 1; $j < 2; $j++){
    //             $code_a = $code.".00$j";  
    //             $asset_a = new Asset;
    //             $asset_a->parent_id = $asset->id;
    //             $asset_a->name = $name_asset . $code_a;
    //             $asset_a->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
    //             $asset_a->required_sn = true;
    //             $asset_a->code = $code_a;
    //             $asset_a->save();
    //             for($k = 1; $k < 3; $k++){
    //                 $code_b = $code_a.".00$k";  
    //                 $asset_b = new Asset;
    //                 $asset_b->parent_id = $asset_a->id;
    //                 $asset_b->name = $name_asset . $code_b;
    //                 $asset_b->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
    //                 $asset_b->required_sn = true;
    //                 $asset_b->code = $code_b;
    //                 $asset_b->save();
    //             }
    //         }
    //     }
    // }

    // private function makeBulkAssetColumns()
    // {
    //     for($i = 1; $i < 16; $i++){
    //         $model_inventory_column = new AssetColumn;
    //         $model_inventory_column->asset_id = $i;
    //         $model_inventory_column->name = "Kapasitas";
    //         $model_inventory_column->data_type = "String";
    //         $model_inventory_column->default = "4 GB";
    //         $model_inventory_column->required = false;
    //         $model_inventory_column->save();

    //         $model_inventory_column = new AssetColumn;
    //         $model_inventory_column->asset_id = $i;
    //         $model_inventory_column->name = "Processor";
    //         $model_inventory_column->data_type = "String";
    //         $model_inventory_column->required = true;
    //         $model_inventory_column->save();
    //     }
    // }

    // private function makeBulkModels()
    // {   
    //     $name_model = "Model ";
    //     for($x = 1; $x < 5; $x++){
    //         for($i = 0; $i < 4; $i++){
    //             $id = $i * 4 + 1;
    //             $code_int = $i+1;
    //             $code = "$code_int";  
    //             $model = new ModelInventory;
    //             $model->asset_id = $id;
    //             $model->name = $name_model."$x ".$code;
    //             $model->description = "Bulk Model Seeder";
    //             $model->manufacturer_id = 1;
    //             $model->required_sn = false;
    //             $model->is_consumable = false;
    //             $model->save();

    //             $code_a = $code.".1";  
    //             $model_a = new ModelInventory;
    //             $model_a->asset_id = $id+1;
    //             $model_a->name = $name_model."$x ".$code_a;
    //             $model_a->description = "Bulk Model Seeder";
    //             $model_a->manufacturer_id = 1;
    //             $model_a->required_sn = false;
    //             $model_a->is_consumable = false;
    //             $model_a->save();
    //             $model->modelParts()->attach($model_a->id, ['quantity' => 1]);

    //             for($k = 2; $k < 4; $k++){
    //                 $index = $k-1;
    //                 $code_b = $code_a.".$index";  
    //                 $model_b = new ModelInventory;
    //                 $model_b->asset_id = $id+$k;
    //                 $model_b->name = $name_model."$x ".$code_b;
    //                 $model_b->description = "Bulk Model Seeder";
    //                 $model_b->manufacturer_id = 1;
    //                 $model_b->required_sn = true;
    //                 $model_b->is_consumable = false;
    //                 $model_b->save();
    //                 $model_a->modelParts()->attach($model_b->id, ['quantity' => 1]);
    //             }

    //             $code_a = $code.".2";  
    //             $model_a = new ModelInventory;
    //             $model_a->asset_id = $id+1;
    //             $model_a->name = $name_model."$x ".$code_a;
    //             $model_a->description = "Bulk Model Seeder";
    //             $model_a->manufacturer_id = 1;
    //             $model_a->required_sn = false;
    //             $model_a->is_consumable = false;
    //             $model_a->save();
    //             $model->modelParts()->attach($model_a->id, ['quantity' => 1]);
    //         }
    //     }
    // }

    // private function makeBulkModelInventoryColumns()
    // {
    //     for($i = 1; $i < 81; $i++){
    //         $model_inventory_column = new ModelInventoryColumn;
    //         $model_inventory_column->model_id = $i;
    //         $model_inventory_column->name = "Kapasitas";
    //         $model_inventory_column->data_type = "String";
    //         $model_inventory_column->default = "4 GB";
    //         $model_inventory_column->required = false;
    //         $model_inventory_column->save();

    //         $model_inventory_column = new ModelInventoryColumn;
    //         $model_inventory_column->model_id = $i;
    //         $model_inventory_column->name = "Processor";
    //         $model_inventory_column->data_type = "String";
    //         $model_inventory_column->required = true;
    //         $model_inventory_column->save();
    //     }
    // }

    // private function makeBulkInventories()
    // {   
    //     $index = 1;
    //     for($v = 1; $v < 3; $v++){
    //         $model_id = 1;
    //         $model_inventory_column_id = 1;
    //         for($x = 1; $x < 5; $x++){
    //             for($i = 0; $i < 4; $i++){
    //                 $id = $i * 4 + 1;
    //                 $code_int = $i+1;
    //                 $code = "$code_int";  
    //                 $inventory = new Inventory;
    //                 $inventory->model_id = $model_id;
    //                 $inventory->status_condition = 1;
    //                 $inventory->status_usage = random_int(1, 3);
    //                 $inventory->mig_id = "MIG-$index";
    //                 $inventory->is_consumable = false;
    //                 $inventory->save();
    //                 $index+=1;
    //                 $model_id +=1;
    //                 $inventory->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
    //                 $model_inventory_column_id++;
    //                 $inventory->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
    //                 $model_inventory_column_id++;
    
    //                 $code_a = $code.".1";  
    //                 $inventory_a = new Inventory;
    //                 $inventory_a->model_id = $model_id;
    //                 $inventory_a->status_condition = 2;
    //                 $inventory_a->status_usage = 2;
    //                 $inventory_a->mig_id = "MIG-$index";
    //                 $inventory_a->is_consumable = false;
    //                 $inventory_a->save();
    //                 $index+=1;
    //                 $model_id +=1;
    //                 $inventory->inventoryParts()->attach($inventory_a->id);
    //                 $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
    //                 $model_inventory_column_id++;
    //                 $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
    //                 $model_inventory_column_id++;
    
    //                 for($k = 1; $k < 3; $k++){
    //                     $code_b = $code_a.".$k";  
    //                     $inventory_b = new Inventory;
    //                     $inventory_b->model_id = $model_id;
    //                     $inventory_b->status_condition = 3;
    //                     $inventory_b->status_usage = 3;
    //                     $inventory_b->mig_id = "MIG-$index";  
    //                     $inventory_b->is_consumable = false; 
    //                     $inventory_b->save();
    //                     $index+=1;
    //                     $model_id +=1;
    //                     $inventory_a->inventoryParts()->attach($inventory_b->id);
    //                     $inventory_b->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
    //                     $model_inventory_column_id++;
    //                     $inventory_b->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
    //                     $model_inventory_column_id++;
    //                 }
    
    //                 $code_a = $code.".2";  
    //                 $inventory_a = new Inventory;
    //                 $inventory_a->model_id = $model_id;
    //                 $inventory_a->status_condition = 2;
    //                 $inventory_a->status_usage = 2;
    //                 $inventory_a->mig_id = "MIG-$index";
    //                 $inventory_a->is_consumable = false;
    //                 $inventory_a->save();
    //                 $index+=1;
    //                 $model_id +=1;
    //                 $inventory->inventoryParts()->attach($inventory_a->id);
    //                 $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
    //                 $model_inventory_column_id++;
    //                 $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
    //                 $model_inventory_column_id++;
    //             }
    //         }
    //     }
    // }

    // private function makeRelationshipInventories()
    // {
    //     $subject_id = 1;
    //     for($y = 0; $y < 4; $y++){
    //         for($i = 1; $i < 6; $i++){
    //             $relationship_inventory = new RelationshipInventory;
    //             $relationship_inventory->relationship_id = $i;
    //             $relationship_inventory->subject_id = $subject_id;
    //             $relationship_inventory->is_inverse = random_int(0, 1) ? true : false;
    //             $relationship_inventory->connected_id = random_int(2, 16);
    //             $relationship_inventory->type_id = random_int(-4, -1);
    //             $relationship_inventory->save();
    //         }
    //         $subject_id++;
    //     }
    // }
}
