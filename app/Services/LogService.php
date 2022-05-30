<?php

namespace App\Services;
use App\User;
use App\Group;
use App\Ticket;
use App\Vendor;
use App\Company;
use App\Incident;
use App\Inventory;
use App\Manufacturer;
use App\Relationship;
use App\TicketStatus;
use App\ModelInventory;
use App\ActivityLogTicket;
use App\ActivityLogCompany;
use App\ActivityLogInventory;
use App\ModelInventoryColumn;
use App\StatusUsageInventory;
use App\Services\GlobalService;
use App\ActivityLogPurchaseOrder;
use App\StatusConditionInventory;
use App\ActivityLogInventoryPivot;
use App\ActivityLogInventoryRelationship;

class LogService
{
    public function __construct()
    {

    }

    // Get Inventory Log

    public function getActivityInventoryLogs($id, $route_name)
    {
        $GlobalService = new GlobalService;
        $access = $GlobalService->checkRoute($route_name);
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
        $current_timestamp_times = strtotime(date("Y-m-d H:i:s"));
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
                if(isset($properties->attributes->list_parts)){
                    $attribute_inventories = Inventory::with('modelInventory:id,name')->select('id','mig_id','model_id')->whereIn('id', $properties->attributes->list_parts)->get();
                    $properties->attributes->list_parts = $attribute_inventories;
                } 
                if(isset($properties->old->list_parts)){
                    $attribute_inventories = Inventory::with('modelInventory:id,name')->select('id','mig_id','model_id')->whereIn('id', $properties->old->list_parts)->get();
                    $properties->old->list_parts = $attribute_inventories;
                } 
                if(isset($properties->attributes->parent_id)) $properties->attributes->parent_id = Inventory::with('modelInventory:id,name')->select('id','mig_id','model_id')->find($properties->attributes->parent_id);
                if(isset($properties->old->parent_id)) $properties->old->parent_id = Inventory::with('modelInventory:id,name')->select('id','mig_id','model_id')->find($properties->old->parent_id);
                if(isset($properties->attributes->child_id)) $properties->attributes->child_id = Inventory::with('modelInventory:id,name')->select('id','mig_id','model_id')->find($properties->attributes->child_id);
                if(isset($properties->old->child_id)) $properties->old->child_id = Inventory::with('modelInventory:id,name')->select('id','mig_id','model_id')->find($properties->old->child_id);
                $causer_name = $inventory_pivot_log->causer->name;
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
                    $causer_name = $inventory_log->causer->name;
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

                if(isset($properties->attributes->owned_by)){
                    $owner = Company::find($properties->attributes->owned_by);
                    if($owner) $properties->attributes->owner_name = $owner->name;
                    else $properties->attributes->owner_name = "Company Tidak Ditemukan";
                } 
                if(isset($properties->old->owned_by)){
                    $owner = Company::find($properties->old->owned_by);
                    if($owner) $properties->old->owner_name = $owner->name;
                    else $properties->old->owner_name = "Company Tidak Ditemukan";
                }

                if(isset($properties->attributes->manufacturer_id)){
                    $manufacturer = Manufacturer::find($properties->attributes->manufacturer_id);
                    if($manufacturer) $properties->attributes->manufacturer_name = $manufacturer->name;
                    else $properties->attributes->manufacturer_name = "Manufacturer Tidak Ditemukan";
                } 
                if(isset($properties->old->manufacturer_id)){
                    $manufacturer = Manufacturer::find($properties->old->manufacturer_id);
                    if($manufacturer) $properties->old->manufacturer_name = $manufacturer->name;
                    else $properties->old->manufacturer_name = "Manufacturer Tidak Ditemukan";
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
                $causer_name = $inventory_log->causer->name;
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
                $causer_name = $inventory_relationship_log->causer->name;

                if($inventory_relationship_log->log_name === 'Created Association'){
                    $ticket = Ticket::with('type')->find($properties->ticket_id);
                    $temp = (object) [
                        'date' => $inventory_relationship_log->created_at,
                        'description' => $inventory_relationship_log->log_name.' Inventory',
                        'properties' => 'Terhubung dengan Ticket '.$ticket->type->code .'-'. $ticket->ticketable_id,
                        'causer_name' => $causer_name
                    ];
                    array_push($logs, $temp);
                    continue;
                }

                if($inventory_relationship_log->log_name === 'Deleted Association'){
                    $ticket = Ticket::with('type')->find($properties->ticket_id);
                    $temp = (object) [
                        'date' => $inventory_relationship_log->created_at,
                        'description' => $inventory_relationship_log->log_name.' Inventory',
                        'properties' => 'Terlepas dari Ticket '.$ticket->type->code .'-'. $ticket->ticketable_id,
                        'causer_name' => $causer_name
                    ];
                    array_push($logs, $temp);
                    continue;
                }

                if(isset($properties->attributes->relationship_id)){
                    $relationship = Relationship::withTrashed()->find($properties->attributes->relationship_id);
                    $properties->attributes->relationship = $properties->attributes->is_inverse ? $relationship->inverse_relationship_type : $relationship->relationship_type;
                }

                if(isset($properties->old->relationship_id)){
                    $relationship = Relationship::withTrashed()->find($properties->old->relationship_id);
                    $properties->old->relationship = $properties->old->is_inverse ? $relationship->inverse_relationship_type : $relationship->relationship_type;
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

    // Get Ticket Log
    
    public function getTicketLog($id, $route_name)
    {
        $GlobalService = new GlobalService;
        $access = $GlobalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $special_logs = ActivityLogTicket::where('subject_id', $id)->where('log_name', 'Note Khusus')->orderBy('created_at','desc')->get();
        $logs = ActivityLogTicket::where('subject_id', $id)->where('log_name', '<>', 'Note Khusus')->orderBy('created_at','desc')->get();
        $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $normal_logs = [];
        foreach($logs as $log){
            if($log->description === 'Association Item'){
                $old_exist = false;
                $new_exist = false;
                $properties = json_decode($log->log_name, false);
                if(isset($properties->old->inventory))$old_exist = true;
                if(isset($properties->attributes->inventory))$new_exist = true;
                if($old_exist){
                    if($new_exist) {
                        $inventory = Inventory::with('modelInventory:id,name')->find($properties->attributes->inventory);
                        $log_name = "Pengubahan Association menjadi ";
                    } else {
                        $inventory = Inventory::with('modelInventory:id,name')->find($properties->old->inventory);
                        $log_name = "Pengeluaran Association ";
                    }
                } else {
                    $inventory = Inventory::with('modelInventory:id,name')->find($properties->attributes->inventory);
                    $log_name = "Penambahan Association ";
                } 
                if($inventory) $name = $inventory->modelInventory->name;
                else $name = "Inventory Not Found";
                $log->log_name = $log_name.$name;
            } else if($log->description === 'Perubahan Status'){
                $properties = json_decode($log->log_name, false);
                $new_status = $statuses[$properties->new_status];
                $log->notes = $properties->notes;
                $log->log_name = "Perubahan status menjadi $new_status";
            }
            $normal_logs[] = $log;
        }
        $data = (object)[
            "normal_logs" => $normal_logs,
            "special_logs" => $special_logs
        ];
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }
    
    public function getClientTicketLog($id, $route_name)
    {
        $GlobalService = new GlobalService;
        $access = $GlobalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $ticket = Ticket::with('creator')->find($id);
        if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
        $user_company_id = auth()->user()->company_id;
        if($ticket->creator->company_id !== $user_company_id) return ["success" => false, "message" => "Tidak Memiliki Access untuk Ticket Ini", "status" => 403];
        $special_logs = ActivityLogTicket::where('subject_id', $id)->where('log_name', 'Note Khusus')->orderBy('created_at','desc')->get();
        $logs = ActivityLogTicket::where('subject_id', $id)->where('is_for_client', true)->where('log_name', '<>', 'Note Khusus')->orderBy('created_at','desc')->get();
        $statuses = ['-','Dalam Proses', 'Menunggu Staff', 'Dalam Proses', 'Dalam Proses', 'Completed', 'Selesai', 'Dibatalkan'];
        $normal_logs = [];
        foreach($logs as $log){
            if($log->description === 'Association Item'){
                $old_exist = false;
                $new_exist = false;
                $properties = json_decode($log->log_name, false);
                if(isset($properties->old->inventory))$old_exist = true;
                if(isset($properties->attributes->inventory))$new_exist = true;
                if($old_exist){
                    if($new_exist) {
                        $inventory = Inventory::with('modelInventory:id,name')->find($properties->attributes->inventory);
                        $log_name = "Pengubahan Asosiasi Menjadi ";
                    } else {
                        $inventory = Inventory::with('modelInventory:id,name')->find($properties->old->inventory);
                        $log_name = "Pemisahan Asosiasi ";
                    }
                } else {
                    $inventory = Inventory::with('modelInventory:id,name')->find($properties->attributes->inventory);
                    $log_name = "Penambahan Asosiasi ";
                } 
                if($inventory) $name = $inventory->modelInventory->name;
                else $name = "Inventory Not Found";
                $log->log_name = $log_name.$name;
            } else if($log->description === 'Perubahan Status'){
                $properties = json_decode($log->log_name, false);
                if(in_array($properties->new_status, [1,4,5]) || $properties->old_status === 4) continue; 
                $new_status = $statuses[$properties->new_status];
                $log->log_name = "Perubahan status menjadi $new_status";
                $log->description = $properties->notes;
            }
            $normal_logs[] = $log;
        }

        $data = (object)[
            "normal_logs" => $normal_logs,
            "special_logs" => $special_logs
        ];
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }

    // Get Company Log

    public function getCompanyLog($request)
    {
        $company_id = $request->get('id', null);
        $rows = $request->get('rows', 10);
        if($rows < 1) $rows = 10;
        if($rows > 100) $rows = 10;
        if($company_id === null) return ["success" => false, "message" => "ID Company Kosong", "status" => 400];
        $company_logs = ActivityLogCompany::where('company_id', $company_id)->orderBy('created_at', 'desc')->paginate($rows);
        foreach($company_logs as $company_log){
            if(str_contains($company_log->log_name, 'Sub')){
                $company = Company::with('parent')->find($company_log->subjectable_id);
                $company_log->subjectable->parent_name = $company->parent->name;
            } 
        }
        return ["success" => true, "data" => $company_logs, "status" => 200];
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
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
        $log->created_at = date("Y-m-d H:i:s");
        $log->save();
    }

    private function createLogInventoryAssociation($subject_id, $causer_id, $ticket_id)
    {
        $log = new ActivityLogInventoryRelationship;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = ['ticket_id' => $ticket_id];
        $log->log_name = "Created Association";
        $log->created_at = date("Y-m-d H:i:s");
        $log->save();
    }


    private function deleteLogInventoryAssociation($subject_id, $causer_id, $ticket_id)
    {
        $log = new ActivityLogInventoryRelationship;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->properties = ['ticket_id' => $ticket_id];
        $log->log_name = "Deleted Association";
        $log->created_at = date("Y-m-d H:i:s");
        $log->save();
    }

    public function associationLogInventory($subject_id, $causer_id, $old_inventory_id, $inventory_id)
    {
        if($old_inventory_id !== null) $this->deleteLogInventoryAssociation($old_inventory_id, $causer_id, $subject_id);
        if($inventory_id !== null) $this->createLogInventoryAssociation($inventory_id, $causer_id, $subject_id);
    }

    public function removeAssociationLogInventory($subject_id, $causer_id, $old_inventory_id)
    {
        $this->deleteLogInventoryAssociation($old_inventory_id, $causer_id, $subject_id);
    }

    // Ticket Log

    private function addLogTicket($subject_id, $causer_id, $log_name, $created_at = null, $description = null, $is_for_client = false)
    {
        $log = new ActivityLogTicket;
        $log->subject_id = $subject_id;
        $log->causer_id = $causer_id;
        $log->log_name = $log_name;
        $log->created_at = $created_at;
        $log->description = $description;
        $log->is_for_client = $is_for_client;
        $log->save();

        return $log;
    }

    public function updateIncidentLogTicket($subject_id, $incident_time)
    {
        $log = ActivityLogTicket::where('subject_id', $subject_id)->where('log_name','Waktu Kejadian')->first();
        $log->created_at = $incident_time;
        $log->save();
    }
    
    public function updateRaisedAtLogTicket($subject_id, $created_at)
    {
        $log = ActivityLogTicket::where('subject_id', $subject_id)->where('log_name','Raised Ticket')->first();
        $log->created_at = $created_at;
        $log->save();
    }

    public function createLogTicketIncident($subject_id, $causer_id, $created_at)
    {
        $log_name = "Waktu Kejadian";
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, null, true);
    }
    
    public function createLogTicket($subject_id, $causer_id)
    {
        $log_name = "Raised Ticket";
        $created_at = date("Y-m-d H:i:s");
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, null, true);
    }

    public function updateLogTicket($subject_id, $causer_id)
    {
        $log_name = "Ticket Telah Diperbarui";
        $created_at = date("Y-m-d H:i:s");
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at);
    }

    public function updateStatusLogTicket($subject_id, $causer_id, $old_status, $new_status, $notes = null)
    {
        // $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        // if($type < 1 || $type > 7) $type_detail = "Status Tidak Ditemukan";
        // else $type_detail = $statuses[$type];
        // $log_name = "Status Berubah Menjadi $type_detail";
        // $created_at = date("Y-m-d H:i:s");
        // $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes);
        $log_notes = $notes ?? "Perubahan Status";
        $properties = ['old_status' => $old_status, 'new_status' => $new_status, "notes" => $log_notes];
        $created_at = date("Y-m-d H:i:s");
        $log_name = json_encode($properties);
        $notes = "Perubahan Status";
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes, true);
    }


    public function assignLogTicket($subject_id, $causer_id, $assignable_type, $assignable_id)
    {
        if($assignable_type === 'App\User'){
            $user = User::find($assignable_id);
            if(!$user) $name = "User Tidak Ditemukan";
            else $name = $user->name;
        } else {
            $group = Group::find($assignable_id);
            if(!$group) $name = "Group Tidak Ditemukan";
            else $name = $group->name;
        }
        $log_name = "Assigned to $name";
        $created_at = date("Y-m-d H:i:s");
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, null, true);
    }

    public function setDeadlineLogTicket($subject_id, $causer_id)
    {
        $log_name = "Set Deadline";
        $created_at = date("Y-m-d H:i:s");
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at);
    }

    public function addNoteLogTicket($subject_id, $causer_id, $notes)
    {
        $log_name = "Note Khusus";
        $created_at = date("Y-m-d H:i:s");
        $log = $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes, true);
        return $log;
    }

    public function updateNoteLogTicket($id, $causer_id, $notes, $log_id, $admin){
        try{
            $log = ActivityLogTicket::find($log_id);
            if($log === null) return ["success" => false, "message" => "Log Tidak Ditemukan", "status" => 400];
            if($log->log_name !== "Note Khusus") return ["success" => false, "message" => "Log Tidak Ditemukan", "status" => 400];
            if($log->subject_id !== $id && !$admin) return ["success" => false, "message" => "Log Bukan Milik Tiket Terhubung", "status" => 403];
            if($log->causer_id !== $causer_id && !$admin) return ["success" => false, "message" => "Log Tidak Dibuat Oleh User Login!", "status" => 403];
            $log->created_at = date("Y-m-d H:i:s");
            $log->description = $notes;
            $log->save();
            
            return ["success" => true, "message" => "Catatan berhasil diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteNoteLogTicket($id, $causer_id, $log_id, $admin){
        try{
            $log = ActivityLogTicket::find($log_id);
            if($log === null) return ["success" => false, "message" => "Log Tidak Ditemukan", "status" => 400];
            if($log->log_name !== "Note Khusus") return ["success" => false, "message" => "Log Tidak Ditemukan", "status" => 400];
            if($log->subject_id !== $id && !$admin) return ["success" => false, "message" => "Log Bukan Milik Tiket Terhubung", "status" => 403];
            if($log->causer_id !== $causer_id && !$admin) return ["success" => false, "message" => "Log Tidak Dibuat Oleh User Login!", "status" => 403];
            $log->delete();
            
            return ["success" => true, "message" => "Catatan berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function setItemLogTicket($subject_id, $causer_id, $old_inventory_id, $inventory_id)
    {
        if($old_inventory_id !== null) $properties['old'] = ['inventory' => $old_inventory_id];
        $properties['attributes'] = ['inventory' => $inventory_id];
        $created_at = date("Y-m-d H:i:s");
        $log_name = json_encode($properties);
        $notes = "Association Item";
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes);
    }

    public function removeItemLogTicket($subject_id, $causer_id, $old_inventory_id)
    {
        $properties['old'] = ['inventory' => $old_inventory_id];
        $created_at = date("Y-m-d H:i:s");
        $log_name = json_encode($properties);
        $notes = "Association Item";
        $this->addLogTicket($subject_id, $causer_id, $log_name, $created_at, $notes);
    }

    private function addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id)
    {
        $log = new ActivityLogCompany;
        $log->log_name = $log_name;
        $log->company_id = $company_id;
        $log->subjectable_type = $subjectable_type;
        $log->subjectable_id = $subjectable_id;
        $log->causer_id = auth()->user()->id;
        $log->created_at = date("Y-m-d H:i:s");
        $log->save();
    }

    public function createCompany($company_id, $subjectable_id, $is_sub = false)
    {
        if($is_sub) $log_name = 'Created Sub';
        else $log_name = 'Created';
        $subjectable_type = 'App\Company';
        $this->addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id);
    }

    public function updateCompany($company_id, $subjectable_id, $is_sub = false)
    {
        if($is_sub) $log_name = 'Updated Sub';
        else $log_name = 'Updated';
        $subjectable_type = 'App\Company';
        $this->addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id);
    }

    public function deleteCompany($company_id, $subjectable_id, $is_sub = false)
    {
        if($is_sub) $log_name = 'Deleted Sub';
        else  $log_name = 'Deleted';
        $subjectable_type = 'App\Company';
        $this->addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id);
    }

    public function createBank($company_id, $subjectable_id)
    {
        $log_name = 'Created';
        $subjectable_type = 'App\Bank';
        $this->addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id);
    }

    public function updateBank($company_id, $subjectable_id)
    {
        $log_name = 'Updated';
        $subjectable_type = 'App\Bank';
        $this->addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id);
    }

    public function deleteBank($company_id, $subjectable_id)
    {
        $log_name = 'Deleted';
        $subjectable_type = 'App\Bank';
        $this->addLogCompany($log_name, $company_id, $subjectable_type, $subjectable_id);
    }

    private function addLogPurchaseOrder($purchase_order_id, $log_name, $description, $connectable_type, $connectable_id)
    {
        $log = new ActivityLogPurchaseOrder;
        $log->purchase_order_id = $purchase_order_id;
        $log->log_name = $log_name;
        $log->description = $description;
        $log->connectable_type = $connectable_type;
        $log->connectable_id = $connectable_id;
        $log->causer_id = auth()->user()->id;
        $log->created_at = date("Y-m-d H:i:s");
        $log->save();
    }

    public function createPurchaseOrder($purchase_order_id)
    {
        $log_name = 'Created';
        $description = 'Detail Pembelian dibuat oleh ';
        $connectable_type = 'App\User';
        $connectable_id = auth()->user()->id;
        $this->addLogPurchaseOrder($purchase_order_id, $log_name, $description, $connectable_type, $connectable_id);
    }

    public function acceptPurchaseOrder($purchase_order_id)
    {
        $log_name = 'Accepted';
        $description = 'Pembelian disetujui oleh ';
        $connectable_type = 'App\User';
        $connectable_id = auth()->user()->id;
        $this->addLogPurchaseOrder($purchase_order_id, $log_name, $description, $connectable_type, $connectable_id);
    }

    public function rejectPurchaseOrder($purchase_order_id)
    {
        $log_name = 'Rejected';
        $description = 'Pembelian ditolak oleh ';
        $connectable_type = 'App\User';
        $connectable_id = auth()->user()->id;
        $this->addLogPurchaseOrder($purchase_order_id, $log_name, $description, $connectable_type, $connectable_id);
    }

    public function sendPurchaseOrder($purchase_order_id, $vendor_id)
    {
        $log_name = 'Sent';
        $description = 'Pengiriman barang oleh ';
        $connectable_type = 'App\Vendor';
        $this->addLogPurchaseOrder($purchase_order_id, $log_name, $description, $connectable_type, $vendor_id);
    }

    public function receivePurchaseOrder($purchase_order_id)
    {
        $log_name = 'Received';
        $description = 'Barang diterima oleh ';
        $connectable_type = 'App\User';
        $connectable_id = auth()->user()->id;
        $this->addLogPurchaseOrder($purchase_order_id, $log_name, $description, $connectable_type, $connectable_id);
    }
}