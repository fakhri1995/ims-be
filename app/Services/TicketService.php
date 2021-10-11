<?php

namespace App\Services;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Inventory;
use App\InventoryValue;
use App\ModelInventoryColumn;
use App\ModelInventory;
use App\Incident;
use App\Ticket;
use App\TicketActivityLog;
use App\User;
use App\Services\CheckRouteService;
use App\Services\CompanyService;
use App\Services\GeneralService;
use App\Services\LogService;

class TicketService
{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }

    // Status Ticket
    // 1 = Open, 2 = On Progress, 3 = Pending, 4 = Resolved, 5 = Canceled, 6 = Closed

    public function checkUpdateProperties($old_ticket, $new_ticket)
    {
        $properties = false;
        foreach($new_ticket->getAttributes() as $key => $value){
            if($new_ticket->$key !== $old_ticket[$key]){
                $properties['attributes'][$key] = $new_ticket->$key;
                $properties['old'][$key] = $old_ticket[$key];
            }
        }
        return $properties;
    }

    public function getTicketRelation($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $companyService = new CompanyService;
        $companies = $companyService->getCompanyListWithTop();
        $users = User::select('user_id AS id', 'fullname AS name')->get();

        $status_ticket = [
            ["id" => 1, "name" => "Open"],
            ["id" => 2, "name" => "On Progress"],
            ["id" => 3, "name" => "Pending"],
            ["id" => 4, "name" => "Resolved"],
            ["id" => 5, "name" => "Canceled"],
            ["id" => 6, "name" => "Close"],
        ];

        $incident_type = [
            ["id" => 1, "name" => "Incident"]
        ];

        $data = ["status_ticket" => $status_ticket, "incident_type" => $incident_type, "requesters" => $users, "companies" => $companies];
        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getClientTicketRelation($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $company = auth()->user()->company;
        $company_id = $company->company_id;
        $company_name = $company->company_name;
        $user_companies = [['id' => $company_id, 'name' => $company_name]];

        $users = User::select('user_id', 'fullname', 'company_id')->where('company_id', $company_id)->get();
        
        $companyService = new CompanyService;
        $companies = $companyService->getCompanyListWithTop();

        $status_ticket = [
            ["id" => 1, "name" => "Open"],
            ["id" => 2, "name" => "On Progress"],
            ["id" => 3, "name" => "Pending"],
            ["id" => 4, "name" => "Resolved"],
            ["id" => 5, "name" => "Canceled"],
            ["id" => 6, "name" => "Closed"],
        ];

        $incident_type = [
            ["id" => 1, "name" => "Incident"]
        ];

        $data = ["status_ticket" => $status_ticket, "incident_type" => $incident_type, "requesters" => $users, "requester_companies" => $user_companies, "companies" => $companies];
        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getTickets($admin)
    {
        try{
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                $tickets = Ticket::where('requester_location', $company_user_login_id)->where('status', '<>', 6)->orderBy('created_at', 'desc')->get();
            } else {
                $tickets = Ticket::where('status', '<>', 6)->orderBy('created_at', 'desc')->get();
            }
            $open_tickets_count = $tickets->where('status', 1)->count();
            $on_progress_tickets_count = $tickets->where('status', 2)->count();
            $pending_tickets_count = $tickets->where('status', 3)->count();
            $resolved_tickets_count = $tickets->where('status', 4)->count();
            $total_tickets = $open_tickets_count + $on_progress_tickets_count + $pending_tickets_count + $resolved_tickets_count;
            $data = ["total_tickets" => $total_tickets, "open_tickets_count" => $open_tickets_count, "on_progress_tickets_count" => $on_progress_tickets_count, "pending_tickets_count" => $pending_tickets_count, "resolved_tickets_count" => $resolved_tickets_count, "tickets" => $tickets];
            
            if(!count($tickets)) return ["success" => false, "message" => "Ticket masih kosong", "data" => $data, "status" => 200];
            return ["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getAdminTickets($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTickets(true);
    }

    public function getClientTickets($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTickets(false);
    }

    public function getClosedTickets($admin)
    {   
        try{
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                $tickets = Ticket::where('requester_location', $company_user_login_id)->where('status', 6)->get();
            } else {
                $tickets = Ticket::where('status', 6)->get();
            }
            $data = ["tickets" => $tickets];
            
            if(!count($tickets)) return ["success" => false, "message" => "Closed Ticket Kosong", "data" => $data, "status" => 200];
            return ["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAdminClosedTickets($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getClosedTickets(true);
    }

    public function getClientClosedTickets($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getClosedTickets(false);
    }


    public function getTicket($id, $admin)
    {
        try{
            $ticket = Ticket::find($id);
            if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                if($ticket->requester_location !== $company_user_login_id) return ["success" => false, "message" => "Ticket Bukan Milik User Login", "status" => 401];
            }
            $company_service = new CompanyService;
            $companies = $company_service->getCompanyList();
            $company_requester_location = $companies->find($ticket->requester_location);
            if($company_requester_location === null) $ticket->requester_location_name = "Not Found";
            else $ticket->requester_location_name = $company_requester_location->company_name;
            
            if($ticket->type === 1){
                $incident = Incident::find($ticket->subject_id);
                if($incident === null) return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => false, "data" => "Data Tidak Ditemukan"]], "status" => 200];
                else {
                    $company_incident_place = $companies->find($ticket->incident_place_id);
                    if($company_incident_place === null) $ticket->incident_place_name = "Not Found";
                    else $ticket->incident_place_name = $company_incident_place->company_name;
                    
                    if($incident->inventory_id){
                        $inventory = Inventory::find($incident->inventory_id);
                        if($inventory === null){
                            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => false, "message" => "Data Inventory Tidak Ditemukan", "data" => ["incident" => $incident, "inventory" => null]]], "status" => 200];
                        } else {
                            $inventory_columns = ModelInventoryColumn::get();
                            $inventory_values = InventoryValue::where('inventory_id', $inventory->id)->get();
                            $additional_attributes = [];
                            foreach($inventory_values as $inventory_value){
                                $inventory_value_column = $inventory_columns->where('id', $inventory_value->model_inventory_column_id)->first();
                                $inventory_value->name = $inventory_value_column === null ? "Nama Kolom Tidak Ditemukan" : $inventory_value_column->name;
                                $additional_attributes[] = $inventory_value;
                            }
                            $inventory->additional_attributes = $additional_attributes;
                            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => true, "data" => ["incident" => $incident, "inventory" => $inventory]]], "status" => 200];
                        }
                    } else return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => false, "message" => "Id Inventory Kosong", "data" => ["incident" => $incident, "inventory" => null]]], "status" => 200];
                }
                
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAdminTicket($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTicket($id, true);
    }

    public function getClientTicket($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTicket($id, false);
    }

    public function addTicket($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $type = $data['type'];
            $requester = $data['requester'];
            if($type === null) return ["success" => false, "message" => "Field Tipe Ticket Belum Terisi", "status" => 400];
            if($requester === null) return ["success" => false, "message" => "Requester Belum Terisi", "status" => 400];
            $last_sub_ticket = Ticket::where('type', $type)->orderBy('sub_id', 'desc')->first();
            
            if($last_sub_ticket === null) $sub_id = 1;
            else $sub_id = $last_sub_ticket->sub_id + 1;
            $subject_id = 0;
            if($type === 1){
                $validator = Validator::make($data, [
                    "incident_place_id" => "required|integer",
                    "asset_id" => "required|integer",
                    "incident_time" => "required|date_format:Y-m-d H:i:s",
                    "description" => "required|string"
                    // "files" => "file|nullable"
                ]);
                
                if ($validator->fails()) {
                    return ["success" => false, "message" => (object)[
                        "errorInfo" => $validator->errors()
                    ], "status" => 400];
                }
                
                $files = $data['files'];
                $names = [];
                if(!empty($files)){
                    foreach($files as $file){
                        $file_name = $file->getClientOriginalName();
                        $filename = pathinfo($file_name, PATHINFO_FILENAME);
                        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                        $name = $filename.'_'.time().'.'.$extension;
                        Storage::disk('local')->putFileAs('incidents', $file, $name);
                        array_push($names, $name);
                    }
                } else {
                    $names = 'no_file.jpg';
                }
                
                $incident = new Incident;
                $incident->incident_place_id = $data['incident_place_id'];
                $incident->asset_id = $data['asset_id'];
                $incident->incident_time = $data['incident_time'];
                $incident->description = $data['description'];
                $incident->files = json_encode($names);
                $incident->save();
                
                $subject_id = $incident->id;
            }
            
            $generalService = new GeneralService;
            $current_timestamp = $generalService->getTimeNow();
            
            $user = User::find($requester);
            if($user === null) $name = "Not Found";
            else $name = $user->fullname;

            $ticket = new Ticket;
            $ticket->sub_id = $sub_id;
            $ticket->subject_id = $subject_id;
            $ticket->type = $type;
            $ticket->status = 1;
            $ticket->created_at = $current_timestamp;
            $ticket->due_to = $data['due_to'];
            $ticket->requester_location = $data['requester_location'];
            $ticket->requester = $requester;
            $ticket->save();

            $causer_id = auth()->user()->user_id;
            $logService = new LogService;
            $logService->createLogTicketIncident($ticket->id, $causer_id, $incident->incident_time);
            
            
            $properties['attributes'] = $ticket;
            $logService->createLogTicket($ticket->id, $causer_id, $properties);

            return ["success" => true, "message" => "Ticket Berhasil Diterbitkan", "id" => $ticket->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function setItemTicket($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $data['id'];
            $inventory_id = $data['inventory_id'];
            $ticket = Ticket::find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($ticket->status === 6) return ["success" => false, "message" => "Status Ticket Sudah Closed", "status" => 400];
            if($inventory_id === null) return ["success" => false, "message" => "Id Inventory Kosong", "status" => 400];
            if($ticket->type !== 1) return ["success" => false, "message" => "Tipe Tiket Tidak Sesuai", "status" => 400];
            $incident = Incident::find($ticket->subject_id);
            if($incident === null) return ["success" => false, "message" => "Incident pada Ticket Tidak Ditemukan", "status" => 400];
            $incident->inventory_id = $inventory_id;
            $incident->save();
            return ["success" => true, "message" => "Inventory Berhasil Ditambahkan pada Ticket", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function changeStatusTicket($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $data['id'];
            $notes = $data['notes'];
            $status = $data['status'];
            $ticket = Ticket::find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($ticket->status === 6) return ["success" => false, "message" => "Status Ticket Sudah Closed", "status" => 400];
            if($status < 1 || $status > 6) return ["success" => false, "message" => "Status Tidak Tepat", "status" => 400];
            if(strlen($notes) > 1000) return ["success" => false, "message" => "Notes Melebihi 1000 Karakter", "status" => 400];
            if($ticket->status === 5 && $status !== 6) return ["success" => false, "message" => "Status Canceled Tidak Dapat Diubah Selain Menjadi Closed", "status" => 400];
            if($status === 5 && $notes === null) return ["success" => false, "message" => "Untuk Status Canceled Diperlukan Keterangan (Notes)", "status" => 400];
            $generalService = new GeneralService;
            $current_timestamp = $generalService->getTimeNow();
            $old_ticket = [];
            foreach($ticket->getAttributes() as $key => $value) $old_ticket[$key] = $value;
            $ticket->status = $status;
            $ticket->save();
            $properties = $this->checkUpdateProperties($old_ticket, $ticket);
            $causer_id = auth()->user()->user_id;
            $logService = new LogService;
            if($properties){
                $logService->updateLogTicket($ticket->id, $causer_id, $properties, $notes);
            }
            
            if($ticket->status === 6){
                if($ticket->type === 1){
                    $incident = Incident::find($ticket->subject_id);
                    $properties = [];
                    if($incident === null) $properties = ["false_message" => "Incident Id Not Found"];
                    else {
                        $inventory = Inventory::find($incident->inventory_id);
                        if($inventory === null) $properties = ["false_message" => "Inventory Id Not Found"];
                        else {
                            $inventory_columns = ModelInventoryColumn::get();
                            $inventory_values = InventoryValue::where('inventory_id', $inventory->id)->get();
                            $additional_attributes = [];
                            if(count($inventory_values)){
                                foreach($inventory_values as $inventory_value){
                                    $inventory_value_column = $inventory_columns->where('id', $inventory_value->model_inventory_column_id)->first();
                                    $inventory_value->name = $inventory_value_column === null ? "not_found_column" : $inventory_value_column->name;
                                    $additional_attributes[] = $inventory_value;
                                }
                            }
                            foreach($inventory->getAttributes() as $key => $value){
                                $properties['attributes']['inventory'][$key] = $value;
                            }
                            if(count($additional_attributes)){
                                foreach($additional_attributes as $additional_attribute){
                                    $properties['attributes']['inventory'][$additional_attribute->name] = $additional_attribute->value;
                                }
                            }
                        }
                    }
                    $notes = "Closed Condition Inventory";
                    $logService->updateLogTicket($ticket->id, $causer_id, $properties, $notes);
                }
            }

            return ["success" => true, "message" => "Berhasil Merubah Status Ticket", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function cancelClientTicket($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $data['id'];
            $notes = $data['notes'];
            // return ["success" => true, "message" => $data, "status" => 400];
            if($notes === null) return ["success" => false, "message" => "Untuk Status Canceled Diperlukan Keterangan (Notes)", "status" => 400];
            $ticket = Ticket::find($id);
            $company_user_login_id = auth()->user()->company_id;
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($ticket->requester_location !== $company_user_login_id) return ["success" => false, "message" => "Tidak Memiliki Akses Tiket Ini", "status" => 401];
            if($ticket->status === 5) return ["success" => false, "message" => "Ticket Sudah Dalam Status Canceled", "status" => 400];
            if($ticket->status === 6) return ["success" => false, "message" => "Ticket Dalam Status Closed", "status" => 400];
            $old_ticket = [];
            foreach($ticket->getAttributes() as $key => $value) $old_ticket[$key] = $value;
            $ticket->status = 5;
            $ticket->save();
            
            $properties = $this->checkUpdateProperties($old_ticket, $ticket);
            if($properties){
                $causer_id = auth()->user()->user_id;
                $logService = new LogService;
                $logService->updateLogTicket($ticket->id, $causer_id, $properties, $notes);
            }
            
            return ["success" => true, "message" => "Berhasil Membatalkan Ticket", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function assignTicket($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $data['id'];
            $asign_to_id = $data['asign_to_id'];
            $ticket = Ticket::find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($asign_to_id === null) return ["success" => false, "message" => "Assign to (Petugas) Kosong", "status" => 400];
            
            
            $user = User::find($asign_to_id);
            if($user === null) $name = "Not Found";
            else $name = $user->fullname;

            $old_ticket = [];
            foreach($ticket->getAttributes() as $key => $value) $old_ticket[$key] = $value;

            $ticket->asign_to = $asign_to_id;
            $ticket->save();

            $properties = $this->checkUpdateProperties($old_ticket, $ticket);
            if($properties){
                $causer_id = auth()->user()->user_id;
                $logService = new LogService;
                $logService->assignLogTicket($ticket->id, $causer_id, $properties);
            }
            
            return ["success" => true, "message" => "Ticket Berhasil Ditugaskan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
}