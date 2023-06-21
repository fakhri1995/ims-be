<?php

namespace App\Services;
use App\AccessFeature;
use App\ModelInventory;
use App\ProductInventory;
use App\ProductInventoryCategory;
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
        // if($access["success"] === false)c return $access;
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
            $model_ids = $request->model_ids ? explode(",",$request->model_ids) : NULL;
            $keyword = $request->keyword ?? NULL;
            $category_id = $request->category_id ?? NULL;
            $products = ProductInventory::with(['modelInventory', 'category']);
            $rows = $request->rows ?? 5;
            
            // filter
            if($keyword) $products = $products->where("name","LIKE", "%$keyword%");
            if($model_ids) $products = $products->whereIn("model_id",$model_ids);
            if($category_id) $products = $products->whereIn("category_id",$category_id);
            
            // sort
            $sort_by = $request->sort_by ?? NULL;
            $sort_type = $request->get('sort_type','asc');
            if($sort_by == "name") $products = $products->orderBy('name',$sort_type);
            //if($sort_by == "count") $products = $products->orderBy('inventories_count',$sort_type); TODO
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
        $resume = ProductInventory::with(['modelInventory', 'category', 'priceOptions'])->find($id);
        if(!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resume, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addProductInventory(Request $request, $route_name)
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function updateProductInventory(Request $request, $route_name)
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function deleteProductInventory(Request $request, $route_name)
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }
}