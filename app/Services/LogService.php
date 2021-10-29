<?php

namespace App\Services;
use App\User;
use App\Group;
use App\Ticket;
use App\Vendor;
use App\Company;
use App\Incident;
use App\Inventory;
use App\Relationship;
use App\TicketStatus;
use App\ModelInventory;
use App\ActivityLogTicket;
use App\RelationshipAsset;
use App\ActivityLogInventory;
use App\ModelInventoryColumn;
use App\StatusUsageInventory;
use App\Services\GeneralService;
use App\StatusConditionInventory;
use App\ActivityLogInventoryPivot;
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

        try{
            $inventory_logs = $this->inventoryLogs($id);
            $clustered_logs = $this->clusteredLogs($inventory_logs);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $clustered_logs, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function clusteredLogs($logs)
    {
        $day = 86400;
        $week = 604800;

        $day_logs = [];
        $week_logs = [];
        $else_logs = [];
        $not_else = true;
        $current_timestamp_times = strtotime($this->current_timestamp);
        foreach($logs as $log){
            if($not_else){
                $timestamp_check = $current_timestamp_times - strtotime($log->date);
                if($timestamp_check < $day){
                    array_push($day_logs, $log);
                    continue;
                } else if($timestamp_check < $week){
                    array_push($week_logs, $log);
                    continue;
                } else {
                    $not_else = false;
                    array_push($else_logs, $log);
                    continue;
                }
            } else {
                array_push($else_logs, $log);
            }
        }

        return ['day_logs' => $day_logs, 'week_logs' => $week_logs, 'else_logs' => $else_logs];
    }

    public function inventoryLogs($id)
    {
        try{
            $logs = [];
            if($id === null) return response()->json(["success" => false, "message" => "Silahkan Tambahkan Parameter Id"], 400);
            
            $inventory_pivot_logs = ActivityLogInventoryPivot::where('subject_id', $id)->get();
            foreach($inventory_pivot_logs as $inventory_pivot_log){
                $properties = $inventory_pivot_log->properties;
                $causer_name = $inventory_pivot_log->causer->fullname;
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
                    $causer_name = $inventory_log->causer->fullname;
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
                    $model = ModelInventory::find($properties->attributes->model_id);
                    if($model) $properties->attributes->model_name = $model->name;
                    else $properties->attributes->model_name = "Model Tidak Ditemukan";
                } 
                if($inventory_log->log_name === 'Deleted'){
                    $model = ModelInventory::find($properties->old->model_id);
                    if($model) $properties->old->model_name = $model->name;
                    else $properties->old->model_name = "Model Tidak Ditemukan";
                } 

                if(isset($properties->attributes->status_usage)) $properties->attributes->status_usage_name = StatusUsageInventory::find($properties->attributes->status_usage)->name;
                if(isset($properties->old->status_usage)) $properties->old->status_usage_name = StatusUsageInventory::find($properties->old->status_usage)->name;

                if(isset($properties->attributes->status_condition)) $properties->attributes->status_condition_name = StatusConditionInventory::find($properties->attributes->status_condition)->name;
                if(isset($properties->old->status_condition)) $properties->old->status_condition_name = StatusConditionInventory::find($properties->old->status_condition)->name;

                if(isset($properties->attributes->vendor_id)){
                    $vendor = Vendor::find($properties->attributes->vendor_id);
                    if($vendor) $properties->attributes->vendor_name = $vendor->name;
                    else $properties->attributes->vendor_name = "Vendor Tidak Ditemukan";
                } 
                if(isset($properties->old->vendor_id)){
                    $vendor = Vendor::find($properties->old->vendor_id);
                    if($vendor) $properties->old->vendor_name = $vendor->name;
                    else $properties->old->vendor_name = "Vendor Tidak Ditemukan";
                }

                if(isset($properties->attributes->location)){
                    $location = Company::find($properties->attributes->location);
                    if($location) $properties->attributes->location_name = $location->name;
                    else $properties->attributes->location_name = "Location Tidak Ditemukan";
                } 
                if(isset($properties->old->location)){
                    $location = Company::find($properties->old->location);
                    if($location) $properties->old->location_name = $location->name;
                    else $properties->old->location_name = "Location Tidak Ditemukan";
                }
                $causer_name = $inventory_log->causer->fullname;
                $temp = (object) [
                    'date' => $inventory_log->created_at,
                    'description' => $inventory_log->log_name.' Inventory',
                    'properties' => $properties,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_log->description ? $inventory_log->description : "-"
                ];
                array_push($logs, $temp);
            }   
            
            $inventory_relationship_logs = ActivityLogInventoryRelationship::where('subject_id', $id)->get();
            foreach($inventory_relationship_logs as $inventory_relationship_log){
                $properties = $inventory_relationship_log->properties;
                $causer_name = $inventory_relationship_log->causer->fullname;

                if(isset($properties->attributes->relationship_asset_id)){
                    $relationship_asset = RelationshipAsset::with('relationship')->withTrashed()->select('id','is_inverse','relationship_id')->find($properties->attributes->relationship_asset_id);
                    $is_inverse_inventory_relationship = $relationship_asset->is_inverse === $properties->attributes->is_inverse ? false : true;
                    $properties->attributes->relationship = $is_inverse_inventory_relationship ? $relationship_asset->relationship->inverse_relationship_type : $relationship_asset->relationship->relationship_type;
                }

                if(isset($properties->old->relationship_asset_id)){
                    $relationship_asset = RelationshipAsset::with('relationship')->withTrashed()->select('id','is_inverse','relationship_id')->find($properties->old->relationship_asset_id);
                    $is_inverse_inventory_relationship = $relationship_asset->is_inverse === $properties->old->is_inverse ? false : true;
                    $properties->old->relationship = $is_inverse_inventory_relationship ? $relationship_asset->relationship->inverse_relationship_type : $relationship_asset->relationship->relationship_type;
                }

                $temp = (object) [
                    'date' => $inventory_relationship_log->created_at,
                    'description' => $inventory_relationship_log->log_name.' Inventory Relationship',
                    'properties' => $properties,
                    'causer_name' => $causer_name,
                    'notes' => $inventory_relationship_log->description ? $inventory_relationship_log->description : "-"
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
        $relationship_assets = RelationshipAsset::withTrashed()->select('id','relationship_id','is_inverse')->get();
        $relationship_logs = [];
        $inventory_relationship_logs = ActivityLogInventoryRelationship::where('subject_id', $id)->get();
        foreach($inventory_relationship_logs as $inventory_relationship_log){
            $properties = $inventory_relationship_log->properties;
            $causer_name = $inventory_relationship_log->causer->fullname;

            if(isset($properties->attributes->relationship_asset_id)){
                $relationship_asset = $relationship_assets->find($properties->attributes->relationship_asset_id);
                $is_inverse_inventory_relationship = $relationship_asset->is_inverse === $properties->attributes->is_inverse ? false : true;
                $properties->attributes->relationship = $is_inverse_inventory_relationship ? $relationship_asset->relationship->inverse_relationship_type : $relationship_asset->relationship->relationship_type;
            }

            if(isset($properties->old->relationship_asset_id)){
                $relationship_asset = $relationship_assets->find($properties->old->relationship_asset_id);
                $is_inverse_inventory_relationship = $relationship_asset->is_inverse === $properties->old->is_inverse ? false : true;
                $properties->old->relationship = $is_inverse_inventory_relationship ? $relationship_asset->relationship->inverse_relationship_type : $relationship_asset->relationship->relationship_type;
            }

            $temp = (object) [
                'date' => $inventory_relationship_log->created_at,
                'description' => $inventory_relationship_log->log_name.' Inventory Relationship',
                'properties' => $properties,
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

        $logs = ActivityLogTicket::where('subject_id', $id)->orderBy('created_at','desc')->get();
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $logs, "status" => 200];
    }
    
    public function getClientTicketLog($id, $route_name)
    {
        $checkRouteService = new CheckRouteService;
        $access = $checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $ticket = Ticket::with('requester.company')->find($id);
        if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
        $user_company_id = auth()->user()->company_id;
        if($ticket->requester->company_id !== $user_company_id) return ["success" => false, "message" => "Tidak Memiliki Access untuk Ticket Ini", "status" => 401];
        $logs = ActivityLogTicket::where('subject_id', $id)->orderBy('created_at','desc')->get();
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

    private function addLogTicket($subject_id, $causer_id, $log_name, $created_at = null, $description = null)
    {
        $log = new ActivityLogTicket;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->log_name = $log_name;
        $log->created_at = $created_at;
        $log->description = $description;
        $log->save();
    }

    public function updateIncidentLogTicket($subject_id, $incident_time)
    {
        $log = ActivityLogTicket::where('subject_id', $subject_id)->where('log_name','Waktu Kejadian')->first();
        $log->created_at = $incident_time;
        $log->save();
    }

    public function createLogTicketIncident($subject_id, $causer_id, $created_at)
    {
        $log_name = "Waktu Kejadian";
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at);
    }
    
    public function createLogTicket($subject_id, $causer_id)
    {
        $log_name = "Raised Ticket";
        $created_at = $this->current_timestamp;
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at);
    }

    public function updateLogTicket($subject_id, $causer_id)
    {
        $log_name = "Ticket Telah Diperbarui";
        $created_at = $this->current_timestamp;
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at);
    }

    public function updateStatusLogTicket($subject_id, $causer_id, $type, $notes = null)
    {
        $type_status = TicketStatus::find($type);
        if(!$type_status) $type_detail = "Status Tidak Ditemukan";
        else $type_detail = $type_status->name;
        $log_name = "Status Berubah Menjadi $type_detail";
        $created_at = $this->current_timestamp;
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes);
    }


    public function assignLogTicket($subject_id, $causer_id, $assignable_type, $assignable_id)
    {
        if($assignable_type === 'App\User'){
            $user = User::find($assignable_id);
            if(!$user) $name = "User Tidak Ditemukan";
            else $name = $user->fullname;
        } else {
            $group = Group::find($assignable_id);
            if(!$group) $name = "Group Tidak Ditemukan";
            else $name = $group->name;
        }
        $log_name = "Assigned to $name";
        $created_at = $this->current_timestamp;
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at);
    }

    public function addNoteLogTicket($subject_id, $causer_id, $notes)
    {
        $log_name = "Note Khusus";
        $created_at = $this->current_timestamp;
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes);
    }
}