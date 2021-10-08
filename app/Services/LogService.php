<?php

namespace App\Services;
use App\Services\GeneralService;
use App\ActivityLogInventory;
use App\ActivityLogInventoryPivot;
use App\ActivityLogInventoryRelationship;
use App\ActivityLogInventoryTicket;
use App\Inventory;
use App\ModelInventory;
use App\ModelInventoryColumn;
use App\User;

class LogService
{
    public function __construct()
    {
        $generalService = new GeneralService;
        $this->current_timestamp = $generalService->getTimeNow();
    }

    // Get Inventory Log

    public function getActivityInventoryLogs($id, $route_name)
    {
        $users = User::select('user_id', 'fullname')->get();

        try{
            $logs = [];
            if($id === null) return response()->json(["success" => false, "message" => "Silahkan Tambahkan Parameter Id"], 400);
            $models = ModelInventory::select('id', 'name')->withTrashed()->get();
            
            $inventory_pivot_logs = ActivityLogInventoryPivot::where('subject_id', $id)->get();
            foreach($inventory_pivot_logs as $inventory_pivot_log){
                $properties = $inventory_pivot_log->properties;
                // $inventory_parent = Inventory::withTrashed()->find($inventory_pivot_log->properties->attributes->parent_id);
                // $inventory_parent_model = $models->where('id', $inventory_parent['model_id'])->first();
                // $properties->attributes->parent_model_name = $inventory_parent_model ? $inventory_parent_model->name : "Model Id Parent Tidak Ditemukan";
                $user = $users->find($inventory_pivot_log->causer_id);
                if($user === null) $causer_name = "Not Found";
                else $causer_name = $user->fullname;
                $temp = (object) [
                    'date' => $inventory_pivot_log->created_at,
                    'description' => $inventory_pivot_log->log_name.' Inventory Pivot',
                    'properties' => $properties,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_pivot_log->description ? $inventory_pivot_log->description : "-"
                ];
                array_push($logs, $temp);
            } 
            
            $inventory_logs = ActivityLogInventory::where('subject_id', $id)->get();
            foreach($inventory_logs as $inventory_log){
                if($inventory_log->log_name === 'Notes'){
                    $user = $users->find($inventory_pivot_log->causer_id);
                    if($user === null) $causer_name = "Not Found";
                    else $causer_name = $user->fullname;
                    $temp = (object) [
                        'date' => $inventory_log->created_at,
                        'description' => $inventory_log->log_name.' Inventory',
                        'causer_name' => $causer_name,
                        'notes' => $inventory_log->description ? $inventory_log->description : "-"
                    ];
                    array_push($logs, $temp);
                    continue;
                }
                $properties = $inventory_log->properties;
                if($inventory_log->log_name === 'Created'){
                    $model = $models->where('id', $properties->attributes->model_id)->first();
                    if($model === null) $model_name = "Id Model Tidak Ditemukan";
                    else $model_name = $model->name;
                    $properties->attributes->model_name = $model_name;
                } else {
                    if (isset($properties->attributes->model_id)){
                        $model = $models->where('id', $properties->attributes->model_id)->first();
                        if($model === null) $model_name = "Id Model Tidak Ditemukan";
                        else $model_name = $model->name;
                        $properties->attributes->model_name = $model_name;
                    }
                }
                $user = $users->find($inventory_pivot_log->causer_id);
                if($user === null) $causer_name = "Not Found";
                else $causer_name = $user->fullname;

                $temp = (object) [
                    'date' => $inventory_log->created_at,
                    'description' => $inventory_log->log_name.' Inventory',
                    'properties' => $properties,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_log->description ? $inventory_log->description : "-"
                ];
                array_push($logs, $temp);
            }   
            
            
            
            usort($logs, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });
            
            $inventory_relationship_logs = ActivityLogInventoryRelationship::where('subject_id', $id)->get();
            $relationship_logs = [];
            foreach($inventory_relationship_logs as $inventory_relationship_log){
                $user = $users->find($inventory_pivot_log->causer_id);
                if($user === null) $causer_name = "Not Found";
                else $causer_name = $user->fullname;

                $temp = (object) [
                    'date' => $inventory_relationship_log->created_at,
                    'description' => $inventory_relationship_log->log_name.' Inventory Relationship',
                    'properties' => $inventory_relationship_log->properties,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_relationship_log->description ? $inventory_relationship_log->description : "-"
                ];
                array_push($relationship_logs, $temp);
            }

            usort($relationship_logs, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });
            
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["inventory" => $logs, "relationship" => $relationship_logs], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    // Inventory Log

    public function createLogInventory($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventory;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Created";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function updateLogInventory($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventory;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Updated";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function deleteLogInventory($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventory;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Deleted";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function noteLogInventory($subject_id, $causer_id, $notes = null)
    {
        $log = new ActivityLogInventory;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = null;
        $log->description = $notes;
        $log->log_name = "Notes";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    // Inventory Pivot Log

    public function createLogInventoryPivot($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventoryPivot;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Created";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function updateLogInventoryPivot($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventoryPivot;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Updated";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function deleteLogInventoryPivot($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventoryPivot;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Deleted";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    // Relationship Log

    public function createLogInventoryRelationship($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventoryRelationship;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Created";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function updateLogInventoryRelationship($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventoryRelationship;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Updated";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function deleteLogInventoryRelationship($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogInventoryRelationship;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Deleted";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }
}