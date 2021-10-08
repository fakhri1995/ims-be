<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LogService;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->logService = new LogService;
    }

    // Normal Route

    public function getActivityInventoryLogs(Request $request)
    {

        $route_name = "LOG_INVENTORY_GET";
        
        $id = $request->get('id', null);
        
        $response = $this->logService->getActivityInventoryLogs($id, $route_name);
        return response()->json($response, $response['status']);

        $header = $request->header("Authorization");
        $headers = ['Authorization' => $header];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }

        
        $headers = [
            'Authorization' => $header,
            'content-type' => 'application/json'
        ];

        try{
            $check_api_user = true;
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true&order_by=asc', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                $users = [['id' => 0, 'name' => "Error API Get List Account"]];
                $check_api_user = false;
            } else {
                $users = [];
                foreach($response['data']['accounts'] as $user){
                    $users[] = ['id' => $user['user_id'], 'name' => $user['fullname']];
                }
            //   return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $users]);  
            } 
        }catch(ClientException $err){
            $users = [['id' => 0, 'name' => "Error API Get List Account"]];
            $check_api_user = false;
        }
        

        $id = $request->get('id', null);
        try{
            $logs = [];
            if($id === null) return response()->json(["success" => false, "message" => "Silahkan Tambahkan Parameter Id"], 400);
            $models = ModelInventory::withTrashed()->get();
            $inventory_value_logs = Activity::where('log_name', 'Inventory Value')->where('properties->attributes->inventory_id', $id)->get();
            $inventory_columns = ModelInventoryColumn::withTrashed()->select('id','name')->get();
            foreach($inventory_value_logs as $inventory_value_log){
                if($inventory_value_log->description === 'updated'){
                    $inventory_decode = json_decode($inventory_value_log->properties,true);
                    $inventory_decode['attributes']['name'] = $inventory_columns->where('id', $inventory_decode['attributes']['model_inventory_column_id'])->first()->name;
                    $causer_name = "Not Found";
                    foreach($users as $user){
                        if($user['id'] === $inventory_value_log->causer_id){
                            $causer_name = $user['name'];
                            break;
                        }
                    }
                    $temp = (object) [
                        'date' => $inventory_value_log->created_at,
                        'description' => $inventory_value_log->description.' inventory value',
                        'properties' => $inventory_decode,
                        'causer_name' => $causer_name
                    ];
                    array_push($logs, $temp);
                }
            } 

            $inventory_pivot_logs = Activity::where('log_name', 'Inventory Pivot')->where('properties->attributes->child_id', $id)->get();
            foreach($inventory_pivot_logs as $inventory_pivot_log){
                $inventory_decode = json_decode($inventory_pivot_log->properties,true);
                $inventory_parent = Inventory::withTrashed()->find($inventory_decode['attributes']['parent_id']);
                $inventory_parent_model = $models->where('id', $inventory_parent['model_id'])->first();
                $inventory_decode['attributes']['parent_model_name'] = $inventory_parent_model ? $inventory_parent_model->name : "Model Id Parent Tidak Ditemukan";
                $causer_name = "Not Found";
                foreach($users as $user){
                    if($user['id'] === $inventory_value_log->causer_id){
                        $causer_name = $user['name'];
                        break;
                    }
                }
                $temp = (object) [
                    'date' => $inventory_pivot_log->created_at,
                    'description' => $inventory_pivot_log->description.' inventory pivot',
                    'properties' => $inventory_decode,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_pivot_log->causer_type ? $inventory_pivot_log->causer_type : "-"
                ];
                array_push($logs, $temp);
            } 
            
            $inventory_logs = Activity::where('log_name', 'Inventory')->where('subject_id', $id)->get();
            foreach($inventory_logs as $inventory_log){
                if($inventory_log->description === 'note'){
                    foreach($users as $user){
                        if($user['id'] === $inventory_value_log->causer_id){
                            $causer_name = $user['name'];
                            break;
                        }
                    }
                    $temp = (object) [
                        'date' => $inventory_log->created_at,
                        'description' => $inventory_log->description.' inventory',
                        'causer_name' => $causer_name,
                        'notes' => $inventory_log->causer_type ? $inventory_log->causer_type : "-"
                    ];
                    array_push($logs, $temp);
                    continue;
                }
                $inventory_decode = json_decode($inventory_log->properties,true);
                if($inventory_log->description === 'created'){
                    $model = $models->where('id', $inventory_decode['attributes']['model_id'])->first();
                    if($model === null) $model_name = "Id Model Tidak Ditemukan";
                    else $model_name = $model->name;
                    $inventory_decode['attributes']['model_name'] = $model_name;
                } else {
                    if (array_key_exists('model_id', $inventory_decode['attributes'])){
                        $model = $models->where('id', $inventory_decode['attributes']['model_id'])->first();
                        if($model === null) $model_name = "Id Model Tidak Ditemukan";
                        else $model_name = $model->name;
                        $inventory_decode['attributes']['model_name'] = $model_name;
                    }
                }
                $causer_name = "Not Found";
                foreach($users as $user){
                    if($user['id'] === $inventory_value_log->causer_id){
                        $causer_name = $user['name'];
                        break;
                    }
                }
                $temp = (object) [
                    'date' => $inventory_log->created_at,
                    'description' => $inventory_log->description.' inventory',
                    'properties' => $inventory_decode,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_log->causer_type ? $inventory_log->causer_type : "-"
                ];
                array_push($logs, $temp);
            }   

            usort($logs, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });

            $inventory_relationship_logs = Activity::where('log_name', 'Inventory Relationship')->where('properties->attributes->subject_id', $id)->get();
            $relationship_logs = [];
            foreach($inventory_relationship_logs as $inventory_relationship_log){
                $inventory_decode = json_decode($inventory_relationship_log->properties,true);
                if($inventory_relationship_log->description === 'updated'){
                    $properties = [];
                    if($inventory_decode['attributes']['relationship_asset_id'] !== $inventory_decode['old']['relationship_asset_id']){
                        $properties["attributes"]['relationship_asset_id'] = $inventory_decode['attributes']['relationship_asset_id'];
                    }
                    if($inventory_decode['attributes']['subject_id'] !== $inventory_decode['old']['subject_id']){
                        $properties["attributes"]['subject_id'] = $inventory_decode['attributes']['subject_id'];
                    } 
                    if($inventory_decode['attributes']['connected_id'] !== $inventory_decode['old']['connected_id']){
                        $properties["attributes"]['connected_id'] = $inventory_decode['attributes']['connected_id'];
                    } 
                    if($inventory_decode['attributes']['detail_connected_id'] !== $inventory_decode['old']['detail_connected_id']){
                        $properties["attributes"]['detail_connected_id'] = $inventory_decode['attributes']['detail_connected_id'];
                    }  
                    if($inventory_decode['attributes']['type_id'] !== $inventory_decode['old']['type_id']){
                        $properties["attributes"]['type_id'] = $inventory_decode['attributes']['type_id'];
                    }  
                    if($inventory_decode['attributes']['is_inverse'] !== $inventory_decode['old']['is_inverse']){
                        $properties["attributes"]['is_inverse'] = $inventory_decode['attributes']['is_inverse'];
                    }  
                    $inventory_decode = $properties;
                }
                $causer_name = "Not Found";
                foreach($users as $user){
                    if($user['id'] === $inventory_value_log->causer_id){
                        $causer_name = $user['name'];
                        break;
                    }
                }
                $temp = (object) [
                    'date' => $inventory_relationship_log->created_at,
                    'description' => $inventory_relationship_log->description.' inventory',
                    'properties' => $inventory_decode,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_relationship_log->causer_type ? $inventory_relationship_log->causer_type : "-"
                ];
                array_push($relationship_logs, $temp);
            }

            usort($relationship_logs, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });
            
            if($check_api_user === false) return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $logs, "users" => $users]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["inventory" => $logs, "relationship" => $relationship_logs]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}