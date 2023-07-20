<?php 

namespace App\Services;

use App\Contract;
use App\ContractProduct;
use App\Product;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContractService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    
    public function getContracts(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "page" => "numeric",
            "rows" => "numeric|max:50",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try{
            $contracts = Contract::with(["client","requester"])->paginate();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{
            $id = $request->id;
            $contract = Contract::with(["client","requester","services"])->find($id);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

            $is_posted_rules = !$request->is_posted ? "" : "required|";

            $requestData = $request->all();
            $validator = Validator::make($requestData, [
                "contract_number" => $is_posted_rules."nullable",
                "title" => $is_posted_rules."nullable",
                "client_id" => $is_posted_rules."numeric|nullable",
                "requester_id" => $is_posted_rules."numeric|nullable",
                "initial_date" => $is_posted_rules."date|nullable",
                "start_date" => $is_posted_rules."date|nullable",
                "end_date" => $is_posted_rules."date|nullable",
                "is_posted" => $is_posted_rules."boolean",
                "extras" => "array|nullable",
                "extras.*.type" => "in:1,2,3|required_with:extras",
                "extras.*.name" => "required_with:extras",
                "extras.*.value" => [
                    "required_with:extras",
                    function ($attribute, $value, $fail) use ($requestData) {
                        $index = explode(".",$attribute)[1];
                        $type = $requestData["extras"][$index]["type"];
                        if ($type == 1 && !is_string($value)) {
                            $fail("The ".$attribute . " must be an string if type is 1.");
                        }
                        if ($type == 2 && !is_array($value)) {
                            $fail("The ".$attribute . " must be an array if type is 2.");
                        }
                        if ($type == 3 && (!is_a($value, "Illuminate\Http\UploadedFile") || !$value->isValid())) {
                            $fail("The ".$attribute . " must be a valid file if type is 3.");
                        }
                    }
                ],
            ]);

            if($validator->fails()){
                $errors = $validator->errors()->all();
                return ["success" => false, "message" => $errors, "status" => 400];
            }
            
            $contract = new Contract();
            $contract->contract_number = $request->contract_number ?? NULL;
            $contract->title = $request->title ?? NULL;
            $contract->client_id = $request->client_id ?? NULL;
            $contract->requester_id = $request->requester_id ?? NULL;
            $contract->initial_date = $request->initial_date ?? NULL;
            $contract->start_date = $request->start_date ?? NULL;
            $contract->end_date = $request->end_date ?? NULL;
            $contract->is_posted = $request->is_posted ?? 0;
            $current_time = date('Y-m-d H:i:s');            
            $contract->created_at = $current_time;
            $contract->updated_at = $current_time;
            $contract->save();

            $extras_arr = $request->extras ?? [];
            $extras = [];
            $fileService = new FileService;
            foreach($extras_arr as $e){
                $extra = [];
                $extra["key"] = Str::uuid()->toString();
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];
                if($e['type'] == 3) {
                    $file = $e['value'];
                    $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                    if($add_file_response["success"]){
                        $extra['value'] = $add_file_response["new_data"];
                    }
                }else{
                    $extra["value"] = $e['value'];
                }
                $extras[] = $extra;
            }

            $contract->extras = $extras;
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
        
        $id = $request->id;
        $contract = Contract::find($id);
        if(!$contract) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
        $old_extras = $contract->extras;
        $old_extras_remap = [];
        $old_extra_key_array = [];
        foreach($old_extras as $oe){
            $old_extra_key_array[] = $oe['key'];
            $old_extras_remap[$oe['key']] = $oe;
        }

        $is_posted_rules = !$request->is_posted ? "" : "required|";
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            "contract_number" => $is_posted_rules."nullable",
            "title" => $is_posted_rules."nullable",
            "client_id" => $is_posted_rules."numeric|nullable",
            "requester_id" => $is_posted_rules."numeric|nullable",
            "initial_date" => $is_posted_rules."date|nullable",
            "start_date" => $is_posted_rules."date|nullable",
            "end_date" => $is_posted_rules."date|nullable",
            "is_posted" => $is_posted_rules."boolean",
            "extras" => "array|nullable",
            "extras.*.key" => "nullable|in:".implode(",",$old_extra_key_array),
            "extras.*.type" => [
                "in:1,2,3",
                "required_with:extras",
                function ($attribute, $value, $fail) use ($requestData, $old_extras_remap) {
                    $index = explode(".",$attribute)[1];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if($issetInOld && ($value != $old_extras_remap[$key]['type'])){
                        $fail("The ".$attribute . " can be change.");
                    }
                }
            ],
            "extras.*.name" => "required_with:extras",
            "extras.*.value" => [
                "required_without:extras.*.key",
                function ($attribute, $value, $fail) use ($requestData) {
                    $index = explode(".",$attribute)[1];
                    $type = $requestData["extras"][$index]["type"];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);    
                    if ($type == 1 && !is_string($value)) {
                        $fail("The ".$attribute . " must be an string if type is 1.");
                    }
                    if ($type == 2 && !is_array($value)) {
                        $fail("The ".$attribute . " must be an array if type is 2.");
                    }
                    if ($type == 3) {
                        if(($key == NULL && !$issetInOld) && (!is_a($value, "Illuminate\Http\UploadedFile") || !$value->isValid())){
                            $fail("The ".$attribute . " must be a valid file if type is 3 and key not exist in old data.");
                        }
                    }
                }
            ],
            "extras.*.is_deleted" => "boolean|nullable",
            "services" => "array|nullable",
            "services.*.product_id" => "required_with:services|numeric",
            "services.*.pax" => "required_with:services",
            "services.*.price" => "required_with:services",
            "services.*.unit" => "required_with:services:in:jam,hari,bulan,tahun"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
            
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

            // Extra Section
            $fileService = new FileService;
            $extras_arr = $request->extras ?? [];
            $extras = [];
            $extra_key_array = [];
            foreach($extras_arr as $k => $e){
                $extra = [];
                // $extra["key"] = Str::uuid()->toString();
                $isset_key = isset($e["key"]);
                $is_deleted = isset($e["is_deleted"]) ? $e["is_deleted"] : false;
                $extra["key"] = $key = $isset_key ? $e["key"] : Str::uuid()->toString();
                $extra_key_array[] = $key;
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];
                
                if($isset_key){
                    if($is_deleted){ // for_delete
                        if($e["type"] == 3){
                            $fileService->deleteForceFile($old_extras_remap[$key]["value"]["id"]);
                        }
                        unset($old_extras_remap[$key]);
                        unset($extras_arr[$k]);
                        continue;
                    }else{
                        if($e["type"] == 3){ //for_update
                            if(is_a($e["value"], "Illuminate\Http\UploadedFile")){
                                try{
                                    $fileService->deleteForceFile($old_extras_remap[$key]["value"]['id']);
                                }catch(Exception $err){
                                    echo $err;
                                }
                                $file = $e["value"];
                                $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                                if($add_file_response["success"]){
                                    $extra["value"] = $add_file_response["new_data"];
                                }
                            }else{
                                try{
                                    $extra["value"] = $old_extras_remap[$key]["value"]['id'];
                                }catch(Exception $err){
                                    echo $err;
                                }
                            }
                        }else{
                            $extra["value"] = $e['value'];
                        }
                    }
                }else{
                    // skip if new but is_deleted true
                    if($is_deleted) continue;
                    // new data
                    if($e['type'] == 3) {
                        $file = $e['value'];
                        $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                        if($add_file_response["success"]){
                            $extra['value'] = $add_file_response["new_data"];
                        }
                    }else{
                        $extra["value"] = $e['value'];
                    }
                }
                $extras[] = $extra;
            }

            $diff_extra = array_diff($old_extra_key_array, $extra_key_array);
            $old_extras = [];
            foreach($diff_extra as $d){
                $old_extras[] = $old_extras_remap[$d];
            }

            $extras = array_merge($old_extras,$extras);
            $contract->extras = $extras;

            // SERVICES
            ContractProduct::where("contract_id",$contract->id)->delete();
            $services = $request->services ?? [];
            $serviceData = [];
            foreach($services as $s){
                $s = (object)$s;
                $serviceData[] = [
                    "product_id" => $s->product_id,
                    "pax" => $s->pax,
                    "price" => $s->price,
                    "unit" => $s->unit,
                    "contract_id" => $contract->id,
                    "created_at" => $current_time,
                    "updated_at" => $current_time,
                ];
            };
            $contract->save();
            $services = ContractProduct::insert($serviceData);

            try{
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function deleteContract(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $validator = Validator::make($request->all(), [
                "id" => "required|numeric",
            ]);
            
            if($validator->fails()){
                $errors = $validator->errors()->all();
                return ["success" => false, "message" => $errors, "status" => 400];
            }

            $id = $request->id;
            $contract = Contract::find($id);
            $contract->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $contract, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

}