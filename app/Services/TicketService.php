<?php

namespace App\Services;
use App\User;
use App\Group;
use Exception;
use App\Ticket;
use App\Incident;
use App\Inventory;
use App\TicketType;
use App\TicketStatus;
use App\TicketActivityLog;
use App\IncidentProductType;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\CompanyService;
use App\Services\GeneralService;
use App\Services\CheckRouteService;
use App\Exports\TicketsExport;
use Excel;
use PDF;

class TicketService
{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }

    // Status Ticket
    // 1 = Open, 2 = On Progress, 3 = On Hold, 4 = Canceled, 5 = Closed

    public function getTicketRelation($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $companyService = new CompanyService;
        $companies = $companyService->getCompanyTreeSelect(1, true);
        $users = User::select('id', 'name','company_id')->with('company:id,name')->get();

        $status_ticket = TicketStatus::all();

        $ticket_types = TicketType::all();
        $product_types = IncidentProductType::all();

        $data = ["status_ticket" => $status_ticket, "ticket_types" => $ticket_types, "requesters" => $users, "companies" => $companies, "product_types" => $product_types];
        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getClientTicketRelation($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $users = User::select('id', 'name', 'company_id')->where('company_id', $company_id)->get();
        
        $companyService = new CompanyService;
        $companies = $companyService->getLocations();

        $status_ticket = TicketStatus::all()->whereNotIn('id', [2,3]);

        $ticket_types = TicketType::all();
        $product_types = IncidentProductType::all();

        $data = ["status_ticket" => $status_ticket, "ticket_types" => $ticket_types, "requesters" => $users, "companies" => $companies, "product_types" => $product_types];
        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getTickets(Request $request, $admin)
    {
        try{
            $ticket_id = $request->get('ticket_id', null);
            $location_id = $request->get('location_id', null);
            $status_id = $request->get('status_id', null);
            $from = $request->get('from', null);
            $to = $request->get('to', null);
            
            $rows = $request->get('rows', 10);
            if($rows < 0) $rows = 10;
            if($rows > 100) $rows = 100;
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                $tickets = Ticket::select('id', 'ticketable_id', 'requester_id', 'raised_at', 'ticketable_type', 'status_id', 'assignable_id', 'assignable_type')->with(['type','status', 'ticketable:id,location_id', 'requester','assignable'])
                ->whereHas('requester', function($q) use ($company_user_login_id){
                    $q->where('users.company_id', $company_user_login_id);
                });
                
                if($ticket_id){
                    $tickets = $tickets->where('ticketable_id', $ticket_id);
                }
                if($status_id){
                    if($status_id == 1) $tickets = $tickets->whereNotIn('status_id', [4,5]);
                    else $tickets = $tickets->where('status_id', $status_id);
                }
                if($from && $to){
                    $tickets = $tickets->whereBetween('raised_at', [$from, $to]);
                }
                if($location_id){
                    $tickets = $tickets->whereHasMorph(
                        'ticketable',
                        ['App\Incident'],
                        function ($query) use ($location_id){
                            $query->where('location_id', '=', $location_id);
                        }
                    );
                }

                $tickets = $tickets->orderBy('status_id', 'asc')->orderBy('raised_at', 'desc')->paginate($rows);
                $statuses = TicketStatus::withCount('clientTickets')->pluck('client_tickets_count');
                $open_tickets_count = $statuses[0] + $statuses[1] + $statuses[2];
                $canceled_tickets_count = $statuses[3];
                $closed_tickets_count = $statuses[4];
                $total_tickets = $open_tickets_count + $canceled_tickets_count + $closed_tickets_count;
                
                foreach($tickets as $ticket)
                {
                    if($ticket->status->id < 4) $ticket->status->name = "Open";
                    if($ticket->ticketable->id !== 0 || $ticket->ticketable->location->id !== 0){
                        $ticket->ticketable->location->full_name = $ticket->ticketable->location->topParent ? $ticket->ticketable->location->topParent->name.' - '.$ticket->ticketable->location->name : $ticket->ticketable->location->name;
                    }
                    $ticket->ticketable->location->makeHidden('topParent');
                }
                
                $data = ["total_tickets" => $total_tickets, "open_tickets_count" => $open_tickets_count, "canceled_tickets_count" => $canceled_tickets_count, "closed_tickets_count" => $closed_tickets_count, "tickets" => $tickets];
            } else {
                $tickets = Ticket::select('id', 'ticketable_id', 'requester_id', 'raised_at', 'ticketable_type', 'status_id','assignable_id', 'assignable_type' )->with(['type','status', 'ticketable:id,location_id', 'requester','assignable']);
                if($ticket_id){
                    $tickets = $tickets->where('ticketable_id', $ticket_id);
                }
                if($status_id){
                    $tickets = $tickets->where('status_id', $status_id);
                }
                if($from && $to){
                    $tickets = $tickets->whereBetween('raised_at', [$from, $to]);
                }
                if($location_id){
                    $tickets = $tickets->whereHasMorph(
                        'ticketable',
                        ['App\Incident'],
                        function ($query) use ($location_id){
                            $query->where('location_id', '=', $location_id);
                        }
                    );
                }
                $tickets = $tickets->orderBy('status_id', 'asc')->orderBy('raised_at', 'desc')->paginate($rows);
                
                foreach($tickets as $ticket)
                {
                    if($ticket->ticketable->id !== 0 || $ticket->ticketable->location->id !== 0){
                        $ticket->ticketable->location->full_name = $ticket->ticketable->location->topParent ? $ticket->ticketable->location->topParent->name.' - '.$ticket->ticketable->location->name : $ticket->ticketable->location->name;
                    }
                    $ticket->ticketable->location->makeHidden('topParent');
                }

                $statuses = TicketStatus::withCount('tickets')->pluck('tickets_count');
                $open_tickets_count = $statuses[0];
                $on_progress_tickets_count = $statuses[1];
                $on_hold_tickets_count = $statuses[2];
                $canceled_tickets_count = $statuses[3];
                $closed_tickets_count = $statuses[4];
                
                $total_tickets = $open_tickets_count + $on_progress_tickets_count + $on_hold_tickets_count + $canceled_tickets_count + $closed_tickets_count;
                $data = ["total_tickets" => $total_tickets, "open_tickets_count" => $open_tickets_count, "on_progress_tickets_count" => $on_progress_tickets_count, "on_hold_tickets_count" => $on_hold_tickets_count, "canceled_tickets_count" => $canceled_tickets_count, "closed_tickets_count" => $closed_tickets_count, "tickets" => $tickets];
            }
            
            
            return ["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getAdminTickets(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTickets($request, true);
    }

    public function getClientTickets(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTickets($request, false);
    }

    public function getClosedTickets(Request $request, $admin)
    {   
        try{
            $ticket_id = $request->get('ticket_id', null);
            $rows = $request->get('rows', 10);
            if($rows < 0) $rows = 10;
            if($rows > 100) $rows = 100;

            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                $tickets = Ticket::select('id', 'ticketable_id', 'requester_id', 'raised_at', 'ticketable_type','assignable_id', 'assignable_type')->whereHas('requester', function($q) use ($company_user_login_id){
                    $q->where('users.company_id', $company_user_login_id);
                })->with(['type', 'ticketable', 'requester','assignable'])->where('status_id', 5);
            } else {
                $tickets = Ticket::select('id', 'ticketable_id', 'requester_id', 'raised_at', 'ticketable_type','assignable_id', 'assignable_type')->with(['type', 'ticketable', 'requester','assignable'])->where('status_id', 5);
            }
            
            if($ticket_id){
                $tickets = $tickets->where('ticketable_id', $ticket_id);
            }
            $tickets = $tickets->paginate($rows);
            // return ["success" => false, "message" => "MASUK", "status" => 400];
            $data = ["tickets" => $tickets];
            
            if(!count($tickets)) return ["success" => false, "message" => "Closed Ticket Kosong", "data" => $data, "status" => 200];
            return ["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAdminClosedTickets(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getClosedTickets($request, true);
    }

    public function getClientClosedTickets(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getClosedTickets($request, false);
    }


    public function getTicket(Request $request, $admin)
    {
        try{
            $id = $request->get('id');
            $ticket = Ticket::with(['type','status', 'requester', 'ticketable.location','assignable'])->find($id);
            if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
            if($ticket->ticketable_type === 'App\Incident') $ticket->ticketable->inventory;
            $ticket->original_raised_at = $ticket->getRawOriginal('raised_at');
            if(!$ticket->closed_at) $ticket->resolved_time = "-";
            else $ticket->resolved_time = Carbon::parse($ticket->original_raised_at)->diffForHumans($ticket->closed_at, true);
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                if($ticket->requester->company_id !== $company_user_login_id) return ["success" => false, "message" => "Ticket Bukan Milik Perusahaan User Login", "status" => 401];
            }
            if($ticket->ticketable->id !== 0 || $ticket->ticketable->location->id !== 0){
                $ticket->ticketable->location->full_name = $ticket->ticketable->location->topParent ? $ticket->ticketable->location->topParent->name.' - '.$ticket->ticketable->location->name : $ticket->ticketable->location->name;
            }
            $ticket->ticketable->location->makeHidden('topParent');
            if($ticket->requester->id !== 0 && $ticket->requester->company->id !== 0){
                $ticket->requester->company->full_name = $ticket->requester->company->topParent ? $ticket->requester->company->topParent->name.' - '.$ticket->requester->company->name : $ticket->requester->company->name;
                $ticket->requester->company->makeHidden('topParent');
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAdminTicket(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTicket($request, true);
    }

    public function getClientTicket(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        return $this->getTicket($request, false);
    }

    public function addTicket($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $type_id = $data['type_id'];
            if($type_id === null) return ["success" => false, "message" => "Field Tipe Ticket Belum Terisi", "status" => 400];
            
            $ticketable_id = 0;
            $ticketable_type = '-';
            if($type_id === 1){
                // $files = $data['files'];
                // $names = [];
                // if(!empty($files)){
                //     foreach($files as $file){
                //         $file_name = $file->getClientOriginalName();
                //         $filename = pathinfo($file_name, PATHINFO_FILENAME);
                //         $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                //         $name = $filename.'_'.time().'.'.$extension;
                //         Storage::disk('local')->putFileAs('incidents', $file, $name);
                //         array_push($names, $name);
                //     }
                // }
                $incident = new Incident;
                $incident->product_type = $data['product_type'];
                $incident->product_id = $data['product_id'];
                $incident->pic_name = $data['pic_name'];
                $incident->pic_contact = $data['pic_contact'];
                $incident->location_id = $data['location_id'];
                $incident->problem = $data['problem'];
                $incident->incident_time = $data['incident_time'];
                $incident->files = $data['files'];
                $incident->description = $data['description'];
                $incident->save();

                $ticketable_type = 'App\Incident';
                $ticketable_id = $incident->id;
            }
            
            $causer_id = auth()->user()->id;
            $generalService = new GeneralService;
            $current_timestamp = $generalService->getTimeNow();
            
            $ticket = new Ticket;
            $ticket->ticketable_id = $ticketable_id;
            $ticket->ticketable_type = $ticketable_type;
            $ticket->status_id = 1;
            $ticket->raised_at = $current_timestamp;
            $ticket->requester_id = $causer_id;
            $ticket->save();

            $logService = new LogService;
            if($data['incident_time'] === null) $time = $current_timestamp;
            else $time = $data['incident_time'];
            $logService->createLogTicketIncident($ticket->id, $causer_id, $time);
            
            
            $logService->createLogTicket($ticket->id, $causer_id);

            return ["success" => true, "message" => "Ticket Berhasil Diterbitkan", "id" => $ticket->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTicket(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $ticket = Ticket::find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
                        
            $causer_id = auth()->user()->id;
            $logService = new LogService;

            if($ticket->ticketable_type === 'App\Incident'){
                $incident = Incident::find($ticket->ticketable_id);
                // return ["success" => false, "message" => [$ticket, $incident], "status" => 400];
                if($incident === null) return ["success" => false, "message" => "Ticket Tidak Memiliki Incident", "status" => 400];
                $old_incident_time = $incident->incident_time;
                $incident->product_type = $request->get('product_type');
                $incident->product_id = $request->get('product_id');
                $incident->pic_name = $request->get('pic_name');
                $incident->pic_contact = $request->get('pic_contact');
                $incident->location_id = $request->get('location_id');
                $incident->problem = $request->get('problem');
                $incident->incident_time = $request->get('incident_time');
                $incident->files = $request->get('files');
                $incident->description = $request->get('description');
                $incident->save();
                if($old_incident_time !== $incident->incident_time){
                    if($request->get('incident_time') === null) $logService->updateIncidentLogTicket($id, $ticket->getRawOriginal('raised_at'));
                    else $logService->updateIncidentLogTicket($id, $incident->incident_time);
                } 
            }
            
            $ticket->requester_id = $request->get('requester_id');
            $ticket->raised_at = $request->get('raised_at');
            $ticket->closed_at = $request->get('closed_at');
            $ticket->save();

            $logService->updateLogTicket($id, $causer_id);

            return ["success" => true, "message" => "Ticket Berhasil Diperbarui", "status" => 200];
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
            if($ticket->status === 5) return ["success" => false, "message" => "Status Ticket Sudah Closed", "status" => 400];
            if($inventory_id === null) return ["success" => false, "message" => "Id Inventory Kosong", "status" => 400];
            if($ticket->ticketable_type !== 'App\Incident') return ["success" => false, "message" => "Tipe Tiket Tidak Sesuai", "status" => 400];
            $incident = Incident::find($ticket->ticketable_id);
            if($incident === null) return ["success" => false, "message" => "Incident pada Ticket Tidak Ditemukan", "status" => 400];
            $old_inventory_id = $incident->inventory_id;
            $incident->inventory_id = $inventory_id;
            $incident->save();

            if($old_inventory_id !== $inventory_id){
                $causer_id = auth()->user()->id;
                $logService = new LogService;
                $logService->setItemLogTicket($id, $causer_id, $old_inventory_id, $inventory_id);
                $logService->associationLogInventory($id, $causer_id, $old_inventory_id, $inventory_id);
            }
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
            $status_id = $data['status_id'];
            $ticket = Ticket::find($id);
            // return ["success" => false, "message" => $data, "status" => 400];
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($ticket->status_id === 5) return ["success" => false, "message" => "Status Ticket Sudah Closed", "status" => 400];
            if($status_id < 1 || $status_id > 5) return ["success" => false, "message" => "Status Tidak Tepat", "status" => 400];
            if(strlen($notes) > 1000) return ["success" => false, "message" => "Notes Melebihi 1000 Karakter", "status" => 400];
            if($ticket->status_id === 4 && $status_id !== 5) return ["success" => false, "message" => "Status Canceled Tidak Dapat Diubah Selain Menjadi Closed", "status" => 400];
            if($status_id === 4 && $notes === null) return ["success" => false, "message" => "Untuk Status Canceled Diperlukan Keterangan (Notes)", "status" => 400];
            $generalService = new GeneralService;
            $current_timestamp = $generalService->getTimeNow();
            $old_status = $ticket->status_id;
            $ticket->status_id = $status_id;
            
            if($ticket->status_id === 5){
                $ticket->closed_at = $current_timestamp;
                // if($ticket->type === 1){
                //     $incident = Incident::find($ticket->ticketable_id);
                //     $properties = [];
                //     if($incident === null) $properties = ["false_message" => "Incident Id Not Found"];
                //     else {
                //         $inventory = Inventory::find($incident->inventory_id);
                //         if($inventory === null) $properties = ["false_message" => "Inventory Id Not Found"];
                //         else {
                //             $inventory_columns = ModelInventoryColumn::get();
                //             $inventory_values = InventoryValue::where('inventory_id', $inventory->id)->get();
                //             $additional_attributes = [];
                //             if(count($inventory_values)){
                //                 foreach($inventory_values as $inventory_value){
                //                     $inventory_value_column = $inventory_columns->where('id', $inventory_value->model_inventory_column_id)->first();
                //                     $inventory_value->name = $inventory_value_column === null ? "not_found_column" : $inventory_value_column->name;
                //                     $additional_attributes[] = $inventory_value;
                //                 }
                //             }
                //             foreach($inventory->getAttributes() as $key => $value){
                //                 $properties['attributes']['inventory'][$key] = $value;
                //             }
                //             if(count($additional_attributes)){
                //                 foreach($additional_attributes as $additional_attribute){
                //                     $properties['attributes']['inventory'][$additional_attribute->name] = $additional_attribute->value;
                //                 }
                //             }
                //         }
                //     }
                //     $notes = "Closed Condition Inventory";
                //     $logService->updateStatusLogTicket($ticket->id, $causer_id, $properties, $notes);
                // }
            }

            $ticket->save();
            $causer_id = auth()->user()->id;
            $logService = new LogService;
            if($old_status !== $ticket->status_id) $logService->updateStatusLogTicket($ticket->id, $causer_id, $ticket->status_id, $notes);

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
            $ticket = Ticket::with(['requester'])->find($id);
            $company_user_login_id = auth()->user()->company_id;
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($ticket->requester->company_id !== $company_user_login_id) return ["success" => false, "message" => "Tidak Memiliki Akses Tiket Ini", "status" => 401];
            if($ticket->status === 4) return ["success" => false, "message" => "Ticket Sudah Dalam Status Canceled", "status" => 400];
            if($ticket->status === 5) return ["success" => false, "message" => "Ticket Dalam Status Closed", "status" => 400];
            $ticket->status_id = 4;
            $ticket->save();
            $causer_id = auth()->user()->id;
            $logService = new LogService;
            $logService->updateStatusLogTicket($ticket->id, $causer_id, $ticket->status_id, $notes);
            
            return ["success" => true, "message" => $ticket->status_id, "status" => 200];
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
            $assignable_type = $data['assignable_type'];
            $assignable_id = $data['assignable_id'];
            $ticket = Ticket::find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($assignable_type === null) return ["success" => false, "message" => "Jenis yang Ditugaskan Kosong", "status" => 400];
            if($assignable_id === null) return ["success" => false, "message" => "Tujuan Penugasan Kosong", "status" => 400];
            // return ["success" => false, "message" => "MASUK", "status" => 400];
            if($assignable_type){
                $assignable_type = 'App\User';
                $user = User::find($assignable_id);
                if($user === null) return ["success" => false, "message" => "Id Petugas Tidak Ditemukan", "status" => 400];
            } else {
                $assignable_type = 'App\Group';
                $group = Group::find($assignable_id);
                if($group === null) return ["success" => false, "message" => "Id Group Tidak Ditemukan", "status" => 400];
            }
            
            $old_assignable_type = $ticket->assignable_type;
            $old_assignable_id = $ticket->assignable_id;
            
            $ticket->assignable_type = $assignable_type;
            $ticket->assignable_id = $assignable_id;
            $ticket->save();

            if($old_assignable_id !== $ticket->assignable_id || $old_assignable_type !== $ticket->assignable_type){
                $logService = new LogService;
                $causer_id = auth()->user()->id;
                $logService->assignLogTicket($ticket->id, $causer_id, $ticket->assignable_type, $ticket->assignable_id);
            }
            
            return ["success" => true, "message" => "Ticket Berhasil Ditugaskan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addNote($request, $route_name, $admin)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->get('id');
            $notes = $request->get('notes', null);
            if($notes === null) return ["success" => false, "message" => "Notes Masih Kosong", "status" => 400];
            $ticket = Ticket::with('requester')->find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                if($ticket->requester->company_id !== $company_user_login_id) return ["success" => false, "message" => "Ticket Bukan Milik Perusahaan User Login", "status" => 401];
            }
            $logService = new LogService;
            $causer_id = auth()->user()->id;
            $logService->addNoteLogTicket($id, $causer_id, $notes);
            
            return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addNoteTicket($request, $route_name)
    {
        return $this->addNote($request, $route_name, true);
    }

    public function clientAddNoteTicket($request, $route_name)
    {
        return $this->addNote($request, $route_name, false);
    }
    
    // type

    // all_field
    // requester_name
    // requester_location
    // raised_at_ticket
    // closed_ticket
    // resolved_time
    // ticket_number
    // ticket_type
    // status_ticket
    // assign_to

    // (Incident Type)
    // Jenis Produk
    // Id Produk
    // Nama PIC
    // Kontak PIC
    // Problem
    // Lokasi Problem
    // Waktu Kejadian
    // Deskripsi Kerusakan
    
    public function ticketsExport($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
    
        $generalService = new GeneralService;
        $current_timestamp = $generalService->getTimeNow();
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $engineer = $request->get('engineer', null);
        $group = $request->get('group', null);
        $type = $request->get('type', null);
        $core_attributes = json_decode($request->get('core_attributes','[1,0,0,0,0,0,0,0,0]'));
        $secondary_attributes = json_decode($request->get('secondary_attributes','[1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]'));
        $excel = Excel::download(new TicketsExport($from, $to, $group, $engineer, $type, $core_attributes, $secondary_attributes), 'tickets.xlsx');
        return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $excel, "status" => 200];
    }
    
    public function TicketExportPdf($request, $route_name, $admin)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id');
        $ticket = Ticket::with(['type','status', 'requester', 'ticketable.location','assignable'])->find($id);
        if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
        if(!$admin){
            $company_user_login_id = auth()->user()->company_id;
            if($ticket->requester->company_id !== $company_user_login_id) return ["success" => false, "message" => "Ticket Bukan Milik Perusahaan User Login", "status" => 401];
        }
        $ticket->original_raised_at = $ticket->getRawOriginal('raised_at');
        if($ticket->ticketable->id !== 0 || $ticket->ticketable->location->id !== 0){
            $ticket->ticketable->location->full_name = $ticket->ticketable->location->topParent ? $ticket->ticketable->location->topParent->name.' - '.$ticket->ticketable->location->name : $ticket->ticketable->location->name;
        }
        $ticket->ticketable->location->makeHidden('topParent');
        $visible = ['assignable_id', 'ticketable_id', 'assignable_type', 'ticketable_type', 'requester_id', 'status_id'];
        $ticket->makeVisible($visible);
        $data = ['ticket' => $ticket];
        $pdf = PDF::loadView('pdf.ticket', $data);
        return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $pdf->download('ticket.pdf'), "status" => 200];
    }

    public function ticketExport($request, $route_name)
    {
        return $this->TicketExportPdf($request, $route_name, true);
    }

    public function clientTicketExport($request, $route_name)
    {
        return $this->TicketExportPdf($request, $route_name, false);
    }
}