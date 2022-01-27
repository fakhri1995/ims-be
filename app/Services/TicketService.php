<?php

namespace App\Services;
use PDF;
use Excel;
use App\Task;
use App\User;
use App\Group;
use Exception;
use App\Ticket;
use App\Company;
use App\Incident;
use App\TaskType;
use App\TaskDetail;
use App\TicketType;
use App\TicketTaskType;
use App\Services\LogService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Exports\TicketsExport;
use App\Services\CompanyService;
use Illuminate\Support\Facades\DB;
use App\Services\CheckRouteService;

class TicketService
{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }

    // Status Ticket
    // 1 = Open, 2 = On Progress, 3 = On Hold, 4 = Canceled, 5 = Closed

    public function getFilterTickets($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', 0);
        $tickets = Ticket::select('id', 'ticketable_id','ticketable_type')->with('type');
        if($id) $tickets = $tickets->where('ticketable_id', $id);
        $tickets = $tickets->limit(50)->get();
        foreach($tickets as $ticket){
            $ticket->name = $ticket->type->code.'-'.sprintf('%03d', $ticket->ticketable_id);
        }
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $tickets, "status" => 200];
    }

    public function getTicketRelation($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $companyService = new CompanyService;
        $companies = $companyService->getCompanyTreeSelect(1, 'clientChild');

        $statuses = [
            (object)[
                'id' => 1,
                'name' => 'Overdue'
            ], 
            (object)[
                'id' => 2,
                'name' => 'Open'
            ], 
            (object)[
                'id' => 3,
                'name' => 'On progress'
            ], 
            (object)[
                'id' => 4,
                'name' => 'On hold'
            ], 
            (object)[
                'id' => 5,
                'name' => 'Completed'
            ], 
            (object)[
                'id' => 6,
                'name' => 'Closed'
            ], 
            (object)[
                'id' => 7,
                'name' => 'Canceled'
        ]];

        $resolved_times = [
            (object)[
                'from' => null,
                'to' => 10801
            ], 
            (object)[
                'from' => 10800,
                'to' => 43201
            ], 
            (object)[
                'from' => 43200,
                'to' => 108001
            ], 
            (object)[
                'from' => 108000,
                'to' => 259201
            ], 
            (object)[
                'from' => 259200,
                'to' => null
            ]
        ];

        $ticket_types = TicketType::all();
        $ticket_task_types = DB::table('ticket_task_types')
            ->select('ticket_task_types.id', 'ticket_task_types.ticket_type_id as type_id', 'ticket_task_types.name', 'ticket_task_types.name')
            ->whereNull('ticket_task_types.deleted_at')->get();

        $data = ["status_ticket" => $statuses, "ticket_types" => $ticket_types, "companies" => $companies, "ticket_task_types" => $ticket_task_types, "resolved_times" => $resolved_times];
        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getClientTicketRelation($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        
        $companyService = new CompanyService;
        $companies = $companyService->getLocations();

        $statuses = [
            (object)[
                'id' => 1,
                'name' => 'Dalam Proses'
            ], 
            (object)[
                'id' => 2,
                'name' => 'Menunggu Staff'
            ],
            (object)[
                'id' => 6,
                'name' => 'Selesai'
            ], 
            (object)[
                'id' => 7,
                'name' => 'Dibatalkan'
        ]];

        $resolved_times = [
            (object)[
                'from' => null,
                'to' => 10801
            ], 
            (object)[
                'from' => 10800,
                'to' => 43201
            ], 
            (object)[
                'from' => 43200,
                'to' => 108001
            ], 
            (object)[
                'from' => 108000,
                'to' => 259201
            ], 
            (object)[
                'from' => 259200,
                'to' => null
            ]
        ];

        $ticket_types = TicketType::all();

        $ticket_task_types = DB::table('ticket_task_types')
            ->select('ticket_task_types.id', 'ticket_task_types.ticket_type_id as type_id', 'ticket_task_types.name', 'ticket_task_types.name')
            ->whereNull('ticket_task_types.deleted_at')->get();

        $data = ["status_ticket" => $statuses, "ticket_types" => $ticket_types, "companies" => $companies, "ticket_task_types" => $ticket_task_types, "resolved_times" => $resolved_times];
        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getTicketTaskStatusCounts($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $status_tickets = Ticket::select(DB::raw('status, count(*) as status_count'))
            ->join('tasks', 'tickets.task_id', '=', 'tasks.id');
        // if(!$admin){
        //     $company_user_login_id = auth()->user()->company_id;
        //     $status_tickets = $status_tickets->whereHas('task.creator', function($query) use ($company_user_login_id){
        //         $query->where('users.company_id', $company_user_login_id);
        //     });
        //     $statuses = ['-','Dalam Proses', 'Menunggu Staff', 'Dalam Proses', 'Dalam Proses', 'Completed', 'Selesai', 'Dibatalkan'];
        // } else 
        $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $status_tickets = $status_tickets->groupBy('tasks.status')->get();
        $sum_ticket = $status_tickets->sum('status_count');
        $list = [];
        for($i = 1; $i < 8; $i++){
            if($i === 5) continue;
            $search = $status_tickets->search(function($query) use($i){
                return $query->status == $i;
            });
            if($search !== false){
                $temp_list = $status_tickets[$search]; 
                $temp_list->status_name = $statuses[$i];
                $list[] = $temp_list;
            } else {
                $list[] = (object)["status" => $i, "status_count" => 0, "status_name" => $statuses[$i]]; 
            }
        }
            
        $ticket_statuses = (object)[
            "statuses" => $list,
            "sum_ticket" => $sum_ticket
        ];

        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $assigned_only = $request->get('assigned_only', null);
        $location = $request->get('location', null);
            
        $login_id = auth()->user()->id;
        $task_ids = DB::table('task_user')->where('user_id', $login_id)->pluck('task_id');
        
        $status_list = DB::table('tasks')->whereIn('id', $task_ids)->select(DB::raw('status, count(*) as status_count'))->groupBy('status')->get();
        $status_list_name = ["-", "Overdue", "Open", "On progress", "On hold", "Completed", "Closed"];
        
        $list = new Collection();
        $active_task = 0;
        $sum_task = $status_list->sum('status_count');
        for($i = 1; $i < 7; $i++){
            $search = $status_list->search(function($query) use($i){
                return $query->status == $i;
            });

            if($search !== false){
                $temp_list = $status_list[$search]; 
                $temp_list->status_name = $status_list_name[$i];
                $temp_list->percentage = $sum_task !== 0 ? round(($status_list[$search]->status_count / $sum_task * 100), 2) : 0;
                $list->push($temp_list);
                if($i < 5) $active_task += $temp_list->status_count;
            } else {
                $list->push((object)["status" => $i, "status_count" => 0, "status_name" => $status_list_name[$i], "percentage" => 0]); 
            }

        }
        $task_statuses = (object)[
            "status_list" => $list,
            "sum_task" => $sum_task,
            "active_task" => $active_task,
        ];
        $data = (object)[
            "ticket" => $ticket_statuses,
            "task" => $task_statuses,
            "user" => (object)[
                "image_profile" => auth()->user()->profile_image,
                "name" => auth()->user()->name,
            ]
        ];
        
        return ["success" => true, "message" => "Status Ticket Berhasil Diambil", "data" => $data, "status" => 200];
    }

    private function getTicketStatusCounts(Request $request, $admin)
    {
        $status_tickets = Ticket::select(DB::raw('status, count(*) as status_count'))
            ->join('tasks', 'tickets.task_id', '=', 'tasks.id');
        if(!$admin){
            $company_user_login_id = auth()->user()->company_id;
            $status_tickets = $status_tickets->whereHas('task.creator', function($query) use ($company_user_login_id){
                $query->where('users.company_id', $company_user_login_id);
            });
            $statuses = ['-','Dalam Proses', 'Menunggu Staff', 'Dalam Proses', 'Dalam Proses', 'Completed', 'Selesai', 'Dibatalkan'];
        } else $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $status_tickets = $status_tickets->groupBy('tasks.status')->get();
        $sum_ticket = $status_tickets->sum('status_count');
        $list = [];
        for($i = 1; $i < 8; $i++){
            if($i === 5) continue;
            $search = $status_tickets->search(function($query) use($i){
                return $query->status == $i;
            });
            if($search !== false){
                $temp_list = $status_tickets[$search]; 
                $temp_list->status_name = $statuses[$i];
                $list[] = $temp_list;
            } else {
                $list[] = (object)["status" => $i, "status_count" => 0, "status_name" => $statuses[$i]]; 
            }
        }
        if(!$admin){
            $list[0]->status_count = $list[0]->status_count + $list[2]->status_count + $list[3]->status_count;
            unset($list[2]);
            unset($list[3]);
            $list = array_values($list);
            $data = (object)[
                "statuses" => $list,
                "sum_ticket" => $sum_ticket
            ];
        } else {
            $total_counts = Ticket::whereNotNull('closed_at')->count();
            $three_hours = Ticket::where('resolved_times', '<', 10801)->count();
            $three_to_twelve_hours = Ticket::where('resolved_times', '>', 10800)->where('resolved_times', '<', 43201)->count();
            $twelve_to_thirty_hours = Ticket::where('resolved_times', '>', 43200)->where('resolved_times', '<', 108001)->count();
            $thirty_hours_to_three_days = Ticket::where('resolved_times', '>', 108000)->where('resolved_times', '<', 259201)->count();
            $three_days = Ticket::where('resolved_times', '>', 259200)->count();
            
            $counts = (object)[
                "total_counts" => $total_counts,
                "three_hours" => [
                    "counts" => $three_hours,
                    "percentage" => $total_counts !== 0 ? round(($three_hours / $total_counts * 100), 2) : 0
                ],
                "three_to_twelve_hours" => [
                    "counts" => $three_to_twelve_hours,
                    "percentage" => $total_counts !== 0 ? round(($three_to_twelve_hours / $total_counts * 100), 2) : 0
                ],
                "twelve_to_thirty_hours" => [
                    "counts" => $twelve_to_thirty_hours,
                    "percentage" => $total_counts !== 0 ? round(($twelve_to_thirty_hours / $total_counts * 100), 2) : 0
                ],
                "thirty_hours_to_three_days" => [
                    "counts" => $thirty_hours_to_three_days,
                    "percentage" => $total_counts !== 0 ? round(($thirty_hours_to_three_days / $total_counts * 100), 2) : 0
                ],
                "three_days" => [
                    "counts" => $three_days,
                    "percentage" => $total_counts !== 0 ? round(($three_days / $total_counts * 100), 2) : 0
                ]
            ];
            
            $data = (object)[
                "statuses" => $list,
                "sum_ticket" => $sum_ticket,
                "counts" => $counts
            ];
        }

        

        return ["success" => true, "message" => "Status Ticket Berhasil Diambil", "data" => $data, "status" => 200];
    }

    public function getAdminTicketStatusCounts(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTicketStatusCounts($request, true);
    }

    public function getClientTicketStatusCounts(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getTicketStatusCounts($request, false);
    }

    private function getTickets(Request $request, $admin)
    {
        try{
            $ticket_id = $request->get('ticket_id', null);
            $location_id = $request->get('location_id', null);
            $status_id = $request->get('status_id', null);
            $type_id = $request->get('type_id', null);
            $from = $request->get('from', null);
            $to = $request->get('to', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');
            
            $rows = $request->get('rows', 10);
            if($rows < 0) $rows = 10;
            if($rows > 100) $rows = 100;
           
            $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                $tickets = Ticket::select('tickets.id', 'tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.task_id', 'tasks.status', 'ticket_types.id as type_id', 'ticket_types.name as type_name', 'ticket_types.code')
                ->whereHas('task.creator', function($query) use ($company_user_login_id){
                    $query->where('users.company_id', $company_user_login_id);
                });
                $statuses = ['-','Dalam Proses', 'Menunggu Staff', 'Dalam Proses', 'Dalam Proses', 'Completed', 'Selesai', 'Dibatalkan'];
            } else {
                $tickets = Ticket::select('tickets.id', 'tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.task_id', 'tasks.status', 'ticket_types.id as type_id', 'ticket_types.name as type_name', 'ticket_types.code');
                $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
            }
            
            $tickets = $tickets->with(['task:id,created_at,status,created_by,location_id', 'task.creator:id,name,company_id', 'task.users:id,name', 'task.location:id,name,parent_id,top_parent_id,role'])
                ->join('tasks', 'tickets.task_id', '=', 'tasks.id')
                ->join('ticket_types', 'tickets.ticketable_type', '=', 'ticket_types.table_name');
                // ->orderBy('tasks.task_type_id');

            if($ticket_id){
                $tickets = $tickets->where('ticketable_id', $ticket_id);
            }
            if($type_id){
                $tickets = $tickets->where('ticket_types.id', $type_id);
            }
            if($status_id){
                if(!$admin){
                    if($status_id == 1 || $status_id == 3 || $status_id == 4) $tickets = $tickets->whereIn('status', [2,3,4]);
                    else $tickets = $tickets->where('status', $status_id);
                } else {
                    $tickets = $tickets->where('status', $status_id);
                }
            }
            if($from && $to){
                $tickets = $tickets->whereBetween('tasks.created_at', [$from, $to]);
            }
            if($location_id){
                $company = Company::withTrashed()->find($location_id);
                if(!$company) return ["success" => false, "message" => "Lokasi Tidak Ditemukan", "status" => 400];
                $companyService = new CompanyService;
                $company_list = $companyService->checkSubCompanyList($company)->toArray();
                $tickets = $tickets->whereIn('location_id', $company_list);
                // $tickets = $tickets->whereHasMorph(
                //     'ticketable',
                //     ['App\Incident'],
                //     function ($query) use ($location_id){
                //         $query->where('location_id', '=', $location_id);
                //     }
                // );
            }
            if($sort_by){
                if($sort_by === 'id') $tickets = $tickets->orderBy('ticketable_id', $sort_type);
                else if($sort_by === 'type') $tickets = $tickets->orderBy('ticket_types.name', $sort_type);
                else if($sort_by === 'raised_at') $tickets = $tickets->orderBy('tasks.created_at', $sort_type);
                else if($sort_by === 'status') $tickets = $tickets->orderBy('status', $sort_type);
            }
            $tickets = $tickets->paginate($rows);

            foreach($tickets as $ticket){
                $ticket->raised_at = date('d M Y, H:i', strtotime($ticket->task->created_at));
                $ticket->full_name = $ticket->code.'-'.$ticket->ticketable_id;

                $ticket->task->creator->full_location = $ticket->task->creator->company->fullName();
                $ticket->task->creator->makeHidden(['company']);
                $ticket->task->location->full_location = $ticket->task->location->fullNameWParentTopParent();
                $ticket->task->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);
                $ticket->status_name = $statuses[$ticket->status];
            }
            return ["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $tickets, "status" => 200];
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
            $location_id = $request->get('location_id', null);
            $type_id = $request->get('type_id', null);
            $from = $request->get('from', null);
            $to = $request->get('to', null);
            $from_res = $request->get('from_res', null);
            $to_res = $request->get('to_res', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');
            
            $rows = $request->get('rows', 10);
            if($rows < 0) $rows = 10;
            if($rows > 100) $rows = 100;
           
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                $tickets = Ticket::select('tickets.id', 'tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.task_id', 'tickets.resolved_times', 'tasks.status', 'ticket_types.id as type_id', 'ticket_types.name as type_name', 'ticket_types.code')
                ->whereHas('task.creator', function($query) use ($company_user_login_id){
                    $query->where('users.company_id', $company_user_login_id);
                });
            } else {
                $tickets = Ticket::select('tickets.id', 'tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.task_id', 'tickets.resolved_times', 'tasks.status', 'ticket_types.id as type_id', 'ticket_types.name as type_name', 'ticket_types.code');
            }

            $tickets = $tickets->with(['task:id,created_at,status,created_by,location_id', 'task.creator:id,name', 'task.users:id,name', 'task.location:id,name,parent_id,top_parent_id,role'])
                ->join('tasks', 'tickets.task_id', '=', 'tasks.id')
                ->join('ticket_types', 'tickets.ticketable_type', '=', 'ticket_types.table_name')
                ->where('status', 6);

            if($ticket_id){
                $tickets = $tickets->where('ticketable_id', $ticket_id);
            }
            if($type_id){
                $tickets = $tickets->where('ticket_types.id', $type_id);
            }
            if($from_res){
                $tickets = $tickets->where('resolved_times', '>', $from_res);
            }
            if($to_res){
                $tickets = $tickets->where('resolved_times', '<', $to_res);
            }
            if($from && $to){
                $tickets = $tickets->whereBetween('tasks.created_at', [$from, $to]);
            }
            if($location_id){
                $company = Company::withTrashed()->find($location_id);
                if(!$company) return ["success" => false, "message" => "Lokasi Tidak Ditemukan", "status" => 400];
                $companyService = new CompanyService;
                $company_list = $companyService->checkSubCompanyList($company)->toArray();
                $tickets = $tickets->whereIn('location_id', $company_list);
            }
            if($sort_by){
                if($sort_by === 'id') $tickets = $tickets->orderBy('ticketable_id', $sort_type);
                else if($sort_by === 'type') $tickets = $tickets->orderBy('ticket_types.name', $sort_type);
                else if($sort_by === 'raised_at') $tickets = $tickets->orderBy('tasks.created_at', $sort_type);
                else if($sort_by === 'resolved_times') $tickets = $tickets->orderBy('resolved_times', $sort_type);
            }
            $tickets = $tickets->paginate($rows);

            foreach($tickets as $ticket){
                $ticket->raised_at = date('d M Y, H:i', strtotime($ticket->task->created_at));
                $ticket->full_name = $ticket->code.'-'.$ticket->ticketable_id;
                $ticket->resolved_times = $this->diffForHuman($ticket->resolved_times);
                $ticket->task->location->full_location = $ticket->task->location->fullSubNameWParentTopParent();
                $ticket->task->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);
            }
            return ["success" => true, "message" => "Tickets Berhasil Diambil", "data" => $tickets, "status" => 200];
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
            $ticket = Ticket::select('tickets.id', 'tickets.ticketable_id', 'tickets.ticketable_type', 'tickets.task_id', 'tickets.closed_at', 'tickets.resolved_times', 'tasks.status')
            ->with(['task:id,created_at,status,created_by,location_id,group_id,deadline', 'task.group', 'task.users', 'task.creator:id,name,company_id', 
            'task.location:id,name,parent_id,top_parent_id,role', 'task.creator.company:id,name,top_parent_id', 'type', 'ticketable.location', 'ticketable.assetType', 'ticketable.inventory'])
                ->join('tasks', 'tickets.task_id', '=', 'tasks.id')
                ->orderBy('tasks.task_type_id')->find($id);
            if($ticket === null) return ["success" => false, "message" => "Ticket Tidak Ditemukan", "status" => 400];
            $company_user_login_id = auth()->user()->company_id;
            if(!$admin && $ticket->task->creator->company_id !== $company_user_login_id) return ["success" => false, "message" => "Tidak Memiliki Akses Tiket Ini", "status" => 401];
            $ticket->name = $ticket->type->code.'-'.$ticket->ticketable_id;
            $ticket->creator_id = $ticket->task->creator->id;
            $ticket->creator_name = $ticket->task->creator->name;
            $ticket->creator_location = $ticket->task->creator->company->fullName();
            $ticket->raised_at = date("d F Y", strtotime($ticket->task->created_at));
            $ticket->resolved_times = $this->diffForHuman($ticket->resolved_times);
            $ticket->task->creator->company->makeHidden('topParent');
            $ticket->task->location->full_location = $ticket->task->location->fullSubNameWParentTopParent();
            $ticket->task->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);
            if($ticket->task->group_id === null){
                if(count($ticket->task->users)){
                    $ticket->assignment_type = "Engineer";
                    $ticket->assignment_operator_id = $ticket->task->users[0]->id;
                    $ticket->assignment_operator_name = $ticket->task->users[0]->name;
                    $ticket->assignment_profile_image = $ticket->task->users[0]->profile_image;
                } else {
                    $ticket->assignment_type = "-";
                    $ticket->assignment_operator_id = 0;
                    $ticket->assignment_operator_name = "-";
                    $ticket->assignment_profile_image = "-";
                }
            } else {
                $ticket->assignment_type = "Group";
                $ticket->assignment_operator_id = $ticket->task->group->id;
                $ticket->assignment_operator_name = $ticket->task->group->name;
                $ticket->assignment_profile_image = "-";
            }
            
            if($admin){
                $ticket->deadline = $ticket->task->deadline ? date("d M Y, H:i", strtotime($ticket->task->deadline)) : "-";
                $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
            } else {
                $ticket->deadline = $this->approximate_deadline($ticket->task->deadline);
                $statuses = ['-','Dalam Proses', 'Menunggu Staff', 'Dalam Proses', 'Dalam Proses', 'Completed', 'Selesai', 'Dibatalkan'];
            }
            $ticket->status_name = $statuses[$ticket->status];
            
            $ticket->ticketable->asset_type_name = $ticket->ticketable->assetType->name;
            $ticket->ticketable->original_incident_time = date("Y-m-d H:i:s" ,strtotime($ticket->ticketable->incident_time));
            $ticket->ticketable->incident_time = date("d F Y - H:i:s" ,strtotime($ticket->ticketable->incident_time));
            $ticket->ticketable->location->full_location = $ticket->ticketable->location->fullSubNameWParentTopParent();
            $ticket->ticketable->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);
            $ticket->makeHidden('task', 'type');
            if($ticket->ticketable_type === 'App\Incident'){
                if($ticket->ticketable->inventory !== null){
                    if($ticket->ticketable->inventory->modelInventory->id !== 0 || $ticket->ticketable->inventory->modelInventory->asset->id !== 0){
                        $ticket->ticketable->inventory->modelInventory->asset->full_name = $ticket->ticketable->inventory->modelInventory->asset->fullName();
                    }
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $ticket, "status" => 200];
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

    public function addTicket($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $type_id = $request->get('type_id');
            if($type_id === null) return ["success" => false, "message" => "Field Tipe Ticket Belum Terisi", "status" => 400];
            
            $ticketable_id = 0;
            $ticketable_type = '-';
            $causer_id = auth()->user()->id;
            $current_timestamp = date("Y-m-d H:i:s");
            $ticket_task_type_id = $request->get('ticket_task_type_id');
            $location_id = $request->get('location_id');
            $ticket_task_type = TicketTaskType::with('taskType.works','ticketType')->find($ticket_task_type_id);
            if($ticket_task_type === null) return ["success" => false, "message" => "Id Tipe Task Ticket Tidak Ditemukan", "status" => 400];
            if($type_id === 1){
                $files = $request->get('files', []);
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
                $new_task_reponse = $this->addTask($ticket_task_type, $location_id, $causer_id);
                // return ["success" => true, "message" => $new_task_reponse, "status" => 200];
                if(!$new_task_reponse['success']) return $new_task_reponse;
                $incident = new Incident;
                $incident->product_type = $request->get('ticket_task_type_id');
                $incident->product_id = $request->get('product_id');
                $incident->pic_name = $request->get('pic_name');
                $incident->pic_contact = $request->get('pic_contact');
                $incident->location_id = $location_id;
                $incident->problem = $request->get('problem');
                $incident->incident_time = $request->get('incident_time');
                $incident->files = $files;
                $incident->description = $request->get('description');
                $incident->save();
                
                $ticketable_type = 'App\Incident';
                $ticketable_id = $incident->id;
            }
            
            $new_task = $new_task_reponse['task'];
            
            $ticket = new Ticket;
            $ticket->task_id = $new_task->id;
            $ticket->ticketable_id = $ticketable_id;
            $ticket->ticketable_type = $ticketable_type;
            $ticket->save();

            $new_task->reference_id = $ticket->id;
            $new_task->save();

            $logService = new LogService;
            if($request->get('incident_time') === null) $time = $current_timestamp;
            else $time = $request->get('incident_time');
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
            $ticket = Ticket::with('task')->find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
                        
            $logService = new LogService;
            $causer_id = auth()->user()->id;
            $location_id = $request->get('location_id');
            $requester_id = $request->get('requester_id');
            $raised_at = $request->get('raised_at');
            if($ticket->ticketable_type === 'App\Incident'){
                $update_task_reponse = $this->updateTask($ticket->task, $raised_at, $location_id, $requester_id, $id);
                if(!$update_task_reponse['success']) return $update_task_reponse;
                $incident = Incident::find($ticket->ticketable_id);
                if($incident === null) return ["success" => false, "message" => "Ticket Tidak Memiliki Incident", "status" => 400];
                $old_incident_time = $incident->incident_time;
                $incident->product_id = $request->get('product_id');
                $incident->pic_name = $request->get('pic_name');
                $incident->pic_contact = $request->get('pic_contact');
                $incident->location_id = $location_id;
                $incident->problem = $request->get('problem');
                $incident->incident_time = $request->get('incident_time');
                $incident->files = $request->get('files');
                $incident->description = $request->get('description');
                $incident->save();
                if($old_incident_time !== $incident->incident_time){
                    if($request->get('incident_time') !== null) $logService->updateIncidentLogTicket($id, $incident->incident_time);
                } 
            }
            $closed_at = $request->get('closed_at');
            if($ticket->closed_at !== $closed_at){
                $ticket->resolved_times = strtotime($closed_at) - strtotime($task->created_at);
            } 
            $ticket->closed_at = $closed_at;
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
            if($ticket->closed_at !== null) return ["success" => false, "message" => "Status Ticket Sudah Closed", "status" => 400];
            if($ticket->ticketable_type !== 'App\Incident') return ["success" => false, "message" => "Tipe Tiket Tidak Sesuai", "status" => 400];
            $incident = Incident::find($ticket->ticketable_id);
            if($incident === null) return ["success" => false, "message" => "Incident pada Ticket Tidak Ditemukan", "status" => 400];
            if($inventory_id === null && $incident->inventory_id === null) return ["success" => false, "message" => "Id Inventory Kosong", "status" => 400];
            $old_inventory_id = $incident->inventory_id;
            $incident->inventory_id = $inventory_id;
            $incident->save();

            $causer_id = auth()->user()->id;
            $logService = new LogService;
            if($inventory_id === null){
                $logService->removeItemLogTicket($id, $causer_id, $old_inventory_id);
                $logService->removeAssociationLogInventory($id, $causer_id, $old_inventory_id);
                return ["success" => true, "message" => "Inventory Berhasil Dikeluarkan dari Ticket", "status" => 200];
            }
            if($old_inventory_id !== $inventory_id){
                $logService->setItemLogTicket($id, $causer_id, $old_inventory_id, $inventory_id);
                $logService->associationLogInventory($id, $causer_id, $old_inventory_id, $inventory_id);
                return ["success" => true, "message" => "Inventory Berhasil Ditambahkan pada Ticket", "status" => 200];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    // public function changeStatusTicket($data, $route_name)
    // {
    //     $access = $this->checkRouteService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     try{
    //         $id = $data['id'];
    //         $notes = $data['notes'];
    //         $status_id = $data['status_id'];
    //         $ticket = Ticket::find($id);
    //         // return ["success" => false, "message" => $data, "status" => 400];
    //         if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
    //         if($ticket->status_id === 5) return ["success" => false, "message" => "Status Ticket Sudah Closed", "status" => 400];
    //         if($status_id < 1 || $status_id > 5) return ["success" => false, "message" => "Status Tidak Tepat", "status" => 400];
    //         if(strlen($notes) > 1000) return ["success" => false, "message" => "Notes Melebihi 1000 Karakter", "status" => 400];
    //         if($ticket->status_id === 4 && $status_id !== 5) return ["success" => false, "message" => "Status Canceled Tidak Dapat Diubah Selain Menjadi Closed", "status" => 400];
    //         if($status_id === 4 && $notes === null) return ["success" => false, "message" => "Untuk Status Canceled Diperlukan Keterangan (Notes)", "status" => 400];
    //         $current_timestamp = date("Y-m-d H:i:s");
    //         $old_status = $ticket->status_id;
    //         $ticket->status_id = $status_id;
            
    //         if($ticket->status_id === 5){
    //             $ticket->closed_at = $current_timestamp;
    //             // if($ticket->type === 1){
    //             //     $incident = Incident::find($ticket->ticketable_id);
    //             //     $properties = [];
    //             //     if($incident === null) $properties = ["false_message" => "Incident Id Not Found"];
    //             //     else {
    //             //         $inventory = Inventory::find($incident->inventory_id);
    //             //         if($inventory === null) $properties = ["false_message" => "Inventory Id Not Found"];
    //             //         else {
    //             //             $inventory_columns = ModelInventoryColumn::get();
    //             //             $inventory_values = InventoryValue::where('inventory_id', $inventory->id)->get();
    //             //             $additional_attributes = [];
    //             //             if(count($inventory_values)){
    //             //                 foreach($inventory_values as $inventory_value){
    //             //                     $inventory_value_column = $inventory_columns->where('id', $inventory_value->model_inventory_column_id)->first();
    //             //                     $inventory_value->name = $inventory_value_column === null ? "not_found_column" : $inventory_value_column->name;
    //             //                     $additional_attributes[] = $inventory_value;
    //             //                 }
    //             //             }
    //             //             foreach($inventory->getAttributes() as $key => $value){
    //             //                 $properties['attributes']['inventory'][$key] = $value;
    //             //             }
    //             //             if(count($additional_attributes)){
    //             //                 foreach($additional_attributes as $additional_attribute){
    //             //                     $properties['attributes']['inventory'][$additional_attribute->name] = $additional_attribute->value;
    //             //                 }
    //             //             }
    //             //         }
    //             //     }
    //             //     $notes = "Closed Condition Inventory";
    //             //     $logService->updateStatusLogTicket($ticket->id, $causer_id, $properties, $notes);
    //             // }
    //         }

    //         $ticket->save();
    //         $causer_id = auth()->user()->id;
    //         $logService = new LogService;
    //         if($old_status !== $ticket->status_id) $logService->updateStatusLogTicket($ticket->id, $causer_id, $ticket->status_id, $notes);

    //         return ["success" => true, "message" => "Berhasil Merubah Status Ticket", "status" => 200];
    //     } catch(Exception $err){
    //         return ["success" => false, "message" => $err, "status" => 400];
    //     }
    // }
    
    public function cancelTicket($request, $admin)
    {
        try{
            $id = $request->get('id');
            $notes = $request->get('notes');
            if($notes === null) return ["success" => false, "message" => "Untuk Status Canceled Diperlukan Keterangan (Notes)", "status" => 400];
            $ticket = Ticket::with(['task.taskDetails', 'task.users', 'task.group'])->find($id);
            $company_user_login_id = auth()->user()->company_id;
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if(!$admin && $ticket->task->creator->company_id !== $company_user_login_id) return ["success" => false, "message" => "Tidak Memiliki Akses Tiket Ini", "status" => 401];
            if($ticket->task->status === 7) return ["success" => false, "message" => "Ticket Sudah Dicancel", "status" => 400];
            $old_status = $ticket->task->status;
            $ticket->task->status = 7;
            $ticket->task->save();
            $ticket->task->delete();
            $ticket->task->taskDetails()->delete();
            $ticket->task->users()->detach();

            $causer_id = auth()->user()->id;
            $logService = new LogService;
            $logService->updateStatusLogTicket($ticket->id, $causer_id, $old_status, 7, $notes);
            
            return ["success" => true, "message" => "Cancel Tiket Berhasil", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function cancelAdminTicket(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        return $this->cancelTicket($request, true);
    }

    public function cancelClientTicket(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        return $this->cancelTicket($request, false);
    }
    
    // public function cancelClientTicket($data, $route_name)
    // {
    //     $access = $this->checkRouteService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     try{
    //         $id = $data['id'];
    //         $notes = $data['notes'];
    //         // return ["success" => true, "message" => $data, "status" => 400];
    //         if($notes === null) return ["success" => false, "message" => "Untuk Status Canceled Diperlukan Keterangan (Notes)", "status" => 400];
    //         $ticket = Ticket::with(['requester'])->find($id);
    //         $company_user_login_id = auth()->user()->company_id;
    //         if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
    //         if($ticket->task->creator->company_id !== $company_user_login_id) return ["success" => false, "message" => "Tidak Memiliki Akses Tiket Ini", "status" => 401];
    //         if($ticket->status === 4) return ["success" => false, "message" => "Ticket Sudah Dalam Status Canceled", "status" => 400];
    //         if($ticket->status === 5) return ["success" => false, "message" => "Ticket Dalam Status Closed", "status" => 400];
    //         $ticket->status_id = 4;
    //         $ticket->save();
    //         $causer_id = auth()->user()->id;
    //         $logService = new LogService;
    //         $logService->updateStatusLogTicket($ticket->id, $causer_id, $ticket->status_id, $notes);
            
    //         return ["success" => true, "message" => $ticket->status_id, "status" => 200];
    //     } catch(Exception $err){
    //         return ["success" => false, "message" => $err, "status" => 400];
    //     }
    // }
    
    public function assignTicket($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->get('id');
            $assignable_type = $request->get('assignable_type');
            $assignable_id = $request->get('assignable_id');
            $ticket = Ticket::with(['task.taskDetails','task.users', 'task.group'])->find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if($assignable_type === null) return ["success" => false, "message" => "Jenis yang Ditugaskan Kosong", "status" => 400];
            if($assignable_id === null) return ["success" => false, "message" => "Tujuan Penugasan Kosong", "status" => 400];
            
            
            if($ticket->task->group_id !== null){
                $old_assignable_type = 'App\Group';
                $old_assignable_id = $ticket->task->group_id;
            } else {
                if(count($ticket->task->users)){
                    $old_assignable_type = 'App\User';
                    $old_assignable_id = $ticket->task->users[0]->user_id;
                } else {
                    $old_assignable_type = null;
                    $old_assignable_id = null;
                }
            }
            
            if($assignable_type){
                $assignable_type = 'App\User';
                $user = User::find($assignable_id);
                if($user === null) return ["success" => false, "message" => "Id Petugas Tidak Ditemukan", "status" => 400];
                $ticket->task->users()->sync($assignable_id);
                foreach($ticket->task->taskDetails as $taskDetail){
                    $taskDetail->users()->sync($assignable_id);
                }    
            } else {
                $assignable_type = 'App\Group';
                $group = Group::with('users')->find($assignable_id);
                if($group === null) return ["success" => false, "message" => "Id Group Tidak Ditemukan", "status" => 400];
                $assignable_ids = $group->users->pluck('id');
                $ticket->task->users()->sync($assignable_ids);
                $ticket->task->group_id = $assignable_id;
                $ticket->task->save();
                foreach($ticket->task->taskDetails as $taskDetail){
                    $taskDetail->users()->sync($assignable_ids);
                }  
            }

            if($old_assignable_id !== $assignable_id || $old_assignable_type !== $assignable_type){
                $logService = new LogService;
                $causer_id = auth()->user()->id;
                $logService->assignLogTicket($ticket->id, $causer_id, $assignable_type, $assignable_id);
            }
            
            return ["success" => true, "message" => "Ticket Berhasil Ditugaskan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function setDeadline($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->get('id');
            $ticket = Ticket::with('task')->find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            $deadline = $request->get('deadline');
            if($deadline === null) return ["success" => false, "message" => "Deadline Masih Kosong", "status" => 400];
            $ticket->task->deadline = $deadline;
            $ticket->task->save();
            $logService = new LogService;
            $causer_id = auth()->user()->id;
            $logService->setDeadlineLogTicket($id, $causer_id);
            
            return ["success" => true, "message" => "Deadline Ticket Berhasil Diubah", "status" => 200];
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
            $ticket = Ticket::with('task.creator')->find($id);
            if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
            if(!$admin){
                $company_user_login_id = auth()->user()->company_id;
                if($ticket->task->creator->company_id !== $company_user_login_id) return ["success" => false, "message" => "Ticket Bukan Milik Perusahaan User Login", "status" => 401];
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
    // No Ticket
    // Nama Pembuat
    // Lokasi Pembuat
    // Tanggal Diajukan
    // Tanggal Ditutup
    // Durasi Pengerjaan
    // Nama Engineer / Group
    // Status Ticket

    // (Incident Type)
    // Jenis Aset
    // Terminal Id
    // Nama PIC
    // Kontak PIC
    // Waktu Kejadian
    // Lokasi Kejadian
    // Deskripsi Kerusakan
    
    public function ticketsExport($request, $route_name)
    {
        // $access = $this->checkRouteService->checkRoute($route_name);
        // if($access["success"] === false) return $access;
    
        $current_timestamp = date("Y-m-d H:i:s");
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $engineer = $request->get('engineer', null);
        $group = $request->get('group', null);
        $type = $request->get('type', null);
        $is_history = $request->get('is_history', false);
        $core_attributes = json_decode($request->get('core_attributes','[1,0,0,0,0,0,0,0,0]'));
        $secondary_attributes = json_decode($request->get('secondary_attributes','[1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]'));
        $name_date = date("d-m-Y H:i");
        $excel = Excel::download(new TicketsExport($from, $to, $group, $engineer, $type, $is_history, $core_attributes, $secondary_attributes), 'Ticket-'.$name_date.'.xlsx');
        return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $excel, "status" => 200];
    }
    
    public function TicketExportPdf($request, $route_name, $admin)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id');
        $ticket = Ticket::with(['task:id,created_at,status,created_by,location_id,group_id,deadline', 'task.creator:id,name,company_id', 
            'task.location:id,name,parent_id,top_parent_id,role', 'task.creator.company:id,name,top_parent_id', 'type', 'ticketable.assetType'])->find($id);
        if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
        if(!$admin){
            $company_user_login_id = auth()->user()->company_id;
            if($ticket->task->creator->company_id !== $company_user_login_id) return ["success" => false, "message" => "Ticket Bukan Milik Perusahaan User Login", "status" => 401];
        }

        $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled'];
        $ticket->status = $statuses[$ticket->task->status];
        $ticket->creator_location = $ticket->task->creator->company->fullName();
        $ticket->raised_at = date("Y-m-d H:i:s", strtotime($ticket->task->created_at));
        $ticket->task->location->full_location = $ticket->task->location->fullSubNameWParentTopParent();
        $ticket->ticketable->incident_time = date("Y-m-d H:i:s" ,strtotime($ticket->ticketable->incident_time));    
        $data = ['ticket' => $ticket];
        $pdf = PDF::loadView('pdf.ticket', $data);
        return ["success" => true, "message" => "Berhasil Membuat Notes Ticket", "data" => $pdf->download('Ticket '.$ticket->type->code.'-'.$ticket->ticketable_id.'.pdf'), "status" => 200];
    }

    public function ticketExport($request, $route_name)
    {
        return $this->TicketExportPdf($request, $route_name, true);
    }

    public function clientTicketExport($request, $route_name)
    {
        return $this->TicketExportPdf($request, $route_name, false);
    }

    public function getTicketNotesLog($request, $admin)
    {
        $id = $request->get('id');
        $ticket = Ticket::with('task.creator:id,company_id')->find($id);
        if($ticket === null) return ["success" => false, "message" => "Id Ticket Tidak Ditemukan", "status" => 400];
        $company_user_login_id = auth()->user()->company_id;
        if(!$admin && $ticket->task->creator->company_id !== $company_user_login_id) return ["success" => false, "message" => "Tidak Memiliki Akses Tiket Ini", "status" => 401];
        
        $logService = new LogService;
        $logs = $logService->getTicketNotesLog($id);
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $logs, "status" => 200];
    }

    public function getAdminTicketNotesLog(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        return $this->getTicketNotesLog($request, true);
    }

    public function getClientTicketNotesLog(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        return $this->getClientTicketNotesLog($request, false);
    }

    // Ticket Task Types
    public function getTicketTaskTypes($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');
            
            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;

            $ticket_task_types = DB::table('ticket_task_types')
            ->select('ticket_task_types.id', 'ticket_types.name as ticket_type_name', 'task_types.name as task_type_name', 'ticket_task_types.name', 'ticket_task_types.description','ticket_task_types.task_type_id','ticket_task_types.ticket_type_id')
            ->join('ticket_types', 'ticket_task_types.ticket_type_id', '=', 'ticket_types.id')
            ->join('task_types', 'ticket_task_types.task_type_id', '=', 'task_types.id')
            ->whereNull('ticket_task_types.deleted_at');

            if($sort_by){
                if($sort_by === 'name') $ticket_task_types = $ticket_task_types->orderBy('name', $sort_type);
                else if($sort_by === 'ticket_type_name') $ticket_task_types = $ticket_task_types->orderBy('ticket_type_name', $sort_type);
                else if($sort_by === 'task_type_name') $ticket_task_types = $ticket_task_types->orderBy('task_type_name', $sort_type);
                else if($sort_by === 'id') $ticket_task_types = $ticket_task_types->orderBy('id', $sort_type);
                else if($sort_by === 'description') $ticket_task_types = $ticket_task_types->orderBy('description', $sort_type);
            }
            
            if($keyword) $ticket_task_types = $ticket_task_types->where('ticket_task_types.name', 'ilike', "%".$keyword."%")->orWhere('task_types.name', 'ilike', "%".$keyword."%")->orWhere('ticket_types.name', 'ilike', "%".$keyword."%")->orWhere('ticket_task_types.description', 'ilike', "%".$keyword."%");
            $ticket_task_types = $ticket_task_types->paginate($rows);
            if($ticket_task_types->isEmpty()) return ["success" => true, "message" => "Ticket Task Type Masih Kosong", "data" => $ticket_task_types, "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $ticket_task_types, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addTicketTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $ticket_task_type = new TicketTaskType;
        $req_task_type_id = $request->get('task_type_id');
        $req_name = $request->get('name');
        $check = TicketTaskType::where('task_type_id', $req_task_type_id)->where('name', $req_name)->first();
        if($check) return ["success" => false, "message" => "Nama Tipe Tiket Task pada Tipe Task Sudah Ada", "status" => 400];
        $ticket_task_type->name = $req_name;
        $ticket_task_type->task_type_id = $req_task_type_id;
        $ticket_task_type->ticket_type_id = $request->get('ticket_type_id');
        $ticket_task_type->description = $request->get('description');
        try{
            $ticket_task_type->save();
            return ["success" => true, "message" => "Ticket Task Type berhasil ditambahkan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTicketTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $ticket_task_type = TicketTaskType::find($id);
        if($ticket_task_type === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        $req_task_type_id = $request->get('task_type_id');
        $req_name = $request->get('name');
        $check = TicketTaskType::where('task_type_id', $req_task_type_id)->where('name', $req_name)->first();
        if($check && $check->id !== $id) return ["success" => false, "message" => "Nama Tipe Tiket Task pada Tipe Task Sudah Ada", "status" => 400];
        
        $ticket_task_type->name = $req_name;
        $ticket_task_type->task_type_id = $req_task_type_id;
        $ticket_task_type->ticket_type_id = $request->get('ticket_type_id');
        $ticket_task_type->description = $request->get('description');
        try{
            $ticket_task_type->save();
            return ["success" => true, "message" => "Ticket Task Type berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTicketTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $ticket_task_type = TicketTaskType::find($id);
        if($ticket_task_type === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        try{
            $ticket_task_type->delete();
            return ["success" => true, "message" => "Ticket Task Type berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function clusteringNewTaskWorks($works)
    {
        $new_works = [];
        foreach($works as $work){
            if($work->type > 0 || $work->type < 7){
                if($work->type > 2){
                    if($work->type === 3){
                        $values = [];
                        foreach($work->details->lists as $list){
                            $values[] = false;
                        }
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "lists" => $work->details->lists, "values" => $values];
                    } else if($work->type === 4){
                        $is_general = $work->details->is_general;
                        $columns = $work->details->columns;
                        if($is_general) $rows = $work->details->rows;
                        else $rows = [];
                        $values = [];
                        foreach($columns as $column){
                            $value_column = [];
                            foreach($rows as $row){
                                $value_column[] = false;
                            }
                            $values[] = $value_column;
                        }
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "rows" => $rows, "columns" => $columns, "is_general" => $is_general, "values" => $values];
                    } else if($work->type === 5){
                        $lists = $work->details->lists;
                        foreach($lists as $list){
                            $list->values = "-";
                        }
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "lists" => $lists];
                    } else if($work->type === 6){
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "dropdown_name" => $work->details->dropdown_name, "lists" => $work->details->lists, "values" => '-'];
                    } 
                } else {
                    $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, 'values' => '-'];
                }
                $new_works[] = new TaskDetail([
                    "component" => $component
                ]);
            } 
        }
        return $new_works;
    }
    
    private function addTask($ticket_task_type, $location_id, $created_by)
    {
        try{
            $task = new Task;
            $task->name = $ticket_task_type->ticketType->name.'-'.$ticket_task_type->name;
            $task->description = "-";
            $task->task_type_id = $ticket_task_type->task_type_id;
            $task->location_id = $location_id;
            $task->reference_id = null;
            $task->created_by = $created_by;
            $task->deadline = null;
            $task->first_deadline = null;
            $task->created_at = date("Y-m-d H:i:s");
            $task->group_id = null;
            $task->is_replaceable = true;
            $task->is_uploadable = true;
            $task->end_repeat_at = null;
            $task->repeat = null;
            $task->is_from_ticket = true;
            $task->files = [];
            $task->is_visible = true;
            $task->status = 2;
            
            $task->save();
            if(count($ticket_task_type->taskType->works)){
                $new_works = $this->clusteringNewTaskWorks($ticket_task_type->taskType->works);
                $task->taskDetails()->saveMany($new_works);
            }
            return ["success" => true, "message" => "Task Berhasil Dibuat","task" => $task, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function updateTask($task, $created_at, $location_id, $created_by, $id)
    {
        try{
            $old_created_at = $task->created_at;
            $task->location_id = $location_id;
            $task->created_by = $created_by;
            $task->created_at = $created_at;
            $task->save();

            if($old_created_at !== $task->created_at){
                $logService = new LogService;
                $logService->updateRaisedAtLogTicket($id, $task->created_at);
            } 
            return ["success" => true, "message" => "Task Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function diffForHuman($times){
        // 60 - minute
        // 3600 - hour
        // 86400 - day
        // 2592000 - month
        if($times === null) return "-";
        else if($times > 2591999) {
            $months = floor($times / 2592000);
            $remainder = $times % 2592000;
            if($remainder === 0) return "$months Bulan";
            if($remainder > 86399){
                $days = floor($remainder / 86400);
                return "$months Bulan $days Hari";
            } else if($remainder > 3599){
                $hours = floor($remainder / 3600);
                return "$months Bulan $hours Jam";
            } else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$months Bulan $minutes Menit";
            } else return "$months Bulan $remainder Detik";
        } else if($times > 86399) {
            $days = floor($times / 86400);
            $remainder = $times % 86400;
            if($remainder === 0) return "$days Hari";
            else if($remainder > 3599){
                $hours = floor($remainder / 3600);
                return "$days Hari $hours Jam";
            } else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$days Hari $minutes Menit";
            } else return "$days Hari $remainder Detik";
        } else if($times > 3599) {
            $hours = floor($times / 3600);
            $remainder = $times % 3600;
            if($remainder === 0) return "$hours Jam";
            else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$hours Jam $minutes Menit";
            } else return "$hours Jam $remainder Detik";
        } else if($times > 59) {
            $minutes = floor($times / 60);
            $remainder = $times % 60;
            if($remainder === 0) return "$minutes Menit";
            else return "$minutes Menit $remainder Detik";
        } else return "$times Detik";
    }

    private function approximate_deadline($deadline)
    {
        if($deadline === null) return "-";
        $approximate_start = date("Y F d", strtotime($deadline));
        $splits_start = explode(" ", $approximate_start); 
        $approximate_end = date("Y F d", strtotime($deadline) + 172800);
        $splits_end = explode(" ", $approximate_end); 
        if($splits_start[1] !== $splits_end[1]){
            if($splits_start[0] !== $splits_end[0]) return $splits_start[2].' '.$splits_start[1].' '.$splits_start[0].' - '.$splits_end[2].' '.$splits_end[1].' '.$splits_end[0];
            else return $splits_start[2].' '.$splits_start[1].' - '.$splits_end[2].' '.$splits_end[1].' '.$splits_end[0];
        } else return $splits_start[2].' - '.$splits_end[2].' '.$splits_end[1].' '.$splits_end[0];
    }
}