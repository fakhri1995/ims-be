<?php 

namespace App\Services;


use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;

class ExampleService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    
    public function getExamples(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            //your code
            $data = []; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getExample(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            //your code
            $data = []; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addExample(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{ 
            //your code
            $data = []; //what you want to send
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateExample(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            //your code
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteExample(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            //your code
            $data = [];//what you want to send
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

}