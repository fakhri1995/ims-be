<?php

namespace App\Services;

use App\Task;
use App\User;
use App\Group;
use App\Company;
use App\TaskType;
use App\Inventory;
use App\TaskDetail;
use App\TaskTypeWork;
use App\LongLatList;
use App\Services\LogService;
use App\Services\FileService;
use App\Services\CompanyService;
use App\Services\GlobalService;
use App\Services\NotificationService;
use App\TaskReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;

class TaskService{

    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

        // Single Textbox
        // Paragraf
        // Checkbox
        // Matrix checkbox
        // Number
        // Dropdown

    // Task

    private function clusteringNewTaskWorks($works, $inventory_ids)
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
                        else {
                            $inventories = Inventory::with('modelInventory:id,name')->select('id','model_id')->find($inventory_ids);
                            $rows = [];
                            foreach($inventories as $inventory) $rows[] = $inventory->modelInventory->name;
                        }
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

    public function getStatusTaskList($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $status_list_name = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled', 'Rejected'];
        try{
            $location = $request->get('location', null);
            $from = $request->get('from', null);
            $to = $request->get('to', null);
            $status_tasks = Task::select(DB::raw('status, count(*) as status_count'));
            if($location) $status_tasks = $status_tasks->where('location_id', $location);
            if($from && $to) $status_tasks = $status_tasks->whereBetween('deadline', [$from, $to]);
            $status_tasks = $status_tasks->groupBy('tasks.status')->get();
            $sum_task = $status_tasks->sum('status_count');
            $list = [];
            for($i = 1; $i < 7; $i++){
                $search = $status_tasks->search(function($query) use($i){
                    return $query->status == $i;
                });
                if($search !== false){
                    $temp_list = $status_tasks[$search];
                    $temp_list->status_name = $status_list_name[$i];
                    $temp_list->percentage = $sum_task !== 0 ? round(($status_tasks[$search]->status_count / $sum_task * 100), 2) : 0;
                    $list[] = $temp_list;
                } else {
                    $list[] = (object)["status" => $i, "status_count" => 0, "status_name" => $status_list_name[$i], "percentage" => 0];
                }
            }
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $list, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskTypeCounts($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $location = $request->get('location', null);
            $task_type_counts = TaskType::select('id', 'name');
            if($location){
                $task_type_counts = $task_type_counts->withCount(['tasks' => function($query) use($location){
                    $query->where('location_id', $location);
                }]);
            } else $task_type_counts = $task_type_counts->withCount('tasks');
            $task_type_counts = $task_type_counts->orderBy('tasks_count', 'desc')->limit(4)->get();
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $task_type_counts, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getDeadlineTasks($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $location = $request->get('location', null);

            $from = $request->get('from', date('Y-m-01'));
            $to = $request->get('to', date("Y-m-t"));
            $from_strtotime = strtotime($from);
            $check = strtotime($to) - $from_strtotime;
            if($check < 518400) return ["success" => false, "message" => "Range Minimal Filter Deadline 6 Hari!", "status" => 400];
            $total = $check / 86400;
            $range = $total / 3;
            $mod = $total % 3;

            if($mod === 2) $second_addition = 1;
            else if($mod === 1) $second_addition = 0;
            else $second_addition = 0;

            $each = floor($range);
            $first_interval = $each;
            $second_interval = $each * 2 + $second_addition;

            $today = date('Y-m-d');
            $tomorrow = date("Y-m-d", strtotime('+1 day'));

            $first_start_date = $from;
            $first_end_date = date("Y-m-d", $from_strtotime + $first_interval * 86400);
            $second_start_date = date("Y-m-d", $from_strtotime + ($first_interval + 1) * 86400);
            $second_end_date = date("Y-m-d", $from_strtotime + $second_interval * 86400);
            $third_start_date = date("Y-m-d", $from_strtotime + ($second_interval + 1) * 86400);
            $third_end_date = $to;

            if($location){
                $today_deadline = Task::where('location_id', $location)->whereDate('deadline', $today)->count();
                $tomorrow_deadline = Task::where('location_id', $location)->whereDate('deadline', $tomorrow)->count();
                $first_range_deadline = Task::where('location_id', $location)->whereBetween('deadline', [$first_start_date, $second_start_date])->count();
                $second_range_deadline = Task::where('location_id', $location)->whereBetween('deadline', [$second_start_date, $third_start_date])->count();
                $third_range_deadline = Task::where('location_id', $location)->whereBetween('deadline', [$third_start_date, $third_end_date])->count();
            } else {
                $today_deadline = Task::whereDate('deadline', $today)->count();
                $tomorrow_deadline = Task::whereDate('deadline', $tomorrow)->count();
                $first_range_deadline = Task::whereBetween('deadline', [$first_start_date, $second_start_date])->count();
                $second_range_deadline = Task::whereBetween('deadline', [$second_start_date, $third_start_date])->count();
                $third_range_deadline = Task::whereBetween('deadline', [$third_start_date, $third_end_date])->count();
            }

            $data = (object)[
                "deadline" => (object)[
                    "today_deadline" => $today_deadline,
                    "tomorrow_deadline" => $tomorrow_deadline,
                    "first_range_deadline" => $first_range_deadline,
                    "second_range_deadline" => $second_range_deadline,
                    "third_range_deadline" => $third_range_deadline,
                ],
                "date" => (object)[
                    "first_start_date" => $first_start_date,
                    "first_end_date" => $first_end_date,
                    "second_start_date" => $second_start_date,
                    "second_end_date" => $second_end_date,
                    "third_start_date" => $third_start_date,
                    "third_end_date" => $third_end_date,
                ]
            ];
            return ["success" => true, "message" => "Data Deadline Task Berhasil Diambil", "data" => $data, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskStaffCounts($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $total_staff = User::where('role', 1)->count();
            $total_staff_without_task = User::where('role', 1)->whereDoesntHave('tasks', function($query){
                $query->whereNotIn('status', [5,6]);
            })->count();
            $data = (object)[
                "total_staff" => $total_staff,
                "total_staff_without_task" => $total_staff_without_task,
                "percentage" => round($total_staff_without_task / $total_staff * 100, 2)
            ];
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $data, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskSparePartList($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $type = $request->get('type', 'keluar');
            $id = $request->get('id', null);
            if($type == 'keluar'){
                $task = Task::with(['inventories.modelInventory.asset', 'inventories.inventoryParts', 'inventories' => function ($query) {
                    $query->wherePivot('is_from_task', true);
                }])->find($id);
                if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
                $task_inventory_out = Task::with(['inventories.modelInventory.asset', 'inventories' => function ($query) {
                    $query->wherePivot('is_in', false);
                }])->find($id);

                $list_inventories = [];
                foreach($task_inventory_out->inventories as $inventory){
                    $temp_list_inventories = $inventory->inventoryPartList()->toArray();
                    $inventory->makeHidden('inventoryPartName', 'vendor_id', 'status_condition', 'status_usage', 'location', 'deskripsi', 'manufacturer_id', 'deleted_at', 'is_consumable');
                    $temp_list_inventories[] = $inventory;
                    $list_inventories = array_merge($list_inventories,$temp_list_inventories);
                }

                $data = (object)[
                    "inventory_list" => $task->inventories,
                    "check_list" => $list_inventories
                ];
                return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $data, "status" => 200];
            } else {
                $keyword = $request->get('keyword', null);
                $data = Inventory::where('location', auth()->user()->company_id)->select('inventories.id', 'inventories.mig_id', 'inventories.model_id', 'inventories.location', 'model_inventories.name as model_name', 'assets.name as asset_name')
                ->join('model_inventories', 'inventories.model_id', '=', 'model_inventories.id')
                ->join('assets', 'model_inventories.asset_id', '=', 'assets.id');
                if($keyword !== null){
                    $data = $data->where(function ($query) use($keyword){
                        $query->where('mig_id', 'like', "%$keyword%")
                        ->orWhere('model_inventories.name', 'like', "%$keyword%")
                        ->orWhere('assets.name', 'like', "%$keyword%");
                    });
                }

                $task_inventory_in = Task::with(['inventories.modelInventory.asset', 'inventories' => function ($query) {
                    $query->wherePivot('is_in', true);
                }])->find($id);
                if($task_inventory_in === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
                $connect_ids = [];
                foreach($task_inventory_in->inventories as $inventory){
                    $connect_ids[] = $inventory->pivot->connect_id;
                }
                $inventories = Inventory::select('id', 'model_id', 'mig_id', 'serial_number')->with('modelInventory.asset')->find($connect_ids);
                // $data = $data->limit(50)->get();
                $data = (object)[
                    "inventory_list" => $data->limit(50)->get(),
                    "check_list" => $inventories
                ];
                return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $data, "status" => 200];
            }


        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTasks($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');
            $status = json_decode($request->get('status', "[]"));
            $location = $request->get('location', -1);
            $task_type = $request->get('task_type', -1);
            $from = $request->get('from', null);
            $to = $request->get('to', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;

            $tasks = Task::with(['taskType:id,name,deleted_at', 'location:id,name,parent_id,top_parent_id,role', 'users' => function($join){
                $join->addSelect("check_in_list.geo_location as geo_loc_check_in", "check_out_list.geo_location as geo_loc_check_out")
                ->leftjoin('long_lat_lists AS check_in_list', function ($join) {
                    $join->on('task_user.long_check_in', '=', 'check_in_list.longitude')->on('task_user.lat_check_in', '=', 'check_in_list.latitude');
                })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                    $join->on('task_user.long_check_out', '=', 'check_out_list.longitude')->on('task_user.lat_check_out', '=', 'check_out_list.latitude');
                });
            }, 'reference:id,ticketable_type,ticketable_id', 'reference.type:id,code,table_name', 'reference.ticketable']);


            if($location > 0){
                $company = Company::find($location);
                if(!$company) return ["success" => false, "message" => "Lokasi Tidak Ditemukan", "status" => 400];
                $companyService = new CompanyService;
                $company_list = $companyService->checkSubCompanyList($company);
                $tasks = $tasks->whereIn('location_id', $company_list);
            }
            if(count($status)) $tasks = $tasks->whereIn('status', $status);
            if($from && $to) $tasks = $tasks->whereBetween('deadline', [$from, $to]);
            if($keyword){
                if(is_numeric($keyword)){
                    $tasks = $tasks->where(function ($query) use ($keyword){
                        $query->where('name', 'like', "%".$keyword."%")->orWhere('id', $keyword);
                    });
                } else $tasks = $tasks->where('name', 'like', "%".$keyword."%");
            }
            if($task_type > 0) $tasks = $tasks->where('task_type_id', $task_type);

            if($sort_by){
                if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
                else if($sort_by === 'deadline') $tasks = $tasks->orderBy('deadline', $sort_type);
                else if($sort_by === 'id') $tasks = $tasks->orderBy('id', $sort_type);
                else if($sort_by === 'status') $tasks = $tasks->orderBy('status', $sort_type);
            }

            $tasks = $tasks->where('is_visible', true)->paginate($rows);
            foreach($tasks as $task){
                $task->location->full_location = $task->location->fullSubNameWParentTopParent();
                $task->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);

                foreach($task->users as $user){
                    $user->geo_loc_check_in = json_decode($user->geo_loc_check_in);
                    $user->geo_loc_check_out = json_decode($user->geo_loc_check_out);
                }

                $task->deadline_message = "";
                $date_now = date_create(date('Y-m-d'));
                $date_deadline = date_create(explode(" ",$task->deadline)[0]);
                if($task->deadline){
                    $deadline_detail = date_diff($date_now,$date_deadline);
                    if(date_format($date_now,"YmdHis") <= date_format($date_deadline,"YmdHis")){
                        if($deadline_detail->days == 0){
                            $task->deadline_message = "hari ini";
                        }else if($deadline_detail->days == 1){
                            $task->deadline_message = "besok";
                        }else if($deadline_detail->days < 7){
                            $task->deadline_message = $deadline_detail->days." hari Lagi";
                        }else{
                            $task->deadline_message = date_format(date_create($task->deadline),'d M Y');
                        }
                    }else{
                        $task->deadline_message = $deadline_detail->days." hari yang lalu";

                    }
                }

                try{
                    $task->reference->ticketable->location->full_location = $task->reference->ticketable->location->fullNameWParentTopParent();
                    $task->reference->ticketable->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);
                }catch(Exception $err){

                }
            }

            if($tasks->isEmpty()) return ["success" => true, "message" => "Task Masih Kosong", "data" => $tasks, "status" => 200];
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $tasks, "status" => 200];

            try{
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getUserLastTwoTasks($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;

            $task_ids = DB::table('task_user')->where('user_id', $login_id)->pluck('task_id');
            $tasks = Task::where(function ($query) use($login_id, $task_ids){
                $query->where('created_by', $login_id)
                ->orWhereIn('id', $task_ids);
            })->where('status', '<', 4)->whereNotNull('deadline')->orderBy('deadline', 'asc')->limit(2)->get();

            if(count($tasks)){
                foreach($tasks as $task){
                    if($task->deadline === null){
                        $task->time_left = "-";
                        $task->time_limit_percentage = 0;
                    } else {
                        $task->time_left = ucwords(Carbon::parse($task->deadline)->diffForHumans(null, true, false, 2));
                        $start_time = strtotime($task->created_at);
                        $deadline_time = strtotime($task->deadline);
                        $current_time = strtotime(date("Y-m-d H:i:s"));
                        $progress = $current_time - $start_time;
                        $limit = $deadline_time - $start_time;
                        $task->time_limit_percentage = !$limit ? 100 : ($progress / $limit * 100);
                    }
                }
            }
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $tasks, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getUserTasks($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');
            $status = json_decode($request->get('status', "[]"));
            $location = $request->get('location', -1);
            $task_type = $request->get('task_type', -1);
            $from = $request->get('from', null);
            $to = $request->get('to', null);
            $assigned_only = $request->get('assigned_only', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;

            $task_ids = DB::table('task_user')->where('user_id', $login_id)->pluck('task_id');
            $tasks = Task::with(['taskType:id,name,deleted_at', 'location:id,name,parent_id,top_parent_id,role', 'users', 'reference:id,ticketable_type,ticketable_id', 'reference.type:id,code,table_name']);
            if($assigned_only){
                $tasks = $tasks->whereIn('id', $task_ids);
            } else {
                $tasks = $tasks->where(function ($query) use($login_id, $task_ids){
                    $query->where('created_by', $login_id)
                    ->orWhereIn('id', $task_ids);
                });
            }



            if($location > 0){
                $company = Company::find($location);
                if(!$company) return ["success" => false, "message" => "Lokasi Tidak Ditemukan", "status" => 400];
                $companyService = new CompanyService;
                $company_list = $companyService->checkSubCompanyList($company);
                $tasks = $tasks->whereIn('location_id', $company_list);
            }
            if(count($status)) $tasks = $tasks->whereIn('status', $status);
            if($from && $to) $tasks = $tasks->whereBetween('deadline', [$from, $to]);
            if($keyword){
                if(is_numeric($keyword)){
                    $tasks = $tasks->where(function ($query) use ($keyword){
                        $query->where('name', 'like', "%".$keyword."%")->orWhere('id', $keyword);
                    });
                } else $tasks = $tasks->where('name', 'like', "%".$keyword."%");
            }
            if($task_type > 0) $tasks = $tasks->where('task_type_id', $task_type);

            if($sort_by){
                if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
                else if($sort_by === 'deadline') $tasks = $tasks->orderBy('deadline', $sort_type);
                else if($sort_by === 'id') $tasks = $tasks->orderBy('id', $sort_type);
                else if($sort_by === 'status') $tasks = $tasks->orderBy('status', $sort_type);
            }

            $tasks = $tasks->paginate($rows);
            foreach($tasks as $task){
                $task->location->full_location = $task->location->fullSubNameWParentTopParent();
                $task->location->makeHidden(['parent', 'parent_id', 'role', 'topParent']);

                $task->deadline_message = "";
                $date_now = date_create(date('Y-m-d'));
                $date_deadline = date_create(explode(" ",$task->deadline)[0]);
                if($task->deadline){
                    $deadline_detail = date_diff($date_now,$date_deadline);
                    if(date_format($date_now,"YmdHis") <= date_format($date_deadline,"YmdHis")){
                        if($deadline_detail->days == 0){
                            $task->deadline_message = "hari ini";
                        }else if($deadline_detail->days == 1){
                            $task->deadline_message = "besok";
                        }else if($deadline_detail->days < 7){
                            $task->deadline_message = $deadline_detail->days." hari Lagi";
                        }else{
                            $task->deadline_message = date_format(date_create($task->deadline),'d M Y');
                        }
                    }else{
                        $task->deadline_message = $deadline_detail->days." hari yang lalu";

                    }
                }
            }
            if($tasks->isEmpty()) return ["success" => true, "message" => "Task Masih Kosong", "data" => $tasks, "status" => 200];
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $tasks, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getStaffTaskStatuses($request, $route_name)
    {
        $name = $request->get('name', null);
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $rows = $request->get('rows', 10);

        $users = DB::table('users')->select('id', 'name')->where('role', 1);
        if($name) $users = $users->where('name', 'like', "%$name%");
        if($rows > 100) $rows = 100;
        if($rows < 1) $rows = 10;
        $users = $users->paginate($rows);
        // $from = "2021-10-30 20:49:53";
        // $to = "2021-12-20 20:49:53";
        $user_ids = $users->pluck('id');
        $user_tasks = DB::table('users')->whereIn('users.id', $user_ids)
        ->select(DB::raw('users.id, tasks.status, count(*) as status_count'))
        ->join('task_user', 'users.id', '=', 'task_user.user_id')
        ->join('tasks', 'task_user.task_id', '=', 'tasks.id');
        if($from && $to) $user_tasks = $user_tasks->whereBetween('tasks.created_at', [$from, $to]);
        $user_tasks = $user_tasks->groupBy('users.id','tasks.status')->get()->groupBy('id');
        $status_list_name = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled', 'Rejected'];
        foreach($users as $user){
            $status_list = $user_tasks[$user->id] ?? collect([(object)["id" => $user->id, "status" => 1, "status_count" => 0]]);
            $list = new Collection();
            for($i = 1; $i < 7; $i++){
                $search = $status_list->search(function($query) use($i){
                    return $query->status == $i;
                });
                if($search !== false){
                    $temp_list = $status_list[$search];
                    $temp_list->status_name = $status_list_name[$i];
                    $list->push($temp_list);
                } else {
                    $list->push((object)["id" => $user->id, "status" => $i, "status_count" => 0, "status_name" => $status_list_name[$i]]);
                }
            }
            $user->status_list = $list;
            $user->sum_task = $status_list->sum('status_count');
        }
        return ["success" => true, "data" => $users, "status" => 200];
    }

    public function getUserTaskStatusList($request, $route_name)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $assigned_only = $request->get('assigned_only', null);
        $location = $request->get('location', null);

        $login_id = auth()->user()->id;
        $task_ids = DB::table('task_user')->where('user_id', $login_id)->pluck('task_id');

        if($assigned_only) $status_list = DB::table('tasks')->whereIn('id', $task_ids);
        else {
            $status_list = DB::table('tasks')->where(function($query) use ($login_id, $task_ids) {
                $query->where('created_by', $login_id)->orWhereIn('id', $task_ids);
            });
        }

        if($location) $status_list = $status_list->where('location_id', $location);
        if($from && $to) $status_list = $status_list->whereBetween('deadline', [$from, $to]);

        $status_list = $status_list->select(DB::raw('status, count(*) as status_count'))->groupBy('status')->get();
        $status_list_name = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled', 'Rejected'];

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
        $data = (object)[
            "image_profile" => auth()->user()->profileImage,
            "name" => auth()->user()->name,
            "status_list" => $list,
            "sum_task" => $sum_task,
            "active_task" => $active_task,
        ];

        return ["success" => true, "data" => $data, "status" => 200];
    }

    public function getUserTaskTypeCounts($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $login_id = auth()->user()->id;
            $location = $request->get('location', null);
            if($location){
                $task_type_counts = TaskType::select('task_types.id', 'name')->withCount(['tasks' => function ($query) use($login_id){
                    $query->where('location_id', $login_id)->where(function ($q) use($login_id){
                        $q->where('created_by', $login_id)->orWhereHas('users', function ($q1) use($login_id){
                            $q1->where('users.id', $login_id);
                        });
                    } );
                }]);
            } else {
                $task_type_counts = TaskType::select('task_types.id', 'name')->withCount(['tasks' => function ($query) use($login_id){
                    $query->where(function ($q) use($login_id){
                        $q->where('created_by', $login_id)->orWhereHas('users', function ($q1) use($login_id){
                            $q1->where('users.id', $login_id);
                        });
                    } );
                }]);
            }
            $task_type_counts = $task_type_counts->orderBy('tasks_count', 'desc')->limit(4)->get();
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $task_type_counts, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskPickList($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');
            $location = $request->get('location', -1);
            $task_type = $request->get('task_type', -1);
            $from = $request->get('from', null);
            $to = $request->get('to', null);
            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;

            $tasks = Task::with(['taskType:id,name,deleted_at', 'location:id,name,parent_id,top_parent_id,role'])->where('status', 2)->doesntHave('users');

            if($location > 0){
                $company = Company::find($location);
                if(!$company) return ["success" => false, "message" => "Lokasi Tidak Ditemukan", "status" => 400];
                $companyService = new CompanyService;
                $company_list = $companyService->checkSubCompanyList($company);
                $tasks = $tasks->whereIn('location_id', $company_list);
            }
            if($from && $to) $tasks = $tasks->whereBetween('deadline', [$from, $to]);
            if($keyword){
                if(is_numeric($keyword)) $tasks = $tasks->where('name', 'like', "%".$keyword."%")->orWhere('id', $keyword);
                else $tasks = $tasks->where('name', 'like', "%".$keyword."%");
            }
            if($task_type > 0) $tasks = $tasks->where('task_type_id', $task_type);

            if($sort_by){
                if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
                else if($sort_by === 'deadline') $tasks = $tasks->orderBy('deadline', $sort_type);
                else if($sort_by === 'id') $tasks = $tasks->orderBy('id', $sort_type);
                else if($sort_by === 'description') $tasks = $tasks->orderBy('description', $sort_type);
            }

            $tasks = $tasks->paginate($rows);
            foreach($tasks as $task){
                $task->location->full_location = $task->location->fullSubNameWParentTopParent();
                $task->location->makeHidden(['parent', 'parent_id', 'role']);
            }
            if($tasks->isEmpty()) return ["success" => true, "message" => "Task Masih Kosong", "data" => $tasks, "status" => 200];
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $tasks, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->get('id', null);
            $task = Task::with(['attachments', 'taskType:id,name', 'creator:id,name,position', 'location:id,name,parent_id,top_parent_id,role','users' => function($join){
                $join->addSelect("check_in_list.geo_location as geo_loc_check_in", "check_out_list.geo_location as geo_loc_check_out")
                ->leftjoin('long_lat_lists AS check_in_list', function ($join) {
                    $join->on('task_user.long_check_in', '=', 'check_in_list.longitude')->on('task_user.lat_check_in', '=', 'check_in_list.latitude');
                })->leftJoin('long_lat_lists AS check_out_list', function ($join) {
                    $join->on('task_user.long_check_out', '=', 'check_out_list.longitude')->on('task_user.lat_check_out', '=', 'check_out_list.latitude');
                });
            } , 'group:id,name', 'inventories:id,model_id,mig_id','inventories.modelInventory.asset', 'taskDetails',
            'reference', 'reference.creator:id,name,company_id', 'reference.creator.company:id,name,top_parent_id',
            'reference.type', 'reference.ticketable', 'reference.ticketable.location', 'reference.ticketable.assetType',
            'reference.ticketable.inventory'])->find($id);


            if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $task->location->full_location = $task->location->fullSubNameWParentTopParent();
            $task->location->makeHidden(['parent', 'parent_id', 'role']);

            foreach($task->users as $user){
                $user->geo_loc_check_in = json_decode($user->geo_loc_check_in);
                $user->geo_loc_check_out = json_decode($user->geo_loc_check_out);
            }

            if(count($task->inventories)){
                foreach($task->inventories as $inventory){
                    $inventory->model_name = $inventory->modelInventory->name;
                    $inventory->asset_name = $inventory->modelInventory->asset->fullName();
                    $inventory->makeHidden('modelInventory');
                    $inventory->is_from_task = $inventory->pivot->is_from_task;
                    $inventory->is_in = $inventory->pivot->is_in;
                    $inventory->connect_id = $inventory->pivot->connect_id;
                }
            }
            $logService = new LogService;
            if($task->reference !== null){
                $task->reference->name = $task->reference->type->code.'-'.$task->reference->ticketable_id;
                $task->reference->creator->location = $task->reference->creator->location ?? $task->reference->creator->company->fullName();
                $task->reference->creator->company->makeHidden('topParent');
                $task->reference->resolved_times = $this->globalService->diffForHuman($task->reference->resolved_times);
                $task->reference->deadline = $task->reference->deadline ? date("d M Y, H:i", strtotime($task->reference->deadline)) : "-";
                $statuses = ['-','Overdue', 'Open', 'On progress', 'On hold', 'Completed', 'Closed', 'Canceled', 'Rejected'];
                $task->reference->status_name = $statuses[$task->reference->status];
                $task->reference->ticketable->asset_type_name = $task->reference->ticketable->assetType->name ?? "-";
                $task->reference->ticketable->original_incident_time = date("Y-m-d H:i:s" ,strtotime($task->reference->ticketable->incident_time));
                $task->reference->ticketable->incident_time = date("d F Y - H:i:s" ,strtotime($task->reference->ticketable->incident_time));
                $task->reference->ticketable->location->full_location = $task->reference->ticketable->location->fullSubNameWParentTopParent();
                $task->reference->ticketable->location->makeHidden(['parent', 'topParent']);
                // $task->reference->makeHidden('type');
                if($task->reference->ticketable_type === 'App\Incident'){
                    if($task->reference->ticketable->inventory !== null){
                        if($task->reference->ticketable->inventory->modelInventory->id !== 0 || $task->reference->ticketable->inventory->modelInventory->asset->id !== 0){
                            $task->reference->ticketable->inventory->modelInventory->asset->full_name = $task->reference->ticketable->inventory->modelInventory->asset->fullName();
                        }
                    }
                }
                $task->reference->name = $task->reference->type->code.'-'.sprintf('%03d', $task->reference->ticketable_id);
                $task->reference->logs = $logService->getTicketLogFunction($task->reference->id);
            }
            $task->logs = $logService->getLogTaskFunction($id);

            if($task->status === 4) $task->time_left = date_diff(date_create($task->deadline), date_create($task->on_hold_at));
            else $task->time_left = date_diff(date_create($task->deadline), date_create(date("Y-m-d H:i:s")));

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $task, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function deleteNotification($description, $notificationable_id, $notificationable_type)
    {
        $notification_service = new NotificationService;
        $notification_service->deleteNotification($description, $notificationable_id, $notificationable_type);
    }

    private function addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users)
    {
        $notification_service = new NotificationService;
        $created_by = auth()->user()->id;
        $notification_service->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, $created_by);
    }

    private function listParentInventory($list, $inventory)
    {
        if(count($inventory->inventoryParents)){
            $list = $this->listParentInventory($list, $inventory->inventoryParents[0]);
            $list[] = $inventory->id;
            return $list;
        }
        $list[] = $inventory->id;
        return $list;
    }

    private function checkInventoryIdsNeccesity($inventory_ids)
    {
        if(count($inventory_ids)){
            $inventories = Inventory::with('inventoryParents')->select('id', 'model_id')->find($inventory_ids);
            $check_inventory_ids = $inventories->pluck('id')->toArray();
            $check_list_remaining = array_diff($inventory_ids, $check_inventory_ids);
            if(count($check_list_remaining)) return ["success" => false, "message" => "ID Inventory Tidak Ditemukan", "list_not_found_ids" => array_values($check_list_remaining), "status" => 400];
            foreach($inventories as $inventory){
                $list_parent_ids = $this->listParentInventory([], $inventory);
                // Excluding its own id
                unset($list_parent_ids[count($list_parent_ids) - 1]);

                // Compare list parent with list inventory_ids whether its redundance or not
                $parent_inventory_id_comparation = array_intersect($list_parent_ids, $inventory_ids);
                if($parent_inventory_id_comparation){
                    $failed_inventory_id = $inventory->id;
                    $failed_parent_inventory_id = $parent_inventory_id_comparation[0];
                    return ["success" => false, "message" => "ID Inventory $failed_inventory_id adalah part dari Inventory dengan id $failed_parent_inventory_id", "status" => 400];
                }
            }
        }
        return ["success" => true];
    }

    public function addTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $is_group = $request->get('is_group', true);
        $assign_ids = $request->get('assign_ids', []);
        $inventory_ids = $request->get('inventory_ids', []);
        $task_type_id = $request->get('task_type_id');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'task_type_id' => 'required|numeric',
            'location_id' => 'nullable|numeric',
            'reference_id' => 'nullable|numeric',
            'created_at' => 'required|date',
            'deadline' => 'nullable|date',
            'is_group' => 'nullable|boolean',
            'is_replaceable' => 'required|boolean',
            'assign_ids' => 'nullable|array',
            'inventory_ids' => 'nullable|array',
            'repeat' => 'required|numeric',
            'is_uploadable' => 'required|boolean',
            'end_repeat_at' => 'nullable|date',
        ]);
        if($validator->fails()) return ["success" => false, "message" => "Gagal menyimpan model","errors" => $validator->errors(), "status" => 400];


        try{
            $task_type = TaskType::with('works')->find($task_type_id);
            if($task_type === null) return ["success" => false, "message" => "Id Tipe Task Tidak Ditemukan", "status" => 400];
            $check_inventory_ids_neccesity = $this->checkInventoryIdsNeccesity($inventory_ids);
            if(!$check_inventory_ids_neccesity["success"]) return $check_inventory_ids_neccesity;

            $task = new Task;
            if($is_group){
                if(count($assign_ids)){
                    $task->group_id = $assign_ids[0];
                    $group = Group::with('users')->find($assign_ids[0]);
                    if($group === null) return ["success" => false, "message" => "Id Group Tidak Ditemukan", "status" => 400];
                }
                else $task->group_id = null;
            }
            $created_by = auth()->user()->id;
            $task->name = $request->get('name');
            $task->description = $request->get('description');
            $task->task_type_id = $task_type_id;
            $task->location_id = $request->get('location_id');
            $task->reference_id = $request->get('reference_id');
            $task->created_by = $created_by;
            $task->deadline = $request->get('deadline');
            $task->first_deadline = $request->get('deadline');
            $task->created_at = $request->get('created_at');
            $task->is_replaceable = $request->get('is_replaceable', false);
            $task->is_uploadable = $request->get('is_uploadable', false);
            $task->end_repeat_at = $request->get('end_repeat_at');
            $task->repeat = $request->get('repeat', 0);
            $task->is_from_ticket = false;
            $task->is_visible = true;
            $task->status = 2;


            $task->save();

            if(count($task_type->works)){
                $new_works = $this->clusteringNewTaskWorks($task_type->works, $inventory_ids);
                $task->taskDetails()->saveMany($new_works);
            }
            $assign_id_count = count($assign_ids);
            if($assign_id_count){
                if($is_group) $assign_ids = $group->users->pluck('id');
                else {
                    if($assign_id_count === 1){
                        foreach($task->taskDetails as $taskDetail){
                            $taskDetail->users()->attach($assign_ids);
                        }
                    }
                }
                $task->users()->attach($assign_ids);
            }

            $assign_ids[] = $created_by;
            $description = "Task Baru Telah Terbuat";
            $link = env('APP_URL_WEB')."/task/".$task->id;
            $image_type = "task";
            $color_type = "green";
            $need_push_notification = false;
            $notificationable_id = $task->id;
            $notificationable_type = 'App\Task';
            $users = $assign_ids;
            $this->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users);

            if(count($inventory_ids)){
                $attach_inventories = [];
                foreach($inventory_ids as $inventory_id){
                    $attach_inventories[$inventory_id] = ['is_from_task' => true];
                }
                $task->inventories()->attach($attach_inventories);
            }
            return ["success" => true, "message" => "Task Berhasil Dibuat","id" => $task->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function updateNotification($notificationable_id, $notificationable_type, $users)
    {
        $notification_service = new NotificationService;
        $notification_service->updateNotification($notificationable_id, $notificationable_type, $users);
    }

    public function updateTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $assign_ids = $request->get('assign_ids', []);
        $deadline = $request->get('deadline');
        $task = Task::with(['taskDetails','inventories' => function ($query) {
            $query->wherePivot('is_from_task', true);
        }])->find($id);
        if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required',
            'location_id' => 'required|numeric',
            'reference_id' => 'nullable|numeric',
            'created_at' => 'required|date',
            'deadline' => 'nullable|date',
            'is_group' => 'nullable|boolean',
            'is_replaceable' => 'required|boolean',
            'assign_ids' => 'nullable|array',
            'inventory_ids' => 'nullable|array',
            'repeat' => 'required|numeric',
            'is_uploadable' => 'required|boolean',
            'end_repeat_at' => 'nullable|date',
        ]);
        if($validator->fails()) return ["success" => false, "message" => "Gagal menyimpan model","errors" => $validator->errors(), "status" => 400];

        $old_inventory_ids = [];
        if(count($task->inventories)) $old_inventory_ids = $task->inventories->pluck('id')->toArray();
        $is_group = $request->get('is_group', null);
        if($is_group === null) return ["success" => false, "message" => "Kolom Is Group Belum Diisi", "status" => 400];

        $login_id = auth()->user()->id;
        if($task->created_by !== $login_id) return ["success" => false, "message" => "Anda Bukan Pembuat Task, Izin Update Tidak Dimiliki", "status" => 403];
        $inventory_ids = $request->get('inventory_ids', []);
        $check_inventory_ids_neccesity = $this->checkInventoryIdsNeccesity($inventory_ids);
        if(!$check_inventory_ids_neccesity["success"]) return $check_inventory_ids_neccesity;

        try{
            if($is_group){
                if(count($assign_ids)){
                    $old_group = $task->group_id;
                    $task->group_id = $assign_ids[0];
                    $group = Group::with('users')->find($assign_ids[0]);
                    if($group === null) return ["success" => false, "message" => "Id Group Tidak Ditemukan", "status" => 400];
                }
            } else $task->group_id = null;

            $task->name = $request->get('name');
            $task->description = $request->get('description');
            $task->location_id = $request->get('location_id');
            $task->reference_id = $request->get('reference_id');
            $task->deadline = $deadline;
            $task->created_at = $request->get('created_at');
            $task->is_replaceable = $request->get('is_replaceable');
            $task->is_uploadable = $request->get('is_uploadable', false);
            $task->end_repeat_at = $request->get('end_repeat_at');
            $task->repeat = $request->get('repeat', 0);

            if(date("Y-m-d H:i:s") < $deadline && $task->status === 1){
                $check_in = false;
                if(count($task->users)){
                    foreach($task->users as $user){
                        if($user->check_in !== null){
                            $check_in = true;
                            break;
                        }
                    }
                }
                if($check_in) $task->status = 3;
                else $task->status = 2;
            }

            $task->save();

            $assign_id_count = count($assign_ids);
            if($assign_id_count){
                if($is_group){
                    $assign_ids = $group->users->pluck('id');
                    // if($old_group !== $task->group_id) $task->users()->sync($group->users->pluck('id'));
                } else {
                    if($assign_id_count === 1){
                        foreach($task->taskDetails as $taskDetail){
                            $taskDetail->users()->sync($assign_ids);
                        }
                    }
                }
                $task->users()->sync($assign_ids);
            }

            if(!$is_group){
                $task->load('users');
                if(count($task->users)) $all_check_out = true;
                else $all_check_out = false;
                foreach($task->users as $user){
                    if($user->check_out === null){
                        $all_check_out = false;
                        break;
                    }
                }

                if($all_check_out){
                    if($task->status !== 5) $this->changeSubmitStatus($task);
                } else {
                    if($task->status === 5) $this->rollBackSubmitStatus($task);
                }
            }

            $assign_ids[] = $task->created_by;
            $notificationable_id = $task->id;
            $notificationable_type = 'App\Task';
            $users = $assign_ids;
            $this->updateNotification($notificationable_id, $notificationable_type, $users);

            $attach_inventories = [];
            foreach($inventory_ids as $inventory_id){
                $attach_inventories[$inventory_id] = ['is_from_task' => true];
            }
            $task->inventories()->sync($attach_inventories);

            if(count($task->taskDetails)){
                foreach($task->taskDetails as $task_detail){
                    if($task_detail->component->type === 4 && !$task_detail->component->is_general){
                        $for_news = array_values(array_diff($inventory_ids, $old_inventory_ids));
                        $for_deletes = array_values(array_diff($old_inventory_ids, $inventory_ids));

                        $rows = $task_detail->component->rows;
                        $values = $task_detail->component->values;
                        $for_delete_indexes = [];
                        foreach($for_deletes as $for_delete){
                            $inventory = Inventory::with('modelInventory')->find($for_delete);
                            $search = array_search($inventory->modelInventory->name, $rows);
                            if($search !== false){
                                $for_delete_indexes[] = $search;
                                unset($rows[$search]);
                            }
                        }
                        $rows = array_values($rows);
                        if(count($for_delete_indexes)){
                            foreach($for_delete_indexes as $for_delete_index){
                                foreach ($values as &$value){
                                    unset($value[$for_delete_index]);
                                }
                            }
                            foreach ($values as &$value) $value = array_values($value);
                        }
                        if(count($for_news)){
                            foreach($for_news as $for_new){
                                $inventory = Inventory::with('modelInventory')->find($for_new);
                                $rows[] = $inventory->modelInventory->name;
                                foreach($values as &$value) $value[] = false;
                            }
                        }
                        $component = (object)[
                            "name" => $task_detail->component->name,
                            "description" => $task_detail->component->description,
                            "type" => $task_detail->component->type,
                            "rows" => $rows,
                            "columns" => $task_detail->component->columns,
                            "is_general" => $task_detail->component->is_general,
                            "values" => $values,
                        ];
                        $task_detail->component = $component;
                        $task_detail->save();
                    }
                }
            }
            return ["success" => true, "message" => "Task Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function saveFilesTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task = Task::find($id);
        if($task === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $attachments = $request->file('attachments', []);
            if(!empty($attachments)){
                $fileService = new FileService;
                $table = 'App\Task';
                $description = 'task_attachment';
                $folder_detail = 'Tasks';
                $new_file_list = [];
                foreach($attachments as $file){
                    $add_file_response = $fileService->addFile($id, $file, $table, $description, $folder_detail, true);
                    $new_file_list[] = $add_file_response['new_data'];
                }
            }
            return ["success" => true, "message" => "Files Berhasil Diperbarui", "new_file_list" => $new_file_list, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteFileTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $task_id = $request->get('task_id', null);
            $id = $request->get('id', null);
            $task = Task::with('attachments')->find($task_id);
            if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
            $search = $task->attachments->search(function ($item) use ($id) {
                return $item->id == $id;
            });
            if($search === false) return ["success" => false, "message" => "File Bukan Milik Task", "status" => 400];
            $fileService = new FileService;
            $delete_file_response = $fileService->deleteFile($id);
            if($delete_file_response['success']) return ["success" => true, "message" => "Berhasil Menghapus File", "status" => 200];
            else return ["success" => false, "message" => $delete_file_response['message'], "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task = Task::find($id);
        if($task === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $task->status = 7;
            $task->save();
            $task->delete();
            $task->taskDetails()->delete();
            $task->users()->detach();
            $task->inventories()->detach();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeStatusToggle($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $notes = $request->get('notes', null);
        $task = Task::with('users')->find($id);
        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        if($task->deadline === null) return ["success" => false, "message" => "Deadline Task Masih Kosong, Deadline Harus Ditentukan Terlebih Dahulu", "status" => 400];
        try{
            $login_id = auth()->user()->id;
            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                $old_status = $task->status;
                if($task->status < 4){
                    $task->status = 4;
                    $task->on_hold_at = date('Y-m-d H:i:s');
                }
                else if($task->status === 4){
                    $new_deadline_times = strtotime($task->deadline) + strtotime(date('Y-m-d H:i:s')) - strtotime($task->on_hold_at);
                    $task->deadline = date("Y-m-d H:i:s", $new_deadline_times);
                    if($task->deadline < date("Y-m-d H:i:s")) $task->status = 1;
                    else {
                        $check_in = false;
                        if(count($task->users)){
                            foreach($task->users as $user){
                                if($user->check_in !== null){
                                    $check_in = true;
                                    break;
                                }
                            }
                        }
                        if($check_in) $task->status = 3;
                        else $task->status = 2;
                    }
                }
                $task->notes = $notes;
                $task->save();

                $logService = new LogService;
                if($task->is_from_ticket){
                    $logService->updateStatusLogTicket($task->reference_id, $login_id, $old_status, $task->status, $notes);
                }

                $logService = new LogService;
                $logProperties = [
                    "log_type" => "task_status",
                    "old_task_status_id" => $old_status,
                    "new_task_status_id" => $task->status
                ];
                $logNotes = $task->status == 4 ? $request->notes : NULL;
                $logService->addLogTask($task->id, $login_id, "Updated", $logProperties, $logNotes);


                return ["success" => true, "message" => "Berhasil Merubah Status Task", "status" => 200];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Task Ini.", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function rejectTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $notes = $request->get('notes', null);
        $task = Task::with('users')->find($id);
        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        try{
            $login_id = auth()->user()->id;
            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                $old_status = $task->status;
                $task->status = 8;
                $task->on_hold_at = date('Y-m-d H:i:s');
                $task->notes = $notes;
                $task->save();

                $logService = new LogService;
                if($task->is_from_ticket){
                    $logService->updateStatusLogTicket($task->reference_id, $login_id, $old_status, $task->status, $notes);
                }

                $logService = new LogService;
                $logProperties = [
                    "log_type" => "task_status",
                    "old_task_status_id" => $old_status,
                    "new_task_status_id" => $task->status
                ];
                $logNotes = $task->status == 4 ? $request->notes : NULL;
                $logService->addLogTask($task->id, $login_id, "Updated", $logProperties, $logNotes);


                return ["success" => true, "message" => "Berhasil Menolak Task", "status" => 200];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Task Ini.", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function addingLatLongList($latitude, $longitude){
        $lat = number_format($latitude, 4);
        $long = number_format($longitude, 4);

        $long_lat = LongLatList::where('longitude', $long)->where('latitude', $lat)->first();
        if(!$long_lat) $long_lat = LongLatList::create(['longitude' => $long, 'latitude' => $lat, 'attempts' => 0]);
    }

    public function changeAttendanceToggle($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $lat = $request->get('lat', null);
        $long = $request->get('long', null);
        $task = Task::with('users')->find($id);


        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        try{
            $login_id = auth()->user()->id;
            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                if($task->users[$search]->check_in === null){
                    $lat = number_format($lat, 4);
                    $long = number_format($long, 4);
                    $task->users()->updateExistingPivot($login_id, ['check_in' => date("Y-m-d H:i:s"), 'lat_check_in' => $lat, 'long_check_in' => $long]);
                    $long_lat = LongLatList::where('longitude', $long)->where('latitude', $lat)->first();
                    if(!$long_lat) $long_lat = LongLatList::create(['longitude' => $long, 'latitude' => $lat, 'attempts' => 0]);

                    if($task->status === 2){
                        $task->status = 3;
                        $task->save();
                        if($task->is_from_ticket){
                            $logService = new LogService;
                            $logService->updateStatusLogTicket($task->reference_id, $login_id, 2, 3);
                        }
                    }
                    $this->addingLatLongList($lat, $long);
                    return ["success" => true, "message" => "Berhasil Melakukan Check In", "status" => 200];
                }
                return ["success" => false, "message" => "Anda Sudah Melakukan Check In", "status" => 400];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Task Ini.", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function rollBackSubmitStatus($task){
        $task->status = 3;
        $task->save();
        $description = "Task Telah Disubmit";
        $notificationable_id = $task->id;
        $notificationable_type = 'App\Task';
        $this->deleteNotification($description, $notificationable_id, $notificationable_type);
    }

    private function changeSubmitStatus($task){
        // if($task->is_from_ticket){
        //     if(count($task->inventories)){
        //         foreach($task->inventories as $inventory_task){
        //             $user = DB::table('users')->select('id', 'company_id')->where('id', $inventory_task->pivot->user_id)->first();
        //             if($inventory_task->pivot->is_in) $this->addInventoryPart($inventory_task->pivot->connect_id, $inventory_task->pivot->inventory_id, $user->id, $task->location_id);
        //             else $this->removeInventoryPart($inventory_task->pivot->inventory_id, $user->id, $user->company_id);
        //         }
        //     }
        //     $current_timestamp = date("Y-m-d H:i:s");
        //     $task->reference->closed_at = $current_timestamp;
        //     $task->reference->resolved_times = strtotime($current_timestamp) - strtotime($task->created_at);
        //     $task->reference->save();
        //     $task->status = 6;

        //     $logService = new LogService;
        //     $logService->updateStatusLogTicket($task->reference_id, $login_id, 3, 6);
        // } else $task->status = 5;
        $task->status = 5;
        $task->save();

        $assign_ids = [$task->created_by];
        $description = "Task Telah Disubmit";
        $link = env('APP_URL_WEB')."/task/".$task->id;
        $image_type = "task";
        $color_type = "green";
        $need_push_notification = false;
        $notificationable_id = $task->id;
        $notificationable_type = 'App\Task';
        $users = $assign_ids;
        $this->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users);
    }

    public function submitTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $lat = $request->get('lat', null);
        $long = $request->get('long', null);
        $task = Task::with(['users', 'inventories' => function ($query) {
                $query->wherePivot('is_from_task', false);
            }])->find($id);
        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        try{
            $login_id = auth()->user()->id;
            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                if(!array_intersect([$task->status], [1,3])) return ["success" => false, "message" => "Status Bukan On Progress, Tidak Dapat Melakukan Submit", "status" => 400];
                else if($task->users[$search]->check_out !== null) return ["success" => false, "message" => "Anda Sudah Melakukan Submit", "status" => 400];
                else {
                    $task->users()->updateExistingPivot($login_id, ['check_out' => date("Y-m-d H:i:s"), 'lat_check_out' => $lat, 'long_check_out' => $long]);
                    $task->load('users');
                    $all_check_out = true;
                    foreach($task->users as $user){
                        if($user->check_out === null){
                            $all_check_out = false;
                            break;
                        }
                    }

                    if($all_check_out){
                        $this->changeSubmitStatus($task);
                    }
                }
                $this->addingLatLongList($lat, $long);
                return ["success" => true, "message" => "Berhasil Melakukan Submit Pada Task", "status" => 200];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Task Ini.", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function declineTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $notes = $request->get('notes', null);
        $task = Task::with('users')->find($id);
        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        try{
            $login_id = auth()->user()->id;

            if($task->created_by === $login_id){
                if($task->status !== 5) return ["success" => false, "message" => "Status Bukan Completed, Tidak Dapat Dilakukan Penolakan", "status" => 400];
                else {
                    foreach($task->users as $user) $task->users()->updateExistingPivot($user->id, ['check_out' => null, 'lat_check_out' => null, 'long_check_out' => null]);

                    $task->status = 3;
                    $task->notes = $notes;
                    $task->save();
                }

                $assign_ids = $task->users->pluck('id');
                $description = "Task Ditolak";
                $link = env('APP_URL_WEB')."/task/".$task->id;
                $image_type = "task";
                $color_type = "red";
                $need_push_notification = false;
                $notificationable_id = $task->id;
                $notificationable_type = 'App\Task';
                $users = $assign_ids;
                $this->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users);

                return ["success" => true, "message" => "Berhasil Melakukan Penolakan Pada Task", "status" => 200];
            } else return ["success" => false, "message" => "Anda Tidak Memiliki Izin Pada Task Ini", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function addChildInventoryPart($inventory, $causer_id, $status_usage, $status_condition, $location){
        $old_inventory = [];
        foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;
        $inventory->status_usage = $status_usage;
        $inventory->status_condition = $status_condition;
        $inventory->location = $location;
        $inventory->save();
        $properties = $this->checkUpdateProperties($old_inventory, $inventory);
        if($properties){
            $logService = new LogService;
            $notes = "Added as Parts with Its Parent";
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }

        if(count($inventory->inventoryPart)){
            foreach($inventory->inventoryPart as $temp_inventory){
                $this->addChildInventoryPart($temp_inventory, $causer_id, $status_usage, $status_condition);
            }
        }
    }

    private function addInventoryPart($parent_id, $inventory_id, $causer_id, $location){
        $notes = "Masuk Suku Cadang";
        if($parent_id !== 0){
            $parent_inventory = Inventory::with(['inventoryPart'])->select('id', 'status_usage')->find($parent_id);
            $old_inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');
            $parent_inventory->inventoryPart()->attach($inventory_id);
        }

        $inventory = Inventory::with('inventoryParent', 'inventoryPart')->find($inventory_id);
        if($inventory === null) return ["success" => false, "message" => "Id Inventory Tidak Terdaftar", "status" => 400];

        $old_inventory = [];
        foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;
        $inventory->status_condition = 1;
        $inventory->status_usage = 1;
        $inventory->location = $location;
        $inventory->save();

        $logService = new LogService;
        $properties = $this->checkUpdateProperties($old_inventory, $inventory);
        if($properties){
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }

        $check_parent_inventory_part = $inventory->inventoryParent;
        $properties = [];
        if(count($check_parent_inventory_part)){
            $parent_inventory_part = $check_parent_inventory_part[0];
            $properties['old']['list_parts'] = $parent_inventory_part->inventoryPart->pluck('id');
            $parent_inventory_part->inventoryPart()->detach($inventory_id);
            $parent_inventory_part->load('inventoryPart');
            $properties['attributes']['list_parts'] = $parent_inventory_part->inventoryPart->pluck('id');
            $logService->updateLogInventoryPivotParts($parent_inventory_part->id, $causer_id, $properties, "Digunakan Untuk Proses Masuk Suku Cadang Lain");

            $properties = [];
            $properties['old'] = ['parent_id' => $parent_inventory_part->id];
            if($parent_id !== 0) $properties['attributes'] = ['parent_id' => $parent_id];
            $logService->updateLogInventoryPivot($inventory_id, $causer_id, $properties, $notes);
        } else {
            if($parent_id !== 0){
                $properties['attributes'] = [
                    'parent_id' => $parent_id,
                    'child_id' => $inventory_id
                ];
                $logService->createLogInventoryPivot($inventory_id, $causer_id, $properties, $notes);
            }
        }

        if(count($inventory->inventoryPart)){
            foreach($inventory->inventoryPart as $temp_inventory){
                $this->addChildInventoryPart($temp_inventory, $causer_id, 1, 1, $location);
            }
        }

        if($parent_id !== 0){
            $parent_inventory = Inventory::with('inventoryPart')->select('id')->find($parent_id);
            $inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');

            $properties = [];
            $properties['old']['list_parts'] = $old_inventory_parent_list;
            $properties['attributes']['list_parts'] = $inventory_parent_list;
            $logService->updateLogInventoryPivotParts($parent_id, $causer_id, $properties, $notes);
        }
    }

    private function removeChildInventoryPart($inventory, $causer_id, $location, $status = null){
        $old_inventory = [];
        foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;

        $inventory->status_condition = 2;
        $inventory->status_usage = 3;
        $inventory->location = $location;
        $inventory->save();

        $logService = new LogService;
        $properties = $this->checkUpdateProperties($old_inventory, $inventory);
        if($properties){
            if($status === "delete inventory") $notes = "Parent Has Been Deleted";
            else $notes = "Removed as Parts with Its Parent";
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }

        if(count($inventory->inventoryPart)){
            foreach($inventory->inventoryPart as $temp_inventory){
                $this->removeChildInventoryPart($temp_inventory, $causer_id, $location, $status);
            }
        }
    }

    private function removeInventoryPart($inventory_id, $causer_id, $location)
    {
        $notes = "Keluar Suku Cadang";
        $inventory = Inventory::with('inventoryPart', 'inventoryParent')->find($inventory_id);
        $old_inventory = [];
        foreach($inventory->getAttributes() as $key => $value) $old_inventory[$key] = $value;

        $inventory->status_condition = 2;
        $inventory->status_usage = 3;
        $inventory->location = $location;
        $inventory->save();

        $logService = new LogService;
        if(count($inventory->inventoryParent)){
            $parent_id = $inventory->inventoryParent[0]->id;
            $parent_inventory = Inventory::with(['inventoryPart'])->select('id')->find($parent_id);
            $old_inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');
            $parent_inventory->inventoryPart()->detach($inventory_id);
            $properties['old'] = [
                'parent_id' => $parent_id,
                'child_id' => $inventory_id
            ];
            $logService->deleteLogInventoryPivot($inventory_id, $causer_id, $properties, $notes);
        }

        $properties = $this->checkUpdateProperties($old_inventory, $inventory);
        if($properties){
            $logService->updateLogInventory($inventory->id, $causer_id, $properties, $notes);
        }

        if(count($inventory->inventoryPart)){
            foreach($inventory->inventoryPart as $temp_inventory){
                $this->removeChildInventoryPart($temp_inventory, $causer_id, $location);
            }
        }

        if(count($inventory->inventoryParent)){
            $parent_inventory = Inventory::with('inventoryPart')->select('id')->find($parent_id);
            $inventory_parent_list = $parent_inventory->inventoryPart->pluck('id');

            $properties = [];
            $properties['old']['list_parts'] = $old_inventory_parent_list;
            $properties['attributes']['list_parts'] = $inventory_parent_list;
            $logService->updateLogInventoryPivotParts($parent_id, $causer_id, $properties, $notes);
        }

    }

    public function approveTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task = Task::with(['inventories' => function ($query) {
                $query->wherePivot('is_from_task', false);
            }])->find($id);
        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        try{
            $login_id = auth()->user()->id;

            if($task->created_by === $login_id){
                if($task->status === 6) return ["success" => false, "message" => "Task Sudah Disetujui", "status" => 400];
                if($task->status !== 5) return ["success" => false, "message" => "Status Bukan Completed, Tidak Dapat Dilakukan Persetujuan", "status" => 400];
                else {
                    $task->status = 6;
                    if(count($task->inventories)){
                        foreach($task->inventories as $inventory_task){
                            $user = DB::table('users')->select('id', 'company_id')->where('id', $inventory_task->pivot->user_id)->first();
                            if($inventory_task->pivot->is_in) $this->addInventoryPart($inventory_task->pivot->connect_id, $inventory_task->pivot->inventory_id, $user->id, $task->location_id);
                            else $this->removeInventoryPart($inventory_task->pivot->inventory_id, $user->id, $user->company_id);
                        }
                    }
                    $task->notes = null;
                    $task->save();
                }

                $assign_ids = $task->users->pluck('id');
                $description = "Task Telah Disetujui";
                $link = env('APP_URL_WEB')."/task/".$task->id;
                $image_type = "task";
                $color_type = "green";
                $need_push_notification = false;
                $notificationable_id = $task->id;
                $notificationable_type = 'App\Task';
                $users = $assign_ids;
                $this->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users);

                // Turn off one hour left deadline notification
                $task->need_one_hour_notification = false;
                $task->save();

                return ["success" => true, "message" => "Berhasil Melakukan Persetujuan Pada Task", "status" => 200];
            } else return ["success" => false, "message" => "Anda Tidak Memiliki Izin Pada Task Ini", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function assignSelfTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $login_id = auth()->user()->id;
        $id = $request->get('id', null);
        $task = Task::with('users', 'taskDetails')->find($id);
        if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
        if(count($task->users)) return ["success" => false, "message" => "Task Sudah Ditugaskan Pada User Lain", "status" => 400];
        try{
            $task->users()->attach($login_id);
            if(count($task->taskDetails)){
                foreach($task->taskDetails as $taskDetail){
                    $taskDetail->users()->attach($login_id);
                }
            }
            return ["success" => true, "message" => "Berhasil Mengambil Tugas Task", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function checkParent($id, $check_id){
        $inventory = Inventory::with('inventoryParent')->select('id')->find($id);
        if(!$inventory) return false;
        if(count($inventory->inventoryParent)){
            if($inventory->inventoryParent[0]->id === $check_id) return true;
            return $this->checkParent($inventory->inventoryParent[0]->id, $check_id);
        }
        return false;
    }

    public function checkUpdateProperties($old_inventory, $new_inventory)
    {
        $properties = false;
        foreach($new_inventory->getAttributes() as $key => $value){
            if($key === "created_at" || $key === "updated_at") continue;
            if($new_inventory->$key !== $old_inventory[$key]){
                $properties['old'][$key] = $old_inventory[$key];
                $properties['attributes'][$key] = $new_inventory->$key;
            }
        }
        return $properties;
    }

    public function sendInventoriesTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $login_id = auth()->user()->id;
        $id = $request->get('id', null);
        $add_in_inventories = $request->get('add_in_inventories', []);
        $remove_in_inventory_ids = $request->get('remove_in_inventory_ids', []);
        $add_out_inventory_ids = $request->get('add_out_inventory_ids', []);
        $remove_out_inventory_ids = $request->get('remove_out_inventory_ids', []);
        try{
            $task = Task::with(['users', 'inventories:id,model_id,mig_id'])->find($id);
            if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
            if(!count($task->users)) return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                if($task->users[$search]->check_in === null) return ["success" => false, "message" => "Anda Perlu Melakukan Check In Terlebih Dahulu ", "status" => 400];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            if(!array_intersect([$task->status], [1,3])) return ["success" => false, "message" => "Status Bukan On Progress", "status" => 400];

            $inventory_from_task_ids = [];
            $inventory_not_from_task_in_ids = [];
            $inventory_not_from_task_out_ids = [];
            $inventory_not_from_task_ins = [];
            if(count($task->inventories)){
                foreach($task->inventories as $inventory_task){
                    if($inventory_task->pivot->is_from_task) $inventory_from_task_ids[] = $inventory_task->id;
                    else {
                        if($inventory_task->pivot->is_in){
                            $inventory_not_from_task_in_ids[] = $inventory_task->id;
                            $inventory_not_from_task_ins[] = $inventory_task;
                        } else $inventory_not_from_task_out_ids[] = $inventory_task->id;
                    }

                    $inventory_task->is_from_task = $inventory_task->pivot->is_from_task;
                    $inventory_task->is_in = $inventory_task->pivot->is_in;
                }
            }

            $companyService = new CompanyService;
            $company_list = $companyService->checkSubCompanyList(auth()->user()->company);

            if(count($add_in_inventories)){
                $company_list = $company_list->toArray();
                foreach($add_in_inventories as $add_in_inventory){
                    $connect_id = $add_in_inventory['connect_id'];
                    if($connect_id === null) return ["success" => false, "message" => "Connect Id (Parent Id) Dengan Item Id $inventory_id Masih Kosong", "status" => 400];
                    $inventory_id = $add_in_inventory['inventory_id'];
                    $inventory = Inventory::find($inventory_id);
                    if($inventory === null) return ["success" => false, "message" => "Id Inventori Tidak Ditemukan", "status" => 400];
                    $check_add_in_inventory = array_intersect([$inventory->location], $company_list);
                    if(!count($check_add_in_inventory)) return ["success" => false, "message" => "Lokasi Item Dengan Item Id $inventory_id Tidak Di Perusahaan Anda", "status" => 400];
                    if($connect_id !== 0){
                        if(count($inventory_from_task_ids)){
                            foreach($inventory_from_task_ids as $inventory_from_task_id){
                                if($connect_id == $inventory_from_task_id){
                                    $check_connect_id = true;
                                    break;
                                }
                                $check_connect_id = $this->checkParent($connect_id, $inventory_from_task_id);
                                if($check_connect_id) break;
                            }
                            if(!$check_connect_id){
                                return ["success" => false, "message" => "Connect Id (Parent Id) Dengan Item Id $inventory_id Bukan Merupakan Part Dari Item Yang Terhubung Dengan Task", "status" => 400];
                            }
                        }
                    }
                    // Check whether add_in_inventory's parent is same with inventory or inventory part with add_out_inventory_id id
                    if(count($add_out_inventory_ids)){
                        foreach($add_out_inventory_ids as $add_out_inventory_id){
                            if($add_in_inventory['connect_id'] == $add_out_inventory_id){
                                return ["success" => false, "message" => "Connect Id (Parent Id) Dengan Item Id $inventory_id Sama Dengan Item Yang Akan Dikeluarkan Dengan Id $add_out_inventory_id", "status" => 400];
                            }
                            $check_connect_id = $this->checkParent($add_in_inventory['connect_id'], $add_out_inventory_id);
                            if($check_connect_id) break;
                        }
                        if($check_connect_id){
                            return ["success" => false, "message" => "Connect Id (Parent Id) Dengan Item Id $inventory_id Sama Dengan Part Item Yang Akan Dikeluarkan Dengan Id $add_out_inventory_id", "status" => 400];
                        }
                    }
                }
            }

            if(count($add_out_inventory_ids)){
                foreach($add_out_inventory_ids as $add_out_inventory_id){
                    foreach($inventory_from_task_ids as $inventory_from_task_id){
                        if($add_out_inventory_id == $inventory_from_task_id){
                            $check_add_out_inventory_id = true;
                            break;
                        }
                        $check_add_out_inventory_id = $this->checkParent($add_out_inventory_id, $inventory_from_task_id);
                        if($check_add_out_inventory_id) break;
                    }
                    if(!$check_add_out_inventory_id){
                        return ["success" => false, "message" => "Item Dengan Id $add_out_inventory_id Bukan Merupakan Part Dari Item Yang Terhubung Dengan Task", "status" => 400];
                    }
                }
            }

            if(count($remove_in_inventory_ids)){
                foreach($remove_in_inventory_ids as $remove_in_inventory_id){
                    $check_in_task = array_intersect([$remove_in_inventory_id], $inventory_from_task_ids);
                    if(count($check_in_task)) return ["success" => false, "message" => "Item Dengan Id $remove_in_inventory_id Merupakan Item Utama Pada Task dan Tidak Dapat Didelete", "status" => 400];
                    $check_not_in_task = array_intersect([$remove_in_inventory_id], $inventory_not_from_task_in_ids);
                    if(!count($check_not_in_task)) return ["success" => false, "message" => "Item Dengan Id $remove_in_inventory_id Bukan Merupakan Item Pada Task dan Tidak Dapat Didelete", "status" => 400];
                    $search = $task->inventories->search(function ($query) use ($remove_in_inventory_id) {
                        return $query->id === $remove_in_inventory_id;
                    });
                    if($task->inventories[$search]->is_in === false) return ["success" => false, "message" => "Item Dengan Id $remove_in_inventory_id Termasuk Suku Cadang Keluar", "status" => 400];
                }
            }

            if(count($remove_out_inventory_ids)){
                foreach($remove_out_inventory_ids as $remove_out_inventory_id){
                    $check_in_task = array_intersect([$remove_out_inventory_id], $inventory_from_task_ids);
                    if(count($check_in_task) && !count(array_keys($inventory_not_from_task_out_ids, $remove_out_inventory_id))) return ["success" => false, "message" => "Item Dengan Id $remove_out_inventory_id Merupakan Item Utama Pada Task dan Tidak Dapat Didelete", "status" => 400];
                    $check_not_in_task = array_intersect([$remove_out_inventory_id], $inventory_not_from_task_out_ids);
                    if(!count($check_not_in_task)) return ["success" => false, "message" => "Item Dengan Id $remove_out_inventory_id Bukan Merupakan Item Pada Task dan Tidak Dapat Didelete", "status" => 400];
                    $search = $task->inventories->search(function ($query) use ($remove_out_inventory_id) {
                        return $query->id === $remove_out_inventory_id;
                    });
                    if($task->inventories[$search]->is_in === true) return ["success" => false, "message" => "Item Dengan Id $remove_out_inventory_id Termasuk Suku Cadang Masuk", "status" => 400];
                }
            }

            foreach($remove_out_inventory_ids as $remove_out_inventory_id){
                $check_in_task = array_intersect([$remove_out_inventory_id], $inventory_from_task_ids);
                $task->inventories()->detach($remove_out_inventory_id);
                if(count($check_in_task)){
                    $task->inventories()->attach($remove_out_inventory_id, ['is_from_task' => true, 'is_in' => null]);
                }
            }
            foreach($remove_in_inventory_ids as $remove_in_inventory_id) $task->inventories()->detach($remove_in_inventory_id);
            foreach($add_out_inventory_ids as $add_out_inventory_id){
                $task->inventories()->attach($add_out_inventory_id, ['is_from_task' => false, 'is_in' => false, 'user_id' => $login_id]);
                // Check if add_out_inventory_ids are parent from inventory_not_from_task_ins
                // if yes, delete task inventory which have add_out_inventory_ids as their parent
                if(count($inventory_not_from_task_ins)){
                    foreach($inventory_not_from_task_ins as $inventory_not_from_task_in){
                        if($add_out_inventory_id == $inventory_not_from_task_in->pivot->connect_id){
                            $check_remove = true;
                            break;
                        }
                        $check_remove = $this->checkParent($inventory_not_from_task_in->pivot->connect_id, $add_out_inventory_id);
                        if($check_remove) break;
                    }
                    if($check_remove) $task->inventories()->detach($inventory_not_from_task_in->id);
                }
            }
            foreach($add_in_inventories as $add_in_inventory){
                $task->inventories()->syncWithoutDetaching([$add_in_inventory['inventory_id'] => ['is_from_task' => false, 'is_in' => true, 'user_id' => $login_id, 'connect_id' => $add_in_inventory['connect_id']]]);
                // Check if add_in_inventories's parent are from inventory_not_from_task_out_ids
                // if yes, delete task inventory which have add_out_inventory_ids as their children
                if(count($inventory_not_from_task_out_ids)){
                    foreach($inventory_not_from_task_out_ids as $inventory_not_from_task_out_id){
                        if($add_in_inventory['connect_id'] == $inventory_not_from_task_out_id){
                            $check_remove = true;
                            break;
                        }
                        $check_remove = $this->checkParent($add_in_inventory['connect_id'], $inventory_not_from_task_out_id);
                        if($check_remove) break;
                    }
                    if($check_remove){
                        $check_in_task = array_intersect([$inventory_not_from_task_out_id], $inventory_from_task_ids);
                        $task->inventories()->detach($inventory_not_from_task_out_id);
                        if(count($check_in_task)){
                            $task->inventories()->attach($inventory_not_from_task_out_id, ['is_from_task' => true, 'is_in' => null]);
                        }
                    }
                }
            }

            return ["success" => true, "message" => "Berhasil Memperbarui Suku Cadang Task", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function sendInInventoryTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $login_id = auth()->user()->id;
        $id = $request->get('id', null);
        $inventory_id = $request->get('inventory_id', null);
        $connect_id = $request->get('connect_id', null);
        try{
            if($connect_id === null) return ["success" => false, "message" => "Connect Id (Parent Id) Masih Kosong", "status" => 400];
            $task = Task::with(['users','location', 'inventories' => function ($query) {
                $query->wherePivot('is_from_task', true);
            }])->find($id);
            if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
            if(!count($task->users)) return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                if($task->users[$search]->check_in === null) return ["success" => false, "message" => "Anda Perlu Melakukan Check In Terlebih Dahulu ", "status" => 400];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            if(!array_intersect([$task->status], [1,3])) return ["success" => false, "message" => "Status Bukan On Progress", "status" => 400];

            $inventory = Inventory::find($inventory_id);
            if($inventory === null) return ["success" => false, "message" => "Id Inventori Tidak Ditemukan", "status" => 400];
            $inventory_from_task_ids = $task->inventories->pluck('id');
            if($connect_id !== 0){
                if(count($inventory_from_task_ids)){
                    foreach($inventory_from_task_ids as $inventory_from_task_id){
                        if($connect_id == $inventory_from_task_id){
                            $check_connect_id = true;
                            break;
                        }
                        $check_connect_id = $this->checkParent($connect_id, $inventory_from_task_id);
                        if($check_connect_id) break;
                    }
                    if(!$check_connect_id){
                        return ["success" => false, "message" => "Connect Id (Parent Id) Bukan Merupakan Part Dari Item Yang Terhubung Dengan Task", "status" => 400];
                    }
                }
            }

            $inventory_ids = [];
            if(count($task->inventories)){
                foreach($task->inventories as $inventory_task) $inventory_ids[] = $inventory_task->id;
            }

            $companyService = new CompanyService;
            $company_list = $companyService->checkSubCompanyList(auth()->user()->company);
            $check_inventory_location = array_intersect([$inventory->location], $company_list->toArray());
            if(!count($check_inventory_location)) return ["success" => false, "message" => "Lokasi Item Tidak Di Perusahaan Anda", "status" => 400];

            $task->inventories()->syncWithoutDetaching([$inventory_id => ['is_from_task' => false, 'is_in' => true, 'user_id' => auth()->user()->id, 'connect_id' => $connect_id]]);
            return ["success" => true, "message" => "Berhasil Menambah Item Pada Task", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function sendOutInventoryTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $login_id = auth()->user()->id;
        $id = $request->get('id', null);
        $inventory_id = $request->get('inventory_id', null);
        try{
            $task = Task::with(['users','inventories' => function ($query) {
                $query->wherePivot('is_from_task', true);
            }])->find($id);
            if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
            $inventory = Inventory::find($inventory_id);
            if($inventory === null) return ["success" => false, "message" => "Id Inventori Tidak Ditemukan", "status" => 400];
            if(!count($task->users)) return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                if($task->users[$search]->check_in === null) return ["success" => false, "message" => "Anda Perlu Melakukan Check In Terlebih Dahulu ", "status" => 400];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            if(!array_intersect([$task->status], [1,3])) return ["success" => false, "message" => "Status Bukan On Progress", "status" => 400];
            $inventory_from_task_ids = [];
            if(count($task->inventories)){
                foreach($task->inventories as $inventory_task) $inventory_from_task_ids[] = $inventory_task->id;
            } else return ["success" => false, "message" => "Task Tidak Memiliki Item Yang Terhubung", "status" => 400];

            foreach($inventory_from_task_ids as $inventory_from_task_id){
                if($inventory_id == $inventory_from_task_id){
                    $check_parent = true;
                    break;
                }
                $check_parent = $this->checkParent($inventory_id, $inventory_from_task_id);
                if($check_parent) break;
            }

            if(!$check_parent) return ["success" => false, "message" => "Item Bukan Merupakan Part Dari Item Yang Terhubung Dengan Task", "status" => 400];
            $task->inventories()->attach($inventory_id, ['is_from_task' => false, 'is_in' => false, 'user_id' => auth()->user()->id]);
            return ["success" => true, "message" => "Berhasil Mengeluarkan Item Dari Task", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function cancelSendInventoryTask($id, $inventory_id, $status, $notes)
    {
        try{
            $login_id = auth()->user()->id;
            $task = Task::with(['users','inventories:id,model_id,mig_id'])->find($id);
            if($task === null) return ["success" => false, "message" => "Id Task Tidak Ditemukan", "status" => 400];
            $inventory = Inventory::find($inventory_id);
            if($inventory === null) return ["success" => false, "message" => "Id Inventori Tidak Ditemukan", "status" => 400];
            if(!count($task->users)) return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            $search = $task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });

            if($search !== false){
                if($task->users[$search]->check_in === null) return ["success" => false, "message" => "Anda Perlu Melakukan Check In Terlebih Dahulu ", "status" => 400];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            if(!array_intersect([$task->status], [1,3])) return ["success" => false, "message" => "Status Bukan On Progress", "status" => 400];

            $inventory_from_task_ids = [];
            $inventory_not_from_task_ids = [];
            $inventory_target_in_task = null;
            if(count($task->inventories)){
                foreach($task->inventories as $inventory_task){
                    if($inventory_task->pivot->is_from_task) $inventory_from_task_ids[] = $inventory_task->id;
                    else $inventory_not_from_task_ids[] = $inventory_task->id;

                    if($inventory_task->id == $inventory_id){
                        $inventory_task->is_from_task = $inventory_task->pivot->is_from_task;
                        $inventory_task->is_in = $inventory_task->pivot->is_in;
                        $inventory_target_in_task = $inventory_task;
                    }
                }
            }

            $check_in_task = array_intersect([$inventory_id], $inventory_from_task_ids);
            if($status === "OUT"){
                if(count($check_in_task) && !count(array_keys($inventory_not_from_task_ids, $inventory_id))) return ["success" => false, "message" => "Item Merupakan Item Utama Pada Task dan Tidak Dapat Didelete", "status" => 400];
                $check_not_in_task = array_intersect([$inventory_id], $inventory_not_from_task_ids);
                if(!count($check_not_in_task)) return ["success" => false, "message" => "Item Bukan Merupakan Item Pada Task dan Tidak Dapat Didelete", "status" => 400];
                if($inventory_target_in_task->is_in === true) return ["success" => false, "message" => "Item Termasuk Suku Cadang Masuk", "status" => 400];
                $status_condition = 1;
                $status_usage = 1;
                $location = $task->location->id;
                $task->inventories()->detach($inventory_id);
                if(count($check_in_task)){
                    $task->inventories()->attach($inventory_id, ['is_from_task' => true, 'is_in' => null]);
                }
            } else {
                if(count($check_in_task)) return ["success" => false, "message" => "Item Merupakan Item Utama Pada Task dan Tidak Dapat Didelete", "status" => 400];
                $check_not_in_task = array_intersect([$inventory_id], $inventory_not_from_task_ids);
                if(!count($check_not_in_task)) return ["success" => false, "message" => "Item Bukan Merupakan Item Pada Task dan Tidak Dapat Didelete", "status" => 400];
                if($inventory_target_in_task->is_in === false) return ["success" => false, "message" => "Item Termasuk Suku Cadang Keluar", "status" => 400];
                $status_condition = 2;
                $status_usage = 2;
                $location = auth()->user()->company_id;
                $task->inventories()->detach($inventory_id);
            }
            return ["success" => true, "message" => "Berhasil Mengeluarkan Item Dari Task", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function cancelSendInInventoryTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $inventory_id = $request->get('inventory_id', null);
        $notes = "Diubah Melalui Ganti Suku Cadang Cancel Masuk Task";
        return $this->cancelSendInventoryTask($id, $inventory_id, "IN", $notes);
    }

    public function cancelSendOutInventoryTask($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $inventory_id = $request->get('inventory_id', null);
        $notes = "Diubah Melalui Ganti Suku Cadang Cancel Keluar Task";
        return $this->cancelSendInventoryTask($id, $inventory_id, "OUT", $notes);
    }

    // Task Detail

    private function checkAddTaskDetail($work, $task_id){
        if(!isset($work['name'])) return ["success" => false, "message" => "Nama Pekerjaan Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe Pekerjaan Masih Kosong", "status" => 400];
        $type = (int) $work['type'];

        if($type < 1 || $type > 6){
            return ["success" => false, "message" => "Tipe Pekerjaan Tidak Tepat", "status" => 400];
        } else {
            if($type > 2){
                if($type === 3){
                    if(isset($work['rows'])) $lists = $work['rows'];
                    else $lists = [];
                    if(!count($lists)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki List Checkbox", "status" => 400];
                    $details = (object)["lists" => $lists];
                    $values = [];
                    foreach($lists as $list) $values[] = false;
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "lists" => $lists, "values" => $values];
                } else if($type === 4){
                    if(isset($work['columns'])) $columns = $work['columns'];
                    else $columns = [];
                    if(!count($columns)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki Kolom", "status" => 400];
                    $is_general = filter_var($work['is_general'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    if($is_general){
                        if(isset($work['rows'])) $rows = $work['rows'];
                        else $rows = [];
                        if(!count($rows)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki Baris", "status" => 400];
                    } else {
                        $task = Task::with('inventories:id,model_id,mig_id')->find($task_id);
                        if(!$task) return ["success" => false, "message" => "Task Pada Pekerjaan Sudah Dihapus", "status" => 400];
                        $inventory_ids = $task->inventories->pluck('id') ?? [];
                        $inventories = Inventory::with('modelInventory:id,name')->select('id','model_id')->find($inventory_ids);
                        $rows = [];
                        if(count($inventories)){
                            foreach($inventories as $inventory) $rows[] = $inventory->modelInventory->name;
                        } else return ["success" => false, "message" => "Task Tidak Memiliki Aset Sehingga Baris Suku Cadang Masih Kosong", "status" => 400];
                    }
                    $values = [];
                    if(count($rows)){
                        foreach($columns as $column){
                            $value_column = [];
                            foreach($rows as $row){
                                $value_column[] = false;
                            }
                            $values[] = $value_column;
                        }
                    }
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "rows" => $rows, "columns" => $columns, "is_general" => $is_general, "values" => $values];
                } else if($type === 5){
                    if(isset($work['rows'])) $lists = $work['rows'];
                    else $lists = [];
                    if(!count($lists)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki List Numeral", "status" => 400];
                    $index_number = 1;
                    foreach($lists as $list){
                        if(!isset($list['type'])) return ["success" => false, "message" => "Pekerjaan Dengan List $index_number Belum Memiliki Tipe", "status" => 400];
                        if(!isset($list['description'])) return ["success" => false, "message" => "Pekerjaan Dengan List $index_number Belum Memiliki Keterangan", "status" => 400];
                    }
                    foreach($lists as &$list){
                        $list['values'] = "-";
                    }
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "lists" => $lists];
                } else {
                    if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "Pekerjaan Dengan List Belum Memiliki Nama Dropdown", "status" => 400];
                    if(isset($work['rows'])) $lists = $work['rows'];
                    else $lists = [];
                    if(!count($lists)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki List Dropdown", "status" => 400];
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "dropdown_name" => $work['dropdown_name'], "lists" => $lists, "values" => '-'];
                }
            } else {
                $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], 'values' => '-'];
            }
        }
        return ['success' => true, 'component' => $component];
    }

    public function addTaskDetail($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $task_id = $request->get('task_id');
            $work = $request->get('work', []);
            $task = Task::find($task_id);
            if(!$task) return ["success" => false, "message" => "Task Id Tidak Ditemukan", "status" => 400];
            if($task->created_by !== auth()->user()->id) return ["success" => false, "message" => "Anda Bukan Pembuat Task, Izin Tambah Task Detail Tidak Dimiliki", "status" => 403];
            $check = $this->checkAddTaskDetail($work, $task_id);
            if(!$check['success']) return $check;
            $task_detail = new TaskDetail;
            $task_detail->component = $check['component'];
            $task_detail->task_id = $task_id;
            $task_detail->save();
            return ["success" => true, "message" => "Pekerjaan Berhasil Ditambah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTaskDetail($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task_id = $request->get('task_id', null);
        $work = $request->get('work', []);
        $task_detail = TaskDetail::with('task')->find($id);
        if($task_detail === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($task_detail->task->created_by !== auth()->user()->id) return ["success" => false, "message" => "Anda Bukan Pembuat Task, Izin Update Task Detail Tidak Dimiliki", "status" => 403];
        if($task_detail->task_id !== $task_id) return ["success" => false, "message" => "Task Detail Bukan Milik Task", "status" => 400];
        if(!isset($work['name'])) return ["success" => false, "message" => "Nama Pekerjaan Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe Pekerjaan Masih Kosong", "status" => 400];

        try{
            if($task_detail->component->type !== $work['type']){
                $check = $this->checkAddTaskDetail($work, $task_id);
                if(!$check['success']) return $check;
                $component = $check['component'];
            } else {
                $component = $task_detail->component;
                if($work['type'] === 3){
                    $lists = $component->lists;
                    $values = $component->values;
                    if(count($work['update_rows'])){
                        foreach($work['update_rows'] as $row)$lists[$row['number']] = $row['value'];
                    }
                    if(count($work['add_rows'])){
                        foreach($work['add_rows'] as $row){
                            $lists[] = $row;
                            $values[] = false;
                        }
                    }
                    if(count($work['delete_rows'])){
                        foreach($work['delete_rows'] as $row){
                            unset($lists[$row]);
                            unset($values[$row]);
                        }
                    }
                    $component->lists = array_values($lists);
                    $component->values = array_values($values);
                } else if($work['type'] === 4){
                    $is_general = $work['is_general'];
                    if($is_general === null) return ["success" => false, "message" => "Pilihan Suku Cadang/Umum Belum Diisi", "status" => 400];
                    $columns = $component->columns;
                    $count_columns = count($columns);
                    if($is_general == $component->is_general){
                        if($is_general){
                            $rows = $component->rows;
                            $values = $component->values;
                            if(count($work['update_rows'])){
                                foreach($work['update_rows'] as $row){
                                    $rows[$row['number']] = $row['values'];
                                }
                            }
                            if(count($work['delete_rows'])){
                                foreach($work['delete_rows'] as $row){
                                    for($i = 0; $i < $count_columns; $i++){
                                        unset($values[$i][$row]);
                                        if(!count($values[$i])) {
                                            $values = [];
                                            break;
                                        }
                                        $values[$i] = array_values($values[$i]);
                                    }
                                    unset($rows[$row]);
                                }
                                $rows = array_values($rows);
                            }
                            if(count($work['add_rows'])){
                                foreach($work['add_rows'] as $row){
                                    $rows[] = $row;
                                    for($i = 0; $i < $count_columns; $i++){
                                        $values[$i][] = false;
                                    }
                                }
                            }
                        }
                    } else {
                        $rows = [];
                        $values = [];
                        if($is_general){
                            if(count($work['add_rows'])){
                                $row_values = [];
                                foreach($work['add_rows'] as $row){
                                    $rows[] = $row;
                                    $row_values[] = false;
                                }
                            } else return ["success" => false, "message" => "Kolom Baris Masih Kosong", "status" => 400];
                        } else {
                            $task = Task::with('inventories:id,model_id,mig_id')->find($task_id);
                            if(!$task) return ["success" => false, "message" => "Task Pada Pekerjaan Sudah Dihapus", "status" => 400];
                            $inventory_ids = $task->inventories->pluck('id') ?? [];
                            $inventories = Inventory::with('modelInventory:id,name')->select('id','model_id')->find($inventory_ids);
                            if(count($inventories)){
                                foreach($inventories as $inventory){
                                    $rows[] = $inventory->modelInventory->name;
                                    $row_values[] = false;
                                }
                            } else return ["success" => false, "message" => "Task Tidak Memiliki Aset Sehingga Baris Suku Cadang Kosong", "status" => 400];
                        }
                        for($i = 0; $i < $count_columns; $i++){
                            $values[] = $row_values;
                        }
                    }
                    if(count($work['update_columns'])){
                        foreach($work['update_columns'] as $column){
                            $columns[$column['number']] = $column['values'];
                        }
                    }
                    if(count($work['delete_columns'])){
                        foreach($work['delete_columns'] as $column){
                            unset($columns[$column]);
                            unset($values[$column]);
                        }
                        $columns = array_values($columns);
                        $values = array_values($values);
                    }
                    if(count($work['add_columns'])){
                        foreach($rows as $row) $column_values[] = false;
                        foreach($work['add_columns'] as $column){
                            $columns[] = $column;
                            $values[] = $column_values;
                        }
                    }
                    $component->rows = $rows;
                    $component->columns = $columns;
                    $component->values = $values;
                    $component->is_general = $is_general;
                } else if($work['type'] === 5) {
                    $lists = $component->lists;
                    if(count($work['update_rows'])){
                        $index = 1;
                        foreach($work['update_rows'] as $row){
                            if(!isset($row['number'])) return ["success" => false, "message" => "Update Baris $index Belum Memiliki Nomor", "status" => 400];
                            if(!isset($row['type'])) return ["success" => false, "message" => "Update Baris $index Belum Memiliki Satuan", "status" => 400];
                            if(!isset($row['description'])) return ["success" => false, "message" => "Update Baris $index Belum Memiliki Keterangan", "status" => 400];
                            $lists[$row['number']] = (object)['type' => $row['type'], 'description' => $row['description'], 'values' => '-'];
                            $index++;
                        }
                    }
                    if(count($work['add_rows'])){
                        $index = 1;
                        foreach($work['add_rows'] as $row){
                            if(!isset($row['type'])) return ["success" => false, "message" => "Tambah Baris $index Belum Memiliki Satuan", "status" => 400];
                            if(!isset($row['description'])) return ["success" => false, "message" => "Tambah Baris $index Belum Memiliki Keterangan", "status" => 400];
                            $lists[] = (object)['type' => $row['type'], 'description' => $row['description'], 'values' => '-'];
                            $index++;
                        }
                    }
                    if(count($work['delete_rows'])){
                        foreach($work['delete_rows'] as $row) unset($lists[$row]);
                    }
                    $component->lists = array_values($lists);
                } else if($work['type'] === 6) {
                    if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "Pekerjaan Belum Memiliki Nama Dropdown", "status" => 400];
                    $component->dropdown_name = $work['dropdown_name'];
                    $lists = $component->lists;
                    if(count($work['update_rows'])){
                        foreach($work['update_rows'] as $row)$lists[$row['number']] = $row['value'];
                    }
                    if(count($work['add_rows'])){
                        foreach($work['add_rows'] as $row)$lists[] = $row;
                    }
                    if(count($work['delete_rows'])){
                        foreach($work['delete_rows'] as $row) unset($lists[$row]);
                    }
                    $component->lists = array_values($lists);
                }
                $component->name = $work['name'];
                $component->description = $work['description'];
            }
            $task_detail->component = $component;
            $task_detail->save();
            return ["success" => true, "message" => "Task Detail Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTaskDetail($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task_id = $request->get('task_id', null);
        $task_detail = TaskDetail::with('task')->find($id);
        if($task_detail === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($task_detail->task->created_by !== auth()->user()->id) return ["success" => false, "message" => "Anda Bukan Pembuat Task, Izin Delete Task Detail Tidak Dimiliki", "status" => 403];
        if($task_detail->task_id !== $task_id) return ["success" => false, "message" => "Task Detail Bukan Milik Task", "status" => 400];
        try{
            $task_detail->delete();
            $task_detail->users()->detach();
            return ["success" => true, "message" => "Task Detail Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function assignTaskDetail($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $assign_ids = $request->get('assign_ids', []);
        $task_detail = TaskDetail::with('task')->find($id);
        if($task_detail === null) return ["success" => false, "message" => "Id Pekerjaan Tidak Ditemukan", "status" => 400];
        if($task_detail->task->created_by !== auth()->user()->id) return ["success" => false, "message" => "Anda Bukan Pembuat Task, Izin Assign Task Detail Tidak Dimiliki", "status" => 403];
        try{
            $task_detail->users()->sync($assign_ids);
            return ["success" => true, "message" => "Berhasil Merubah Petugas Pekerjaan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function fillTaskDetail($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $values = $request->get('values', null);
        if($values === null) return ["success" => false, "message" => "Values Tidak Boleh Kosong!", "status" => 400];
        $task_detail = TaskDetail::with(['users', 'task.users'])->find($id);
        if($task_detail === null) return ["success" => false, "message" => "Id Pekerjaan Tidak Ditemukan", "status" => 400];
        try{
            $login_id = auth()->user()->id;
            if(!count($task_detail->task->users)) return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];
            $search_task = $task_detail->task->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });
            if($search_task !== false){
                if($task_detail->task->users[$search_task]->check_in === null) return ["success" => false, "message" => "Anda Perlu Melakukan Check In Terlebih Dahulu", "status" => 400];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

            if(!in_array($task_detail->task->status, [1,3])) return ["success" => false, "message" => "Status Task Bukan On Progress", "status" => 400];

            if(!count($task_detail->users)) return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Pekerjaan Ini", "status" => 400];
            $search_task_detail = $task_detail->users->search(function ($item) use ($login_id) {
                return $item->id === $login_id;
            });
            if($search_task_detail !== false){
                $type = $task_detail->component->type;
                $component  = $task_detail->component;
                if($type !== 5){
                    if($type === 3){
                        if(!is_array($values)) return ["success" => false, "message" => "Values Harus Bertipe Data Array of Boolean", "status" => 400];
                        if(count($component->lists) !== count($values)) return ["success" => false, "message" => "Jumlah List Check Box dan Values Check Box Tidak Cocok", "status" => 400];
                        foreach($values as $value){
                            if(!is_bool($value)) return ["success" => false, "message" => "Isi Values Pada Array Terdapat Bukan Boolean", "status" => 400];
                        }
                    } else if($type === 4) {
                        if(!is_array($values)) return ["success" => false, "message" => "Values Harus Bertipe Data Array of Boolean", "status" => 400];
                        if(!count($values)) return ["success" => false, "message" => "Array Tidak Boleh Kosong", "status" => 400];
                        $row_count = count($component->rows);
                        $column_count = count($component->columns);
                        $row_edge = $row_count - 1;
                        $column_edge = $column_count - 1;
                        if(!isset($values[0][$row_edge])) return ["success" => false, "message" => "Jumlah Values Array Pada Rownya Masih Kurang", "status" => 400];
                        if(!isset($values[$column_edge][0])) return ["success" => false, "message" => "Jumlah Values Array Pada Columnnya Masih Kurang", "status" => 400];
                        if(isset($values[0][$row_count])) return ["success" => false, "message" => "Terdapat Values Array Pada Row Yang Berlebih", "status" => 400];
                        if(isset($values[$column_count][0])) return ["success" => false, "message" => "Terdapat Values Array Pada Column Yang Berlebih", "status" => 400];
                    } else {
                        if(!is_string($values)) return ["success" => false, "message" => "Values Harus Bertipe Data String", "status" => 400];
                    }
                    $component->values = $values;
                } else {
                    if(!is_array($values)) return ["success" => false, "message" => "Values Harus Bertipe Data Array of String", "status" => 400];
                    $count_list = count($component->lists);
                    if($count_list !== count($values)) return ["success" => false, "message" => "Jumlah List Numeral dan Values Numeral Harus Sama", "status" => 400];
                    for($index = 0; $index < $count_list; $index++){
                        if(!is_string($values[$index])) return ["success" => false, "message" => "Isi Values Pada Array Terdapat Bukan String", "status" => 400];
                        $component->lists[$index]->values = $values[$index];
                    }
                }
                $task_detail->component = $component;
                $task_detail->save();
                return ["success" => true, "message" => "Berhasil Merubah Isi Pekerjaan", "status" => 200];
            } else return ["success" => false, "message" => "Anda Tidak Ditugaskan Pada Pekerjaan Ini", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function fillTaskDetails($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;


        $validator = Validator::make($request->all(), [
            "fills" => "required|array",
            "fills.*.id" => "required|numeric|exists:App\TaskDetail,id",
            "fills*.values" => "required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $fills = $request->fills;
        $ids = [];
        $values = [];

        foreach($fills as $fill){
            $ids[] = $fill["id"];
            $values[] = $fill["values"];
        }

        array_multisort($ids, $values);
        $taskDetailsArr = [];
        try{
            $login_id = auth()->user()->id;
            $task_details = TaskDetail::with(['users', 'task.users'  => function($query) use ($login_id){
                $query->where('users.id',$login_id);
            }])->findMany($ids);

            $ids_task_detail = [];
            foreach($task_details as $task_detail){
                $ids_task_detail[] = $task_detail->id;
            }
            $ids_diff = array_diff($ids,$ids_task_detail);
            if($ids_diff != null) return ["success" => false, "message" => "ID : [". implode(", ",$ids_diff) ."] pada Pekerjaan Tidak Ditemukan", "status" => 400];
            $i = -1;
            foreach($task_details as $task_detail){
                $i++;
                if(!count($task_detail->task->users)) return ["success" => false, "message" => "ID : [$task_detail->id], Anda Tidak Ditugaskan Pada Tugas ini", "status" => 400];
                $search_task = $task_detail->task->users->search(function ($item) use ($login_id) {
                    return $item->id === $login_id;
                });
                if($search_task !== false){
                    if($task_detail->task->users[$search_task]->check_in === null) return ["success" => false, "message" => "Anda Perlu Melakukan Check In Terlebih Dahulu", "status" => 400];
                } else return ["success" => false, "message" => "ID : [$task_detail->id], Anda Tidak Ditugaskan Pada Tugas Ini", "status" => 400];

                if(!in_array($task_detail->task->status, [1,3])) return ["success" => false, "message" => "ID : [$task_detail->id], Status Task Bukan On Progress", "status" => 400];

                if(!count($task_detail->users)) return ["success" => false, "message" => "ID : [$task_detail->id], Anda Tidak Ditugaskan Pada Pekerjaan Ini", "status" => 400];
                $search_task_detail = $task_detail->users->search(function ($item) use ($login_id) {
                    return $item->id === $login_id;
                });

                if($search_task_detail !== false){
                    $type = $task_detail->component->type;
                    $component  = $task_detail->component;
                    if($type !== 5){
                        if($type === 3){
                            if(!is_array($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Values Harus Bertipe Data Array of Boolean", "status" => 400];
                            if(count($component->lists) !== count($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Jumlah List Check Box dan Values Check Box Tidak Cocok", "status" => 400];
                            foreach($values[$i] as $value){
                                if(!is_bool($value)) return ["success" => false, "message" => "ID : [$task_detail->id], Isi Values Pada Array Terdapat Bukan Boolean", "status" => 400];
                            }
                        } else if($type === 4) {
                            if(!is_array($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Values Harus Bertipe Data Array of Boolean", "status" => 400];
                            if(!count($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Array Tidak Boleh Kosong", "status" => 400];
                            $row_count = count($component->rows);
                            $column_count = count($component->columns);
                            $row_edge = $row_count - 1;
                            $column_edge = $column_count - 1;
                            if(!isset($values[$i][0][$row_edge])) return ["success" => false, "message" => "ID : [$task_detail->id], Jumlah Values Array Pada Rownya Masih Kurang", "status" => 400];
                            if(!isset($values[$i][$column_edge][0])) return ["success" => false, "message" => "ID : [$task_detail->id], Jumlah Values Array Pada Columnnya Masih Kurang", "status" => 400];
                            if(isset($values[$i][0][$row_count])) return ["success" => false, "message" => "ID : [$task_detail->id], Terdapat Values Array Pada Row Yang Berlebih", "status" => 400];
                            if(isset($values[$i][$column_count][0])) return ["success" => false, "message" => "ID : [$task_detail->id], Terdapat Values Array Pada Column Yang Berlebih", "status" => 400];
                        } else {
                            if(!is_string($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Values Harus Bertipe Data String", "status" => 400];
                        }
                        $component->values = $values[$i];
                    } else {

                        if(!is_array($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Values Harus Bertipe Data Array of String", "status" => 400];
                        $count_list = count($component->lists);
                        if($count_list !== count($values[$i])) return ["success" => false, "message" => "ID : [$task_detail->id], Jumlah List Numeral dan Values Numeral Harus Sama", "status" => 400];
                        for($index = 0; $index < $count_list; $index++){
                            if(!is_string($values[$i][$index])) return ["success" => false, "message" => "ID : [$task_detail->id], Isi Values Pada Array Terdapat Bukan String", "status" => 400];
                            $component->lists[$index]->values = $values[$i][$index];
                        }
                    }
                    $task_detail->component = $component;
                    $taskDetailsArr[] = $task_detail;
                } else return ["success" => false, "message" => "ID : [$task_detail->id], Anda Tidak Ditugaskan Pada Pekerjaan Ini", "status" => 400];
            }

            $batchTaskDetailUpdate = DB::transaction(function () use ($taskDetailsArr) {
                try{
                    foreach($taskDetailsArr as $td){
                        $td->save();
                    }
                    return true;
                }catch(Exception $err){
                    return false;
                }
            }, 10);

            if($batchTaskDetailUpdate) return ["success" => true, "message" => "Berhasil Merubah Isi Pekerjaan", "status" => 200];
            else return ["success" => false, "message" => "Error Database Transaction", "status" => 400];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    // Task Type

    public function getFilterTaskTypes($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $name = $request->get('name', null);
            $task_types = TaskType::select('id','name');
            if($name) $task_types->where('name', 'like', "%".$name."%");
            $task_types = $task_types->limit(50)->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $task_types, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskTypes($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $name = $request->get('name', null);
            $rows = $request->get('rows', 10);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;

            $tasks = TaskType::select('id', 'name', 'description')->withCount('tasks');

            if($name) $tasks = $tasks->where('name', 'like', "%".$name."%");
            if($sort_by){
                if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
                else if($sort_by === 'count') $tasks = $tasks->orderBy('tasks_count', $sort_type);
            }

            $tasks = $tasks->paginate($rows);
            if($tasks->isEmpty()) return ["success" => true, "message" => "Tipe Task Masih Kosong", "data" => $tasks, "status" => 200];
            return ["success" => true, "message" => "Tipe Task Berhasil Diambil", "data" => $tasks, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->get('id', null);
            $task = TaskType::with('works')->find($id);
            if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Tipe Task Berhasil Diambil", "data" => $task, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function checkTypes($work, $index, $order_type = 'Add'){
        if($order_type === 'Update_Add') $pekerjaan = "Tambahan Pekerjaan $index";
        else if($order_type === 'Update_Update') $pekerjaan = "Update Pekerjaan Dengan Id $index";
        else $pekerjaan = "Pekerjaan $index";

        if(!isset($work['name'])) return ["success" => false, "message" => "Nama $pekerjaan Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe $pekerjaan Masih Kosong", "status" => 400];
        $type = (int) $work['type'];
        $details = (object)[];

        if($type < 1 || $type > 6){
            return ["success" => false, "message" => "Tipe $pekerjaan Tidak Tepat", "status" => 400];
        } else if($type === 3){
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki List Checkbox", "status" => 400];
            $details = (object)["lists" => $lists];
        } else if($type === 4){
            if(isset($work['columns'])) $columns = $work['columns'];
            else $columns = [];
            if(!count($columns)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki Kolom", "status" => 400];
            $is_general = filter_var($work['is_general'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if($is_general){
                if(isset($work['rows'])) $rows = $work['rows'];
                else $rows = [];
                if(!count($rows)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki Baris", "status" => 400];
            }
            $details = (object)[
                "is_general" => $is_general,
                "rows" => $rows ?? [],
                "columns" => $columns
            ];
        } else if($type === 5){
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki List Numeral", "status" => 400];
            $index_number = 1;
            foreach($lists as $list){
                if(!isset($list['type'])) return ["success" => false, "message" => "$pekerjaan Dengan List $index_number Belum Memiliki Tipe", "status" => 400];
                if(!isset($list['description'])) return ["success" => false, "message" => "$pekerjaan Dengan List $index_number Belum Memiliki Keterangan", "status" => 400];
                $index_number++;
            }
            $details = (object)["lists" => $lists];
        } else if($type === 6){
            if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "$pekerjaan Memiliki Nama Dropdown", "status" => 400];
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki List Dropdown", "status" => 400];
            $details = (object)["lists" => $lists, "dropdown_name" => $work['dropdown_name']];
        }
        return ['success' => true, 'type' => $type, 'details' => $details];
    }

    public function addTaskType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $task = new TaskType;
        $name = $request->get('name');
        $check_task_type = TaskType::where('name', $name)->first();
        if($check_task_type) return ["success" => false, "message" => "Nama Task Type Sudah Digunakan", "status" => 400];
        $task->name = $name;
        $task->description = $request->get('description');
        $works = $request->get('works', []);
        if(!count($works)) return ["success" => false, "message" => "Pekerjaan Belum Diisi", "status" => 400];

        $index = 1;
        $new_works = [];
        foreach($works as $work){
            $result = $this->checkTypes($work, $index);
            if(!$result['success']) return $result;
            $new_works[] = new TaskTypeWork([
                "name" => $work['name'],
                "description" => $work['description'] ?? null,
                "type" => $result['type'],
                "details" => $result['details'],
            ]);
            $index++;
        }

        try{
            $task->save();
            $task->works()->saveMany($new_works);
            return ["success" => true, "message" => "Tipe Task Berhasil Dibuat", "id" => $task->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTaskType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task = TaskType::find($id);
        if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];$task->name = $request->get('name');
        $name = $request->get('name');
        $check_task_type = TaskType::where('name', $name)->first();
        if($check_task_type && $check_task_type->id !== $id) return ["success" => false, "message" => "Nama Task Type Sudah Digunakan", "status" => 400];

        $task->name = $name;
        $task->description = $request->get('description');
        $add_works = $request->get('add_works', []);
        $update_works = $request->get('update_works', []);
        $delete_works = $request->get('delete_works', []);

        $index = 1;
        $new_works = [];
        if(count($add_works)){
            foreach($add_works as $work){
                $result = $this->checkTypes($work, $index, 'Update_Add');
                if(!$result['success']) return $result;
                $new_works[] = new TaskTypeWork([
                    "name" => $work['name'],
                    "description" => $work['description'] ?? null,
                    "type" => $result['type'],
                    "details" => $result['details']
                ]);
                $index++;
            }
        }

        $new_update_works = [];
        if(count($update_works)){
            foreach($update_works as $work){
                $temp_id = $work['id'];
                $temp_work = TaskTypeWork::find($temp_id);
                if($temp_work === null) return ["success" => false, "message" => "Update Dengan Id $temp_id Tidak Ditemukan", "status" => 400];
                if($temp_work->task_type_id !== $id) return ["success" => false, "message" => "Update Dengan Id $temp_id Bukan Milik Tipe Task", "status" => 400];
                $result = $this->checkTypes($work, $temp_id, 'Update_Update');
                if(!$result['success']) return $result;
                $temp_work->name = $work['name'];
                $temp_work->description = $work['description'];
                $temp_work->type = $result['type'];
                $temp_work->details = $result['details'];

                $new_update_works[] = $temp_work;
            }
        }

        $new_delete_works = [];
        if(count($delete_works)){
            foreach($delete_works as $work){
                $temp_work = TaskTypeWork::find($work);
                if($temp_work === null) return ["success" => false, "message" => "Delete Dengan Id $work Tidak Ditemukan", "status" => 400];
                if($temp_work->task_type_id !== $id) return ["success" => false, "message" => "Delete Dengan Id $work Bukan Milik Tipe Task", "status" => 400];

                $new_delete_works[] = $temp_work;
            }
        }

        try{
            $task->save();
            $task->works()->saveMany($new_works);

            foreach($new_update_works as $work) $work->save();
            foreach($new_delete_works as $work) $work->delete();

            return ["success" => true, "message" => "Tipe Task Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTaskType($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id', null);
        $task = TaskType::find($id);
        if($task === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $task->delete();
            return ["success" => true, "message" => "Tipe Task Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addTaskReport($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

    try{

            $task_id = $request->task_id;
            $task = Task::find($task_id);

            if(!$task) return ["success" => false, "message" => "Data Task Tidak Ditemukan", "status" => 400];

            $current_time = date('Y-m-d H:i:s');
            $taskReport = new TaskReport();
            $taskReport->information = $request->information;
            $taskReport->task_id = $request->task_id;
            $taskReport->created_by = auth()->user()->id;
            $taskReport->created_at = $current_time;
            $taskReport->updated_at = $current_time;
            $taskReport->save();


            return ["success" => true, "message" => "Task Report Berhasil Dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskReports($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;


        $validator = Validator::make($request->all(), [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $rows = $request->rows ?? 5;

        try{

            $taskReports = TaskReport::with('task')->paginate();

            return ["success" => true, "message" => "Task Reports Berhasil Diambil", "data" => $taskReports, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskReport($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{

            $id = $request->id;
            $taskReport = TaskReport::with('task')->find($id);

            if(!$taskReport) return ["success" => false, "message" => "Task Report Tidak Ditemukan", "status" => 400];

            return ["success" => true, "message" => "Task Report Berhasil Diambil", "data" => $taskReport, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTaskReport($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{

            $id = $request->id;
            $taskReport = TaskReport::find($id);

            if(!$taskReport) return ["success" => false, "message" => "Task Report Tidak Ditemukan", "status" => 400];

            $taskReport->delete();

            return ["success" => true, "message" => "Task Report Berhasil Dihapus", "data" => $taskReport, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}
