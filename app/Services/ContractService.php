<?php 

namespace App\Services;

use App\Contract;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContractService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    
    public function getContracts(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $contracts = Contract::paginate();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $contract = Contract::find($id);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

            
            $contract = new Contract();
            $contract->contract_number = $request->contract_number ?? NULL;
            $contract->title = $request->title ?? NULL;
            $contract->client_id = $request->client_id ?? NULL;
            $contract->requester_id = $request->requester_id ?? NULL;
            $contract->initial_date = $request->initial_date ?? NULL;
            $contract->start_date = $request->start_date ?? NULL;
            $contract->end_date = $request->end_date ?? NULL;
            $contract->is_posted = $request->is_posted ?? 0;
            
            $extras_arr = $request->extras ?? [];
            $extras = [];
            foreach($extras_arr as $e){
                $extra = [];
                $extra["key"] = Str::uuid()->toString();
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];
                $extra["value"] = $e['value'];
                $extras[] = $extra;
            }

            $contract->extras = $extras;


            $current_time = date('Y-m-d H:i:s');            
            $contract->created_at = $current_time;
            $contract->updated_at = $current_time;
            $contract->save();

            try{ 
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $contract = Contract::find($id);
            $contract->contract_number = $request->contract_number ?? NULL;
            $contract->title = $request->title ?? NULL;
            $contract->client_id = $request->client_id ?? NULL;
            $contract->requester_id = $request->requester_id ?? NULL;
            $contract->initial_date = $request->initial_date ?? NULL;
            $contract->start_date = $request->start_date ?? NULL;
            $contract->end_date = $request->end_date ?? NULL;
            $contract->extras = $request->extras ?? NULL;
            $contract->is_posted = $request->is_posted ?? NULL;
            $current_time = date('Y-m-d H:i:s');          
            $contract->updated_at = $current_time;

            $extras_arr = $request->extras ?? [];
            $extras = [];
            foreach($extras_arr as $e){
                $extra = [];
                $extra["key"] = Str::uuid()->toString();
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];
                $extra["value"] = $e['value'];
                $extras[] = $extra;
            }

            $contract->extras = $extras;
            
            $contract->save();
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $contract = Contract::find($id);
            $contract->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

}