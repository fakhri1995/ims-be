<?php

namespace App\Services;
use App\User;
use App\Ticket;
use App\Incident;
use App\Inventory;
use App\ModelInventory;
use App\ActivityLogInventory;
use App\ModelInventoryColumn;
use App\Services\GeneralService;
use App\ActivityLogInventoryPivot;
use App\ActivityLogTicket;
use App\Services\CheckRouteService;
use App\ActivityLogInventoryRelationship;

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
        $checkRouteService = new CheckRouteService;
        $access = $checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $users = User::select('user_id', 'fullname')->get();

        try{
            $inventory_logs = $this->inventoryLogs($id);
            $relationship_logs = $this->relationshipInventoryLog($id);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["inventory" => $inventory_logs, "relationship" => $relationship_logs], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function inventoryLogs($id)
    {
        try{
            $users = User::select('user_id', 'fullname')->get();
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
                    $user = $users->find($inventory_log->causer_id);
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
                $user = $users->find($inventory_log->causer_id);
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
            return $logs;
        }
        catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function relationshipInventoryLog($id)
    {
        $users = User::select('user_id', 'fullname')->get();
        $relationship_logs = [];
        $inventory_relationship_logs = ActivityLogInventoryRelationship::where('subject_id', $id)->get();
        foreach($inventory_relationship_logs as $inventory_relationship_log){
            $user = $users->find($inventory_relationship_log->causer_id);
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

        return $relationship_logs;
    }

    // Get Ticket Log

    public function getTicketLog($id, $route_name)
    {
        $checkRouteService = new CheckRouteService;
        $access = $checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $logs = ActivityLogTicket::where('subject_id', $id)->get();
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $logs, "status" => 200];
    }
    
    public function getClientTicketLog($id, $route_name)
    {
        $checkRouteService = new CheckRouteService;
        $access = $checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $ticket = Ticket::find($id);
        if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
        $user_company_id = auth()->user()->company_id;
        if($ticket->requester_location !== $user_company_id) return ["success" => false, "message" => "Tidak Memiliki Access untuk Ticket Ini", "status" => 401];
        $logs = ActivityLogTicket::where('subject_id', $id)->get();
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $logs, "status" => 200];
    }

    public function getCloseTicketLog($id, $route_name){
        $checkRouteService = new CheckRouteService;
        $access = $checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $ticket = Ticket::select("id", "type", "subject_id")->find($id);
        if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
        $ticket_logs = ActivityLogTicket::where('subject_id', $id)->get();

        if($ticket->type === 1){
            $incident = Incident::select("id", "inventory_id")->find($ticket->subject_id);
            if($incident === null) return ["success" => true, "message" => "Incident Tidak Ditemukan", "data" => ["ticket_logs" => $ticket_logs, "inventory_logs" => []], "status" => 200];
            else {
                $inventory = Inventory::find($incident->inventory_id);
                if($inventory === null) return ["success" => true, "message" => "Inventory Tidak Ditemukan", "data" => ["ticket_logs" => $ticket_logs, "inventory_logs" => []], "status" => 200];
                else $inventory_logs = $this->inventoryLogs($inventory->id);
            }
            return ["success" => true, "data" => ["ticket_logs" => $ticket_logs, "inventory_logs" => $inventory_logs], "status" => 200];
        }
        
        return ["success" => true, "data" => ["ticket_logs" => $ticket_logs], "status" => 200];
    }

    //

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

    public function createLogInventoryPivotParts($subject_id, $causer_id, $properties)
    {
        $log = new ActivityLogInventoryPivot;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = "List Parts of Inventory";
        $log->log_name = "Created";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function updateLogInventoryPivotParts($subject_id, $causer_id, $properties, $notes)
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

    // Ticket Log

    public function createLogTicketIncident($subject_id, $causer_id, $created_at)
    {
        $log = new ActivityLogTicket;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = (object)[];
        $log->description = "Incident Happened";
        $log->log_name = "Incident";
        $log->created_at = $created_at;
        $log->save();
    }
    
    public function createLogTicket($subject_id, $causer_id, $properties)
    {
        $log = new ActivityLogTicket;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = "Raised Ticket";
        $log->log_name = "Created";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function updateLogTicket($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogTicket;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Updated";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }

    public function assignLogTicket($subject_id, $causer_id, $properties, $notes = null)
    {
        $log = new ActivityLogTicket;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = $properties;
        $log->description = $notes;
        $log->log_name = "Assigned";
        $log->created_at = $this->current_timestamp;
        $log->save();
    }
}