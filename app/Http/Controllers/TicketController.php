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

    public function getFilterTickets(Request $request){
        $route_name = "TICKET_FILTER_GET";
        
        $response = $this->ticketService->getFilterTickets($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTicketTaskStatusCounts(Request $request){
        $route_name = "TICKET_TASK_STATUS_COUNTS_GET";
        
        $response = $this->ticketService->getTicketTaskStatusCounts($request, $route_name);
        return response()->json($response, $response['status']);
    }

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

    
    
    public function getTicketStatusCounts(Request $request)
    {
        $route_name = "TICKETS_GET";
        
        $response = $this->ticketService->getAdminTicketStatusCounts($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getClientTicketStatusCounts(Request $request)
    {
        $route_name = "CLIENT_TICKETS_GET";
        
        $response = $this->ticketService->getClientTicketStatusCounts($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getTickets(Request $request)
    {
        $route_name = "TICKETS_GET";
        
        $response = $this->ticketService->getAdminTickets($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getClientTickets(Request $request)
    {
        $route_name = "CLIENT_TICKETS_GET";
        
        $response = $this->ticketService->getClientTickets($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClosedTickets(Request $request)
    {
        $route_name = "CLOSED_TICKETS_GET";
        
        $response = $this->ticketService->getAdminClosedTickets($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientClosedTickets(Request $request)
    {
        $route_name = "CLIENT_CLOSED_TICKETS_GET";
        
        $response = $this->ticketService->getClientClosedTickets($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTicket(Request $request)
    {
        $route_name = "TICKET_GET";   
        
        $response = $this->ticketService->getAdminTicket($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getClientTicket(Request $request)
    {
        
        $route_name = "CLIENT_TICKET_GET";   
        
        $response = $this->ticketService->getClientTicket($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTicket(Request $request)
    {
        $route_name = "TICKET_ADD";

        $response = $this->ticketService->addTicket($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateTicket(Request $request)
    {
        $route_name = "TICKET_UPDATE";

        $response = $this->ticketService->updateTicket($request, $route_name);
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
    
    // public function changeStatusTicket(Request $request)
    // {
    //     $route_name = "TICKET_SET_STATUS";
        
    //     $data_request = [
    //         'id' => $request->get('id', null),
    //         'notes' => $request->get('notes', null),
    //         'status_id' => $request->get('status_id', null),
    //     ];

    //     $response = $this->ticketService->changeStatusTicket($data_request, $route_name);
    //     return response()->json($response, $response['status']);
    // }
    
    public function cancelTicket(Request $request)
    {
        $route_name = "CANCEL_TICKET";

        $response = $this->ticketService->cancelAdminTicket($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function cancelClientTicket(Request $request)
    {
        $route_name = "CLIENT_CANCEL_TICKET";

        $response = $this->ticketService->cancelClientTicket($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getTicketNotesLog(Request $request)
    {
        $route_name = "CANCEL_TICKET";

        $response = $this->ticketService->getAdminTicketNotesLog($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getClientTicketNotesLog(Request $request)
    {
        $route_name = "CLIENT_CANCEL_TICKET";

        $response = $this->ticketService->getClientTicketNotesLog($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function setDeadline(Request $request)
    {
        $route_name = "TICKET_DEADLINE_SET";

        $response = $this->ticketService->setDeadline($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function assignTicket(Request $request)
    {
        $route_name = "TICKET_ASSIGN";

        $response = $this->ticketService->assignTicket($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addNoteTicket(Request $request)
    {
        $route_name = "TICKET_NOTE_ADD";

        $response = $this->ticketService->addNoteTicket($request, $route_name);
        return response()->json($response, $response['status']);
        
    }

    public function clientAddNoteTicket(Request $request)
    {
        $route_name = "CLIENT_TICKET_NOTE_ADD";

        $response = $this->ticketService->clientAddNoteTicket($request, $route_name);
        return response()->json($response, $response['status']);
        
    }

    public function ticketsExport(Request $request)
    {
        $route_name = "TICKETS_EXPORT";

        $response = $this->ticketService->TicketsExport($request, $route_name);
        if($response['success'] === false) return response()->json($response, $response['status']);
        return $response['data'];
    }

    public function ticketExport(Request $request)
    {
        $route_name = "TICKET_EXPORT";

        $response = $this->ticketService->ticketExport($request, $route_name);
        if($response['success'] === false) return response()->json($response, $response['status']);
        return $response['data'];
    }

    public function clientTicketExport(Request $request)
    {
        $route_name = "CLIENT_TICKET_EXPORT";

        $response = $this->ticketService->clientTicketExport($request, $route_name);
        if($response['success'] === false) return response()->json($response, $response['status']);
        return $response['data'];
    }

    public function getTicketTaskTypes(Request $request)
    {
        $route_name = "TICKET_TASK_TYPES_GET";

        $response = $this->ticketService->getTicketTaskTypes($request, $route_name);
        return response()->json($response, $response['status']);   
    }

    public function addTicketTaskType(Request $request)
    {
        $route_name = "TICKET_TASK_TYPE_ADD";

        $response = $this->ticketService->addTicketTaskType($request, $route_name);
        return response()->json($response, $response['status']);   
    }

    public function updateTicketTaskType(Request $request)
    {
        $route_name = "TICKET_TASK_TYPE_UPDATE";

        $response = $this->ticketService->updateTicketTaskType($request, $route_name);
        return response()->json($response, $response['status']);   
    }

    public function deleteTicketTaskType(Request $request)
    {
        $route_name = "TICKET_TASK_TYPE_DELETE";

        $response = $this->ticketService->deleteTicketTaskType($request, $route_name);
        return response()->json($response, $response['status']);   
    }
}