<?php

namespace App\Services;
use App\AccessFeature;
use App\ModelInventory;
use App\ProductInventory;
use App\ProductInventoryCategory;
use App\ProductInventoryPriceOption;
use App\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryService
{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

  public function getProductInventories(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "sort_by" => "in:name,count,price",
            "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
        try{
            $model_id = $request->model_id ?? NULL;
            $keyword = $request->keyword ?? NULL;
            $category_id = $request->category_id ?? NULL;
            $products = ProductInventory::with(['modelInventory', 'category']);
            $rows = $request->rows ?? 5;
            $is_active = $request->is_active ?? NULL;
            // filter
            if($keyword) $products = $products->where("name","LIKE", "%$keyword%");
            if($category_id) $products = $products->where("category_id",$category_id);
            if($model_id) $products = $products->where("model_id",$model_id);
            if($is_active != NULL) $products = $products->where("is_active", $is_active);
            
            // sort
            $sort_by = $request->sort_by ?? NULL;
            $sort_type = $request->get('sort_type','asc');
            if($sort_by == "name") $products = $products->orderBy('name',$sort_type);
            //if($sort_by == "count") $products = $products->orderBy('model_inventory.inventories_count',$sort_type); TODO
            if($sort_by == "price") $products = $products->orderBy('price',$sort_type);

            $products = $products->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $products, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    public function getProductInventory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "exists:App\ProductInventory,id|numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $product = ProductInventory::with(['modelInventory', 'category', 'priceOptions'])->find($id);
        if(!$product) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $product, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addProductInventory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "description" => "nullable",
            "price" => "required|numeric",
            "price_option_id" => "required|numeric|nullable",
            "model_id" => "numeric|nullable",
            "category_id" => "numeric|nullable"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        try{ 
            // Resume Basic Information
            $product = new ProductInventory();
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->price_option_id = $request->price_option_id;
            $product->model_id = $request->model_id;
            $product->category_id = $request->category_id;
            $product->is_active = $request->is_active;
            $product->created_at = Date('Y-m-d H:i:s');
            $product->updated_at = Date('Y-m-d H:i:s');

            // relations section
            $price_option_id = $request->price_option_id;
            $price_option = ProductInventoryPriceOption::find($price_option_id);
            if(!$price_option) return ["success" => false, "message" => "Data Price Option Tidak Ditemukan", "status" => 400];

            $model_id = $request->assessment_id;
            if($model_id){
                $model = ModelInventory::find($model_id);
                if(!$model) return ["success" => false, "message" => "Data Model Tidak Ditemukan", "status" => 400];
            }

            $category_id = $request->category_id;
            if($category_id){
                $category = ProductInventoryCategory::find($category_id);
                if(!$category) return ["success" => false, "message" => "Data Category Tidak Ditemukan", "status" => 400];
            }
            
            
            if(!$product->save()) return ["success" => false, "message" => "Gagal Menambah Produk", "status" => 400];

            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $product->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateProductInventory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\ProductInventory,id",
            "name" => "nullable",
            "description" => "nullable",
            "price" => "numeric|nullable",
            "price_option_id" => "numeric|nullable",
            "model_id" => "numeric|nullable",
            "category_id" => "numeric|nullable",
            "is_active" => "numeric|nullable"
        ]);
        

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $product_id = $request->id;
        $product = ProductInventory::find($product_id);
        if(!$product) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        if($request->name)$product->name = $request->name;
        if($request->description)$product->description = $request->description;
        if($request->price)$product->price = $request->price;
        if($request->is_active)$product->is_active = $request->is_active;
        $product->updated_at = Date('Y-m-d H:i:s');

        // if price option changes
        if($request->price_option_id){
            $price_option = ProductInventoryPriceOption::find($request->price_option_id);
            if(!$price_option) return ["success" => false, "message" => "Data Price Option Tidak Ditemukan", "status" => 400];

            $product->price_option_id = $request->price_option_id;
        }

        // if model changes
        if($request->model_id){
            $model = ModelInventory::find($request->model_id);
            if(!$model) return ["success" => false, "message" => "Data Model Tidak Ditemukan", "status" => 400];

            $product->model_id = $request->model_id;
        }

        // if category changes
        if($request->category_id){
            $category = ProductInventoryCategory::find($request->category_id);
            if(!$category) return ["success" => false, "message" => "Data Category Tidak Ditemukan", "status" => 400];

            $product->category_id = $request->category_id;
        }

        if(!$product->save()) return ["success" => false, "message" => "Gagal Mengubah Produk", "status" => 400];
        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
    }

    
    //PRODUCT INVENTORY CATEGORY
    public function getProductInventoryCategories(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $products = ProductInventoryCategory::with(['products'])->withCount('products')->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $products, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    public function getProductInventoryCategory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $id = $request->id;
        try{
            $products = ProductInventoryCategory::with(['products'])->find($id);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $products, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    public function deleteProductInventory(Request $request, $route_name)
    {
        
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\ProductInventory,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $product = ProductInventory::find($id);
        if(!$product) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
            $product->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $product, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function addProductInventoryCategory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "name" => "required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        try{ 
            // Resume Basic Information
            $category = new ProductInventoryCategory();
            $category->name = $request->name;
            
            if(!$category->save()) return ["success" => false, "message" => "Gagal Menambah Category", "status" => 400];

            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $category->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteProductInventoryCategory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\ProductInventoryCategory,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $category = ProductInventoryCategory::find($id);
        if(!$category) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
            $category->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $category, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateProductInventoryCategory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:App\ProductInventory,id",
            "name" => "required"
        ]);
        

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $category_id = $request->id;
        $category = ProductInventoryCategory::find($category_id);
        if(!$category) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        $category->name = $request->name;

        if(!$category->save()) return ["success" => false, "message" => "Gagal Mengubah Kategori", "status" => 400];
        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
    }
}