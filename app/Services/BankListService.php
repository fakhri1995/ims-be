<?php 

namespace App\Services;
use App\BankList;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;

class BankListService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function getBankLists(Request $request, $route_name)
    {   
        
        try{

            $name = $request->name ?? NULL;

            $bankLists = new BankList;
            if($name) $bankLists = $bankLists->where("name","LIKE", "%".$name."%");
            
            $bankLists = $bankLists->get();
            return ["success" => true, "message" => "Data berhasil diambil", "data" => $bankLists, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

}