<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->ticketService = new TicketService;
    }

    // Status Ticket
    // 1 = Open, 2 = On Progress, 3 = Pending, 4 = Resolved, 5 = Canceled, 6 = Closed

    public function getTicketRelation(Request $request){
        $route_name = "TICKET_GET";
        
        $response = $this->ticketService->getTicketRelation($route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientTicketRelation(Request $request){
        $route_name = "CLIENT_TICKET_GET";
        
        $response = $this->ticketService->getClientTicketRelation($route_name);
        return response()->json($response, $response['status']);
    }

    public function getTickets(Request $request)
    {
        $route_name = "TICKETS_GET";
        
        $response = $this->ticketService->getAdminTickets($route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getClientTickets(Request $request)
    {
        $route_name = "CLIENT_TICKETS_GET";
        
        $response = $this->ticketService->getClientTickets($route_name);
        return response()->json($response, $response['status']);
    }

    public function getClosedTickets(Request $request)
    {
        $route_name = "CLOSED_TICKETS_GET";
        
        $response = $this->ticketService->getAdminClosedTickets($route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientClosedTickets(Request $request)
    {
        $route_name = "CLIENT_CLOSED_TICKETS_GET";
        
        $response = $this->ticketService->getClientClosedTickets($route_name);
        return response()->json($response, $response['status']);
    }

    public function getTicket(Request $request)
    {
        
        $route_name = "TICKET_GET";   
        $id = $request->get('id', null);
        
        $response = $this->ticketService->getAdminTicket($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientTicket(Request $request)
    {
        
        $route_name = "CLIENT_TICKET_GET";   
        $id = $request->get('id', null);
        
        $response = $this->ticketService->getClientTicket($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTicket(Request $request)
    {
        $route_name = "TICKET_ADD";
        
        $data_request = [
            'type' => (int)$request->get('type', null),
            'requester' => (int)$request->get('requester', null),
            'files' => $request->file('files'),
            'incident_place_id' => $request->get('incident_place_id'),
            'asset_id' => $request->get('asset_id'),
            'incident_time' => $request->get('incident_time'),
            'description' => $request->get('description'),
            'requester_location' => $request->get('requester_location'),
            'due_to' => $request->get('due_to', null)
        ];

        $response = $this->ticketService->addTicket($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function setItemTicket(Request $request)
    {
        $route_name = "TICKET_SET_ITEM";
        
        $data_request = [
            'id' => $request->get('id', null),
            'inventory_id' => $request->get('inventory_id')
        ];

        $response = $this->ticketService->setItemTicket($data_request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function changeStatusTicket(Request $request)
    {
        $route_name = "TICKET_SET_STATUS";
        
        $data_request = [
            'id' => $request->get('id', null),
            'notes' => $request->get('notes', null),
            'status' => $request->get('status', null),
        ];

        $response = $this->ticketService->changeStatusTicket($data_request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function cancelClientTicket(Request $request)
    {
        $route_name = "TICKET_SET_ITEM";
        
        $data_request = [
            'id' => $request->get('id', null),
            'notes' => $request->get('notes')
        ];

        $response = $this->ticketService->cancelClientTicket($data_request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function assignTicket(Request $request)
    {
        $route_name = "TICKET_ASSIGN";
        
        $data_request = [
            'id' => $request->get('id', null),
            'asign_to_id' => $request->get('asign_to_id', null)
        ];

        $response = $this->ticketService->assignTicket($data_request, $route_name);
        return response()->json($response, $response['status']);
    }
}