<?php

use App\ProductInventory;
use App\ProductInventoryCategory;
use App\ProductInventoryPriceOption;
use Illuminate\Database\Seeder;

class ProductInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
     private function addDefaultProductInventory()
    {   
        $datas = $this->defaultProductInventory();
        foreach($datas as $data){
            $product = new ProductInventory();
            $product->name = $data['name'];
            $product->description = $data['description'];
            $product->price = $data['price'];
            $product->price_option_id = $data['price_option_id'];
            $product->model_id = $data['model_id'];
            $product->category_id = $data['category_id'];
            $product->created_at = Date('Y-m-d H:i:s');
            $product->updated_at = Date('Y-m-d H:i:s');
            $product->save();
        }

        $datas = $this->defaultProductInventoryCategory();
        foreach($datas as $data){
            $category = new ProductInventoryCategory();
            $category->name = $data['name'];
            $category->save();
        }

        $datas = $this->defaultProductInventoryOptions();
        foreach($datas as $data){
            $option = new ProductInventoryPriceOption();
            $option->name = $data['name'];
            $option->save();
        }
    }

    public function run()
    {
        $this->addDefaultProductInventory();
    }

    private function defaultProductInventory(){
        $data = [
            [
                "name" => "PC Laptop merk A",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 1,
                "model_id" => 1,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk B",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 2,
                "model_id" => 2,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk C",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 3,
                "model_id" => 3,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk D",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 4,
                "model_id" => 4,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk E",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 1,
                "model_id" => 5,
                "category_id" => 1
            ],
            [
                "name" => "TV merk A",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 2,
                "model_id" => 6,
                "category_id" => 2
            ],
            [
                "name" => "TV merk B",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 3,
                "model_id" => 7,
                "category_id" => 2
            ],
            [
                "name" => "TV merk C",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 4,
                "model_id" => 8,
                "category_id" => 2
            ],
            [
                "name" => "TV merk D",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 1,
                "model_id" => 9,
                "category_id" => 2
            ],
            [
                "name" => "TV merk E",
                "description" => "",
                "price" => 1000,
                "price_option_id" => 2,
                "model_id" => 10,
                "category_id" => 2
            ],
        ];
        return $data;
    }
    private function defaultProductInventoryCategory(){
        $data = [
            [
                "name" => "Laptops"
            ],
            [
                "name" => "TVs"
            ],
        ];
        return $data;
    }

    private function defaultProductInventoryOptions(){
        $data = [
            [
                "name" => "jam"
            ],
            [
                "name" => "hari"
            ],
            [
                "name" => "bulan"
            ],
            [
                "name" => "tahun"
            ],
        ];
        return $data;
    }
}
