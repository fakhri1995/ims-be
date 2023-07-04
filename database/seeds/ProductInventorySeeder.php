<?php

use App\ProductInventory;
use App\Category;
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
            $product->product_id = $data['product_id'];
            $product->description = $data['description'];
            $product->price = $data['price'];
            $product->price_option = $data['price_option'];
            $product->model_id = $data['model_id'];
            $product->category_id = $data['category_id'];
            $product->created_at = Date('Y-m-d H:i:s');
            $product->updated_at = Date('Y-m-d H:i:s');
            $product->save();
        }

        $datas = $this->defaultCategory();
        foreach($datas as $data){
            $category = new Category();
            $category->name = $data['name'];
            $category->created_at = Date('Y-m-d H:i:s');
            $category->updated_at = Date('Y-m-d H:i:s');
            $category->save();
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
                "product_id" => "0001",
                "description" => "",
                "price" => 1000,
                "price_option" => "Bulan",
                "model_id" => 1,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk B",
                "product_id" => "0002",
                "description" => "",
                "price" => 1000,
                "price_option" => "Jam",
                "model_id" => 2,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk C",
                "product_id" => "0003",
                "description" => "",
                "price" => 1000,
                "price_option" => "Tahun",
                "model_id" => 3,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk D",
                "product_id" => "0004",
                "description" => "",
                "price" => 1000,
                "price_option" => "Hari",
                "model_id" => 4,
                "category_id" => 1
            ],
            [
                "name" => "PC Laptop merk E",
                "product_id" => "0005",
                "description" => "",
                "price" => 1000,
                "price_option" => "Tahun",
                "model_id" => 5,
                "category_id" => 1
            ],
            [
                "name" => "TV merk A",
                "product_id" => "0006",
                "description" => "",
                "price" => 1000,
                "price_option" => "Hari",
                "model_id" => 6,
                "category_id" => 2
            ],
            [
                "name" => "TV merk B",
                "product_id" => "0007",
                "description" => "",
                "price" => 1000,
                "price_option" => "Hari",
                "model_id" => 7,
                "category_id" => 2
            ],
            [
                "name" => "TV merk C",
                "product_id" => "0008",
                "description" => "",
                "price" => 1000,
                "price_option" => "Hari",
                "model_id" => 8,
                "category_id" => 2
            ],
            [
                "name" => "TV merk D",
                "product_id" => "0009",
                "description" => "",
                "price" => 1000,
                "price_option" => "Bulan",
                "model_id" => 9,
                "category_id" => 2
            ],
            [
                "name" => "TV merk E",
                "product_id" => "0010",
                "description" => "",
                "price" => 1000,
                "price_option" => "Bulan",
                "model_id" => 10,
                "category_id" => 2
            ],
        ];
        return $data;
    }
    private function defaultCategory(){
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
}
