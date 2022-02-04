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

    private function makeBulkAssets()
    {
        $name_asset = "Asset ";
        $asset_name = ['', 'IT Asset', 'Non-IT Asset', 'Rotable', 'Component'];
        for($i = 1; $i < 5; $i++){
            $code = "00$i";  
            $asset = new Asset;
            $asset->parent_id = null;
            $asset->name = $asset_name[$i];
            $asset->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
            $asset->required_sn = true;
            $asset->code = $code;
            $asset->save();
            for($j = 1; $j < 2; $j++){
                $code_a = $code.".00$j";  
                $asset_a = new Asset;
                $asset_a->parent_id = $asset->id;
                $asset_a->name = $name_asset . $code_a;
                $asset_a->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
                $asset_a->required_sn = true;
                $asset_a->code = $code_a;
                $asset_a->save();
                for($k = 1; $k < 3; $k++){
                    $code_b = $code_a.".00$k";  
                    $asset_b = new Asset;
                    $asset_b->parent_id = $asset_a->id;
                    $asset_b->name = $name_asset . $code_b;
                    $asset_b->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
                    $asset_b->required_sn = true;
                    $asset_b->code = $code_b;
                    $asset_b->save();
                }
            }
        }
    }

    private function makeBulkAssetColumns()
    {
        for($i = 1; $i < 16; $i++){
            $model_inventory_column = new AssetColumn;
            $model_inventory_column->asset_id = $i;
            $model_inventory_column->name = "Kapasitas";
            $model_inventory_column->data_type = "String";
            $model_inventory_column->default = "4 GB";
            $model_inventory_column->required = false;
            $model_inventory_column->save();

            $model_inventory_column = new AssetColumn;
            $model_inventory_column->asset_id = $i;
            $model_inventory_column->name = "Processor";
            $model_inventory_column->data_type = "String";
            $model_inventory_column->required = true;
            $model_inventory_column->save();
        }
    }

    private function makeBulkModels()
    {   
        $name_model = "Model ";
        for($x = 1; $x < 5; $x++){
            for($i = 0; $i < 4; $i++){
                $id = $i * 4 + 1;
                $code_int = $i+1;
                $code = "$code_int";  
                $model = new ModelInventory;
                $model->asset_id = $id;
                $model->name = $name_model."$x ".$code;
                $model->description = "Bulk Model Seeder";
                $model->manufacturer_id = 1;
                $model->required_sn = false;
                $model->save();

                $code_a = $code.".1";  
                $model_a = new ModelInventory;
                $model_a->asset_id = $id+1;
                $model_a->name = $name_model."$x ".$code_a;
                $model_a->description = "Bulk Model Seeder";
                $model_a->manufacturer_id = 1;
                $model_a->required_sn = false;
                $model_a->save();
                $model->modelParts()->attach($model_a->id, ['quantity' => 1]);

                for($k = 2; $k < 4; $k++){
                    $index = $k-1;
                    $code_b = $code_a.".$index";  
                    $model_b = new ModelInventory;
                    $model_b->asset_id = $id+$k;
                    $model_b->name = $name_model."$x ".$code_b;
                    $model_b->description = "Bulk Model Seeder";
                    $model_b->manufacturer_id = 1;
                    $model_b->required_sn = true;
                    $model_b->save();
                    $model_a->modelParts()->attach($model_b->id, ['quantity' => 1]);
                }

                $code_a = $code.".2";  
                $model_a = new ModelInventory;
                $model_a->asset_id = $id+1;
                $model_a->name = $name_model."$x ".$code_a;
                $model_a->description = "Bulk Model Seeder";
                $model_a->manufacturer_id = 1;
                $model_a->required_sn = false;
                $model_a->save();
                $model->modelParts()->attach($model_a->id, ['quantity' => 1]);
            }
        }
    }

    private function makeBulkModelInventoryColumns()
    {
        for($i = 1; $i < 81; $i++){
            $model_inventory_column = new ModelInventoryColumn;
            $model_inventory_column->model_id = $i;
            $model_inventory_column->name = "Kapasitas";
            $model_inventory_column->data_type = "String";
            $model_inventory_column->default = "4 GB";
            $model_inventory_column->required = false;
            $model_inventory_column->save();

            $model_inventory_column = new ModelInventoryColumn;
            $model_inventory_column->model_id = $i;
            $model_inventory_column->name = "Processor";
            $model_inventory_column->data_type = "String";
            $model_inventory_column->required = true;
            $model_inventory_column->save();
        }
    }

    private function makeBulkInventories()
    {   
        $index = 1;
        for($v = 1; $v < 3; $v++){
            $model_id = 1;
            $model_inventory_column_id = 1;
            for($x = 1; $x < 5; $x++){
                for($i = 0; $i < 4; $i++){
                    $id = $i * 4 + 1;
                    $code_int = $i+1;
                    $code = "$code_int";  
                    $inventory = new Inventory;
                    $inventory->model_id = $model_id;
                    $inventory->status_condition = 1;
                    $inventory->status_usage = random_int(1, 3);
                    $inventory->mig_id = "MIG-$index";
                    $inventory->is_consumable = false;
                    $inventory->save();
                    $index+=1;
                    $model_id +=1;
                    $inventory->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
                    $model_inventory_column_id++;
                    $inventory->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
                    $model_inventory_column_id++;
    
                    $code_a = $code.".1";  
                    $inventory_a = new Inventory;
                    $inventory_a->model_id = $model_id;
                    $inventory_a->status_condition = 2;
                    $inventory_a->status_usage = 2;
                    $inventory_a->mig_id = "MIG-$index";
                    $inventory_a->is_consumable = false;
                    $inventory_a->save();
                    $index+=1;
                    $model_id +=1;
                    $inventory->inventoryParts()->attach($inventory_a->id);
                    $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
                    $model_inventory_column_id++;
                    $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
                    $model_inventory_column_id++;
    
                    for($k = 1; $k < 3; $k++){
                        $code_b = $code_a.".$k";  
                        $inventory_b = new Inventory;
                        $inventory_b->model_id = $model_id;
                        $inventory_b->status_condition = 3;
                        $inventory_b->status_usage = 3;
                        $inventory_b->mig_id = "MIG-$index";  
                        $inventory_b->is_consumable = false; 
                        $inventory_b->save();
                        $index+=1;
                        $model_id +=1;
                        $inventory_a->inventoryParts()->attach($inventory_b->id);
                        $inventory_b->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
                        $model_inventory_column_id++;
                        $inventory_b->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
                        $model_inventory_column_id++;
                    }
    
                    $code_a = $code.".2";  
                    $inventory_a = new Inventory;
                    $inventory_a->model_id = $model_id;
                    $inventory_a->status_condition = 2;
                    $inventory_a->status_usage = 2;
                    $inventory_a->mig_id = "MIG-$index";
                    $inventory_a->is_consumable = false;
                    $inventory_a->save();
                    $index+=1;
                    $model_id +=1;
                    $inventory->inventoryParts()->attach($inventory_a->id);
                    $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "4 GB"]);
                    $model_inventory_column_id++;
                    $inventory_a->additionalAttributes()->attach($model_inventory_column_id, ['value' => "AMD Ryzen™ 7 5800X"]);
                    $model_inventory_column_id++;
                }
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

    private function makeRelationshipInventories()
    {
        $subject_id = 1;
        for($y = 0; $y < 4; $y++){
            for($i = 1; $i < 6; $i++){
                $relationship_inventory = new RelationshipInventory;
                $relationship_inventory->relationship_id = $i;
                $relationship_inventory->subject_id = $subject_id;
                $relationship_inventory->is_inverse = random_int(0, 1) ? true : false;
                $relationship_inventory->connected_id = random_int(2, 16);
                $relationship_inventory->type_id = random_int(-4, -1);
                $relationship_inventory->save();
            }
            $subject_id++;
        }
    }

    public function run()
    {
        $this->makeBulkAssets();
        $this->makeBulkAssetColumns();
        $this->makeBulkModels();
        $this->makeBulkModelInventoryColumns();
        $this->makeBulkInventories();
        $this->makeStatusConditions();
        $this->makeStatusUsages();
        $this->makeRelationships();
        $this->makeRelationshipInventories();
    }

}
