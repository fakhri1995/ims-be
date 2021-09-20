<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\AccessFeature;
use App\Inventory;
use App\InventoryValue;
use App\ModelInventoryColumn;
use App\Incident;
use App\Ticket;
use App\TicketActivityLog;
use DateTime;
use DateTimeZone;
use Exception;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function checkRoute($name, $auth)
    {
        $protocol = $name;
        $access_feature = AccessFeature::where('name',$protocol)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
                    "server_code" => 400,
                    "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
                ]
            ]];
        }
        $body = [
            'path_url' => $access_feature->feature_key
        ];
        $headers = [
            'Authorization' => $auth,
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/validate-feature', [
                    'headers'  => $headers,
                    'json' => $body
            ]);
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
            return ["success" => true, "id" => $log_user_id];
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]];
        }
    }

    public function getTimeNow(){
        $tz = 'Asia/Jakarta';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); 
        $dt->setTimestamp($timestamp);
        return $dt->format('Y-m-d H:i:s');
    }

    public function getLocationDetailName($data, $parent, $target_id){
        if($data['company_id'] === $target_id){
            return ["success" => true, "data" => $parent.' / '.$data['company_name']];
        } 
        if (array_key_exists('members', $data)){
            foreach($data['members'] as $company){
                $result = $this->getLocationDetailName($company, $parent, $target_id);
                if($result["success"]) return $result;
            }
        } 
        return ["success" => false, "data" => ""];
        
    }

    // Status Ticket
    // 1 = Open, 2 = On Progress, 3 = Pending, 4 = Resolved, 5 = Canceled, 6 = Closed

    public function getTicketLog(Request $request){
        $id = $request->get('id');
        $logs = TicketActivityLog::where('subject_id', $id)->get();
        foreach($logs as $log){
            $log->properties = json_decode($log->properties);
        }
        return response()->json(["success" => true, "data" => $logs]);
    }

    public function getTicketRelation(Request $request){
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        $users = [];
        $companies = [];
        $responses = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
            'headers'  => $headers
        ]);
        $responses = json_decode((string) $responses->getBody(), true)['data']['accounts'];
        foreach($responses as $response){
            $users[] = ['id' => $response['user_id'], 'name' => $response['fullname']];
        }
        
        $responses = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
            'headers'  => $headers
        ]);
        $responses = json_decode((string) $responses->getBody(), true)['data']['companies'];
        foreach($responses as $response){
            $companies[] = ['id' => $response['company_id'], 'name' => $response['company_name']];
        }

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
        return response()->json(["success" => true, "data" => $data]);
    }

    public function getTickets(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            // $tickets = Ticket::where('status', '<>', 6)->get();
            $tickets = Ticket::where('status', '<>', 6)->orderBy('created_at', 'desc')->get();
            $open_tickets_count = $tickets->where('status', 1)->count();
            $on_progress_tickets_count = $tickets->where('status', 2)->count();
            $pending_tickets_count = $tickets->where('status', 3)->count();
            $resolved_tickets_count = $tickets->where('status', 4)->count();
            $total_tickets = $open_tickets_count + $on_progress_tickets_count + $pending_tickets_count + $resolved_tickets_count;
            $data = ["total_tickets" => $total_tickets, "open_tickets_count" => $open_tickets_count, "on_progress_tickets_count" => $on_progress_tickets_count, "pending_tickets_count" => $pending_tickets_count, "resolved_tickets_count" => $resolved_tickets_count, "tickets" => $tickets];
            
            if(!count($tickets)) return response()->json(["success" => false, "message" => "Ticket masih kosong", "data" => $data]);
            return response()->json(["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $data ]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getClosedTickets(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $tickets = Ticket::where('status', 6)->get();
            $data = ["tickets" => $tickets];
            
            if(!count($tickets)) return response()->json(["success" => false, "message" => "Closed Ticket Kosong", "data" => $data]);
            return response()->json(["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $data ]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getTicket(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $ticket = Ticket::find($id);
            if($ticket === null) return response()->json(["success" => false, "message" => "Ticket tidak ditemukan"]);
            
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            $response_companies = $this->client->request('GET', '/account/v1/company-hierarchy', [
                'headers'  => $headers
            ]);
            $response_companies = json_decode((string) $response_companies->getBody(), true);
            if(array_key_exists('error', $response_companies)) {
                $ticket->requester_location_name = "Error Server C**";
            } else {
                $check = false;
                foreach($response_companies['data']['members'] as $company){
                    if($check) break;
                    if($company['company_id'] === $ticket->requester_location){
                        $ticket->requester_location_name = $company['company_name'];
                        break;
                    }
                    if (array_key_exists('members', $company)){
                        $result = $this->getLocationDetailName($company, $company['company_name'], $ticket->requester_location);
                    }
                    if($result['success']){
                        $check = true;
                        $ticket->requester_location_name = $result['data'];
                    }
                }
                if(!$check){
                    $ticket->requester_location_name = "Not Found";
                }
            } 
            if($ticket->type === 1){
                $incident = Incident::find($ticket->subject_id);
                if($incident === null) return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => false, "data" => "Data Tidak Ditemukan"]]]);
                else {
                    if(array_key_exists('error', $response_companies)) {
                        $incident->incident_place_name = "Error Server C**";
                    } else {
                        $check = false;
                        foreach($response_companies['data']['members'] as $company){
                            if($check) break;
                            if($company['company_id'] === $incident->incident_place_id){
                                $check = true;
                                $incident->incident_place_name = $company['company_name'];
                                break;
                            }
                            if (array_key_exists('members', $company)){
                                $result = $this->getLocationDetailName($company, $company['company_name'], $incident->incident_place_id);
                            }
                            if($result['success']){
                                $check = true;
                                $incident->incident_place_name = $result['data'];
                            }
                        }
                        if(!$check){
                            $incident->incident_place_name = "Not Found";
                        }
                    } 
                    if($incident->inventory_id){
                        $inventory = Inventory::find($incident->inventory_id);
                        if($inventory === null){
                            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => true, "message" => "Data Tidak Ditemukan", "data" => null]]]);
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
                            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => true, "data" => ["incident" => $incident, "inventory" => $inventory]]]]);
                        }
                    } else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket, "incident" => ["success" => false, "data" => ["incident" => $incident, "inventory" => null]]]]);
                }
                
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["ticket" => $ticket]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addTicket(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $type = (int)$request->get('type', null);
            $requester = (int)$request->get('requester', null);
            if($type === null) return response()->json(["success" => false, "message" => "Field Tipe Ticket Belum Terisi"], 400);
            if($requester === null) return response()->json(["success" => false, "message" => "Requester Belum Terisi"], 400);
            $last_sub_ticket = Ticket::where('type', $type)->orderBy('sub_id', 'desc')->first();

            if($last_sub_ticket === null) $sub_id = 1;
            else $sub_id = $last_sub_ticket->sub_id + 1;
            $subject_id = 0;
            if($type === 1){
                $validator = Validator::make($request->all(), [
                    "incident_place_id" => "required|integer",
                    "asset_id" => "required|integer",
                    "incident_time" => "required|date_format:Y-m-d H:i:s",
                    "description" => "required|string"
                    // "files" => "file|nullable"
                ]);
    
                if ($validator->fails()) {
                    return response()->json(["success" => false, "message" => (object)[
                        "errorInfo" => $validator->errors()
                    ]], 400);
                }
    
                $files = $request->file('files');
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
                $incident->incident_place_id = $request->get('incident_place_id');
                $incident->asset_id = $request->get('asset_id');
                $incident->incident_time = $request->get('incident_time');
                $incident->description = $request->get('description');
                $incident->files = json_encode($names);
                $incident->save();
                
                $subject_id = $incident->id;
            }

            $current_timestamp = $this->getTimeNow();
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                    'headers'  => $headers
            ]);
            $response = json_decode((string) $response->getBody(), true);
            $name = "Not Found";
            $users = [];
            if(array_key_exists('error', $response)) $name = "Server C** Error";
            else foreach($response['data']['accounts'] as $user) $users[] = ['id' => $user['user_id'], 'name' => $user['fullname']];
              
            if(count($users)){
                foreach($users as $user){
                    if($requester === $user['id']){
                        $name = $user['name'];
                        break;
                    }
                }
            }

            $ticket = new Ticket;
            $ticket->sub_id = $sub_id;
            $ticket->subject_id = $subject_id;
            $ticket->type = $type;
            $ticket->status = 1;
            $ticket->created_at = $current_timestamp;
            $ticket->due_to = $request->get('due_to', null);
            $ticket->requester_location = $request->get('requester_location');
            $ticket->requester = $requester;
            $ticket->requester_name = $name;
            $ticket->save();

            $log = new TicketActivityLog;
            $log->subject_id = $ticket->id;
            $log->causer_id = $check['id'];
            $log->description = "Incident Happened";
            $log->created_at = $incident->incident_time;
            $log->save();

            $properties['attributes'] = $ticket;
            $log = new TicketActivityLog;
            $log->subject_id = $ticket->id;
            $log->causer_id = $check['id'];
            $log->properties = json_encode($properties);
            $log->description = "Raised Ticket";
            $log->created_at = $current_timestamp;
            $log->save();

            return response()->json(["success" => true, "message" => "Ticket Berhasil Diterbitkan", "id" => $ticket->id]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function setItemTicket(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $inventory_id = $request->get('inventory_id');
            $ticket = Ticket::find($id);
            if($ticket === null) return response()->json(["success" => false, "message" => "Id Ticket Tidak Ditemukan"]);
            if($ticket->status === 6) return response()->json(["success" => false, "message" => "Status Ticket Sudah Closed"]);
            if($inventory_id === null) return response()->json(["success" => false, "message" => "Id Inventory Kosong"]);
            if($ticket->type !== 1) return response()->json(["success" => false, "message" => "Tipe Tiket Tidak Sesuai"]);
            $incident = Incident::find($ticket->subject_id);
            if($incident === null) return response()->json(["success" => false, "message" => "Incident pada Ticket Tidak Ditemukan"]);
            $incident->inventory_id = $inventory_id;
            $incident->save();
            return response()->json(["success" => true, "message" => "Inventory Berhasil Ditambahkan pada Ticket"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
    
    public function changeStatusTicket(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $notes = $request->get('notes', null);
            $status = (int)$request->get('status', null);
            $ticket = Ticket::find($id);
            if($ticket === null) return response()->json(["success" => false, "message" => "Id Ticket Tidak Ditemukan"]);
            if($ticket->status === 6) return response()->json(["success" => false, "message" => "Status Ticket Sudah Closed"]);
            if($status < 1 || $status > 6) return response()->json(["success" => false, "message" => "Status Tidak Tepat"]);
            if(strlen($notes) > 1000) return response()->json(["success" => false, "message" => "Notes Melebihi 1000 Karakter"]);
            if($ticket->status === 5 && $status === 1) return response()->json(["success" => false, "message" => "Status Canceled Tidak Dapat Diubah Menjadi Open Kembali"]);
            $current_timestamp = $this->getTimeNow();
            $old_ticket = [];
            foreach($ticket->getAttributes() as $key => $value) $old_ticket[$key] = $value;
            $ticket->status = $status;
            $ticket->save();
            $properties = [];
            foreach($ticket->getAttributes() as $key => $value){
                if($ticket->$key !== $old_ticket[$key]){
                    $properties['attributes'][$key] = $ticket->$key;
                    $properties['old'][$key] = $old_ticket[$key];
                }
            }
            
            $log = new TicketActivityLog;
            $log->subject_id = $ticket->id;
            $log->description = $notes;
            $log->causer_id = $check['id'];
            $log->properties = json_encode($properties);
            $log->created_at = $current_timestamp;
            $log->save();

            return response()->json(["success" => true, "message" => "Berhasil Merubah Status Ticket"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
    
    public function assignTicket(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACTS_GET", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $asign_to_id = (int) $request->get('asign_to_id', null);
            $ticket = Ticket::find($id);
            if($ticket === null) return response()->json(["success" => false, "message" => "Id Ticket Tidak Ditemukan"]);
            $headers = [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ];
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true', [
                    'headers'  => $headers
            ]);
            $response = json_decode((string) $response->getBody(), true);
            $name = "Not Found";
            $users = [];
            if(array_key_exists('error', $response)) return response()->json(["success" => false, "message" => "Error Ids Server C**"], 500);
            else foreach($response['data']['accounts'] as $user) $users[] = ['id' => $user['user_id'], 'name' => $user['fullname']];
              
            if(count($users)){
                foreach($users as $user){
                    if($asign_to_id === $user['id']){
                        $name = $user['name'];
                        break;
                    }
                }
            }
            $old_ticket = [];
            foreach($ticket->getAttributes() as $key => $value) $old_ticket[$key] = $value;

            $ticket->asign_to = $asign_to_id;
            $ticket->asign_to_name = $name;
            $ticket->save();
            $properties = [];
            foreach($ticket->getAttributes() as $key => $value){
                if($ticket->$key !== $old_ticket[$key]){
                    $properties['attributes'][$key] = $ticket->$key;
                    $properties['old'][$key] = $old_ticket[$key];
                }
            }
            
            $current_timestamp = $this->getTimeNow();
            $log = new TicketActivityLog;
            $log->subject_id = $ticket->id;
            $log->causer_id = $check['id'];
            $log->properties = json_encode($properties);
            $log->created_at = $current_timestamp;
            $log->save();
            
            return response()->json(["success" => true, "message" => "Ticket Berhasil Ditugaskan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function cancelTicket(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $ticket = Ticket::find($id);
            if($ticket === null) return response()->json(["success" => false, "message" => "Id Ticket Tidak Ditemukan"]);
            
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $tickets ]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}