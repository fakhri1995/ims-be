<?php 

namespace App\Services;

use App\ActivityLogProjectTask;
use App\Helpers\MockApiHelper;
use App\Project;
use App\ProjectStatus;
use App\ProjectTask;
use App\Services\GlobalService;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectTaskService{
    
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->logService = new LogService;
    }


    // PROJECT SECTION
    public function addProject(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "start_date" => "date|before:end_date",
            "end_date" => "date|after:start_date",
            "project_staffs" => "array|nullable",
            "project_staffs.*" => "numeric",
            "description" => "string|nullable"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
    
        $project = new Project();
        $project->name = $request->name;
        $project->start_date = $request->start_date;
        $project->end_date = $request->end_date;
        $project->description = $request->description;
        $project->created_by = auth()->user()->id;
        $currect_time = date("Y-m-d H:i:s");
        $project->created_at = $currect_time;
        $project->updated_at = $currect_time;
        
        
        $project_staffs = $request->project_staffs;
        $project_staffs_arr = User::select("id")->whereIn("id",$project_staffs)->pluck("id")->toArray();
        $project_staffs_diff = array_diff($project_staffs,$project_staffs_arr);
        if(count($project_staffs_diff)) return ["success" => false, "message" => "Project Staff Id : [".implode(", ",$project_staffs_diff)."] tidak ditemukan", "status" => 400];

        $project->save();
        $project->project_staffs()->attach($project_staffs_arr);
        $project->proposed_bys()->attach(auth()->user()->id);

        $logDataNew = clone $project;
        $logDataNew->project_staffs = $project_staffs_arr;
        $logDataNew->proposed_bys = [auth()->user()->id];
        
        $logProperties = [
            "log_type" => "created_project",
            "old" => null,
            "new" => $logDataNew
        ];

        $this->logService->addLogProjectTask($project->id, NULL, auth()->user()->id, "Created", $logProperties, null);

        return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => ["id" => $project->id], "status" => 200];
        
    }

    public function getProject(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $project = Project::with(["project_staffs","project_staffs.profileImage","proposed_bys.profileImage","status","tasks"])->find($id);
        if(!$project) return ["success" => false, "message" => "Data project tidak ditemukan", "status" => 400];

        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $project, "status" => 200];
    }

    public function getProjects($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "user_id" => "numeric",
            "page" => "numeric",
            "rows" => "numeric|max:50",
            "sort_by" => "in:name,start_date,end_date,status",
            "sort_type" => "in:asc,desc"
        ];

        if($request->to && $request->from){
            $rules["from"] = "date|before:to";
            $rules["to"] = "date|after:from";
        }

        $validator = Validator::make($request->all(), $rules);

        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        

        $projects = Project::with(["proposed_bys","status"]);
        
        $rows = $request->rows ?? 5;
        $user_id = $request->user_id;
        $keyword = $request->keyword;
        $from = $request->from ?? NULL;
        $to = $request->to ?? NULL;
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type ?? 'asc';
        $status_ids = $request->status_ids ? explode(",",$request->status_ids) : NULL;

        if($user_id) $projects = $projects->whereHas("project_staffs", function($q) use ($user_id){
            $q->where("id", $user_id);
        });
        if($keyword) $projects = $projects->where("name","LIKE","%$keyword%");
        if($status_ids) $projects = $projects->whereIn("status_id", $status_ids);
        if($from) $projects = $projects->where(function ($q) use ($from){
            $q->where("start_date", ">=", $from)
            ->orWhere("end_date", ">=", $from);
        });
        if($to) $projects = $projects->where(function ($q) use ($to){
            $q->where("start_date", "<=", $to)
            ->orWhere("end_date", "<=", $to);
        });


        //sorting
        if(in_array($sort_by, ["name","start_date","end_date"])) $projects = $projects->orderBy($sort_by,$sort_type);
        if($sort_by == "status") $projects = $projects->orderBy(ProjectStatus::select("name")
        ->whereColumn("project_statuses.id","projects.status_id"),$sort_type);

        $projects = $projects->paginate($rows);
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $projects, "status" => 200];
    }
    
    public function getProjectsList($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $projects = Project::select("id","name")->orderBy("name")->get();
            
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $projects, "status" => 200];
    }

    public function updateProject(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "name" => "required",
            "start_date" => "date|before:end_date",
            "end_date" => "date|after:start_date",
            "project_staffs" => "array",
            "project_staffs.*" => "numeric",
            "proposed_bys" => "array",
            "proposed_bys.*" => "numeric",
            "status_id" => "numeric",
            "description" => "string"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $project = Project::find($id);
        if(!$project) return ["success" => false, "message" => "Data project tidak ditemukan", "status" => 400];

        $status_id = $request->status_id;
        $projectStatus = ProjectStatus::find($status_id);
        if(!$projectStatus) return ["success" => false, "message" => "Project status tidak ditemukan", "status" => 400];

        //oldLog
        $logDataOld = clone $project;
        $logDataOld->project_staffs = $logDataOld->project_staffs()->pluck("id")->toArray();
        $logDataOld->proposed_bys = $logDataOld->proposed_bys()->pluck("id")->toArray();
        

        $project->name = $request->name;
        $project->start_date = $request->start_date;
        $project->end_date = $request->end_date;
        $project->description = $request->description;
        $project->status_id = $request->status_id;
        $currect_time = date("Y-m-d H:i:s");
        $project->updated_at = $currect_time;
        
        $project_staffs = $request->project_staffs;
        $project_staffs_arr = User::select("id")->whereIn("id",$project_staffs)->pluck("id")->toArray();
        $project_staffs_diff = array_diff($project_staffs,$project_staffs_arr);
        if(count($project_staffs_diff)) return ["success" => false, "message" => "Project Staff Id : [".implode(", ",$project_staffs_diff)."] tidak ditemukan", "status" => 400];

        $proposed_bys = $request->proposed_bys;
        $proposed_bys_arr = User::select("id")->whereIn("id",$proposed_bys)->pluck("id")->toArray();
        $proposed_bys_diff = array_diff($proposed_bys,$proposed_bys_arr);
        if(count($proposed_bys_diff)) return ["success" => false, "message" => "Proposed By Id : [".implode(", ",$proposed_bys_diff)."] tidak ditemukan", "status" => 400];

        $project->save();
        $project->project_staffs()->sync($project_staffs_arr);
        $project->proposed_bys()->sync($proposed_bys_arr);

        $logDataNew = clone $project;
        $logDataNew->project_staffs = $project_staffs_arr;
        $logDataNew->proposed_bys = $proposed_bys_arr;

        $logProperties = [
            "log_type" => "updated_project",
            "old" => $logDataOld,
            "new" => $logDataNew
        ];

        unset($logDataNew->updated_at);
        unset($logDataOld->updated_at);


        //if the data is not same, then the write the log
        if(json_encode($logDataOld) != json_encode($logDataNew)){
            $this->logService->addLogProjectTask($project->id, NULL, auth()->user()->id, "Updated", $logProperties, null);
        }

        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];

    }

    public function deleteProject(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $project = Project::find($id);
        if(!$project) return ["success" => false, "message" => "Data project tidak ditemukan", "status" => 400];

        $logDataOld = clone $project;
        $logDataOld->project_staffs = $logDataOld->project_staffs()->pluck("id")->toArray();
        $logDataOld->proposed_bys = $logDataOld->proposed_bys()->pluck("id")->toArray();

        $project->delete();
        
        $logProperties = [
            "log_type" => "deleted_project",
            "old" => $logDataOld,
            "new" => null,
        ];

        $this->logService->addLogProjectTask($project->id, NULL, auth()->user()->id, "Deleted", $logProperties, null);

        return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
    }

    public function updateProject_status(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "status_id" => "numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $project = Project::find($id);
        if(!$project) return ["success" => false, "message" => "Data project tidak ditemukan", "status" => 400];

        $status_id = $request->status_id;
        $projectStatus = ProjectStatus::find($status_id);
        if(!$projectStatus) return ["success" => false, "message" => "Project status tidak ditemukan", "status" => 400];

        //oldLog
        $logDataOld = clone $project;
        $logDataOld->project_staffs = $logDataOld->project_staffs()->pluck("id")->toArray();
        $logDataOld->proposed_bys = $logDataOld->proposed_bys()->pluck("id")->toArray();
        

        $project->status_id = $request->status_id;
        $currect_time = date("Y-m-d H:i:s");
        $project->updated_at = $currect_time;
        $project->save();

        $logDataNew = clone $logDataOld;
        $logDataNew->status_id = $request->status_id;

        $logProperties = [
            "log_type" => "updated_project",
            "old" => $logDataOld,
            "new" => $logDataNew
        ];

        unset($logDataNew->updated_at);
        unset($logDataOld->updated_at);


        //if the data is not same, then the write the log
        if(json_encode($logDataOld) != json_encode($logDataNew)){
            $this->logService->addLogProjectTask($project->id, NULL, auth()->user()->id, "Updated", $logProperties, null);
        }

        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];

    }

    public function getProjectsCount(Request $request, $route_name)
    {

        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $projectsStatus = ProjectStatus::withCount('projects')->get();

        $total = 0;

        foreach($projectsStatus as $p){
            $total += $p->projects_count;
        }

        $data = [
            "status" => $projectsStatus,
            "total" => $total
        ];
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }

    public function getProjectsDeadline(Request $request, $route_name)
    {   
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "year" => "numeric"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $year = $request->year ?? date('Y');
        $yearMonth = date('Y-m');
        $yearMonthPast3Month = date('Y-m', strtotime('-3 month'));
        $yearMonthNext3Month = date('Y-m', strtotime('+3 month'));
        $yearPast3Month = explode('-', $yearMonthPast3Month)[0];
        $monthPast3Month = explode('-', $yearMonthPast3Month)[1];
        $yearNext3Month = explode('-', $yearMonthNext3Month)[0];
        $monthNext3Month = explode('-', $yearMonthNext3Month)[1];

        $projects = Project::select(DB::raw("count(*) as total, CAST(DATE_FORMAT(end_date, '%m') as int) as month, CAST(DATE_FORMAT(end_date, '%Y') as int) as year"))
        ->whereYear('end_date' , '=', $yearPast3Month)
        ->whereYear('end_date' , '=', $yearNext3Month, 'or')
        ->whereMonth('end_date' , '=', $monthPast3Month)
        ->whereMonth('end_date' , '=', $monthNext3Month, 'or')
        ->groupBy(DB::raw("DATE_FORMAT(end_date, '%m')"))
        ->get();

        $data = [];
        for($i = 1; $i <= 12; $i++){
            $data[] = [
                "total" => 0,
                "month" => $i,
                "year" => $year,
                "month_string" => $this->globalService->getIndonesiaMonth($i),
            ];
        }

        foreach($projects as $p){
            $data[$p->month-1] = $p;
            $data[$p->month-1]["month_string"] = $this->globalService->getIndonesiaMonth($p->month);
        }

        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }

    //TASK SECTION
    public function addProjectTask(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "project_id" => "numeric|nullable",
            "start_date" => "date|before:end_date",
            "end_date" => "date|after:start_date",
            "task_staffs" => "array",
            "task_staffs.*" => "numeric"
            
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $project_id = $request->project_id ?? NULL;
        $project_staffs_ids = [];
        if($project_id){
            $project = Project::with("project_staffs")->find($project_id);
            if(!$project) return ["success" => false, "message" => "Data Project tidak ditemukan.", "status" => 400]; 
            $project_staffs_ids = $project->project_staffs->pluck("id")->toArray();
        }
        

        $projectTask = new ProjectTask();
        $projectTask->name = $request->name;
        $projectTask->start_date = $request->start_date;
        $projectTask->end_date = $request->end_date;
        $projectTask->project_id = $project_id;
        $projectTask->description = $request->description;
        $currect_time = date("Y-m-d H:i:s");
        $projectTask->created_by = auth()->user()->id;
        $projectTask->created_at = $currect_time;
        $projectTask->updated_at = $currect_time;

        
        $task_staffs = $request->task_staffs;
        sort($task_staffs);
        if(count($project_staffs_ids)){
            $task_staffs_diff = array_diff($task_staffs,$project_staffs_ids);
            if(count($task_staffs_diff)) return ["success" => false, "message" => "Task Staff Id : [".implode(", ",$task_staffs_diff)."] tidak bagian dari project", "status" => 400];
        }else{
            $task_staffs_arr = User::select("id")->whereIn("id",$task_staffs)->pluck("id")->toArray();
            $task_staffs_diff = array_diff($task_staffs,$task_staffs_arr);
            if(count($task_staffs_diff)) return ["success" => false, "message" => "Task Staff Id : [".implode(", ",$task_staffs_diff)."] tidak ditemukan", "status" => 400];
        }

        $projectTask->save();
        $projectTask->task_staffs()->attach($task_staffs);

        $logDataNew = clone $projectTask;
        $logDataNew->task_staffs = $task_staffs;
        
        $logProperties = [
            "log_type" => "created_task",
            "old" => null,
            "new" => $logDataNew
        ];

        $this->logService->addLogProjectTask($project_id, $projectTask->id, auth()->user()->id, "Created", $logProperties, null);

        return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => ["id" => $projectTask->id], "status" => 200];
    }

    public function getProjectTask(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectTask = ProjectTask::with("project","task_staffs")->find($id);
        if(!$projectTask) return ["success" => false, "message" => "Data tidak ditemukan", "status" => "400"];

        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $projectTask, "status" => 200];
    }

    public function getProjectTasks($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "user_id" => "numeric",
            "project_id" => "numeric",
            "page" => "numeric",
            "rows" => "numeric|max:50",
            "sort_by" => "in:deadline",
            "sort_type" => "in:asc,desc"
        ];

        if($request->to && $request->from){
            $rules["from"] = "date|before:to";
            $rules["to"] = "date|after:from";
        }

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $projectTasks = ProjectTask::with('task_staffs');

        $rows = $request->rows ?? 5;
        $user_id = $request->user_id;
        $project_id = $request->project_id;
        $keyword = $request->keyword;
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type ?? 'asc';
        $status_ids = $request->status_ids ? explode(",",$request->status_ids) : NULL;

        if($project_id) $projectTasks = $projectTasks->where("project_id", $project_id);
        if($user_id) $projectTasks = $projectTasks->whereHas("task_staffs", function($q) use ($user_id){
            $q->where("id", $user_id);
        });
        if($keyword) $projectTasks = $projectTasks->where("name","LIKE","%$keyword%");
        if($status_ids) $projectTasks = $projectTasks->whereIn("status_id", $status_ids);
       


        //sorting
        if($sort_by == "deadline") $projectTasks = $projectTasks->orderBy("end_date",$sort_type);
        if($sort_by == "status") $projectTasks = $projectTasks->orderBy(ProjectStatus::select("name")
        ->whereColumn("project_statuses.id","project_tasks.status_id"),$sort_type);

        $projectTasks = $projectTasks->paginate($rows);
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $projectTasks, "status" => 200];
    }


    public function updateProjectTask(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "name" => "string|required",
            "project_id" => "numeric|nullable",
            "start_date" => "date|before:end_date",
            "end_date" => "date|after:start_date",
            "task_staffs" => "array",
            "task_staffs.*" => "numeric",
            "status_id" => "numeric",
            "description" => "string"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectTask = ProjectTask::find($id);
        if(!$projectTask) return ["success" => false, "message" => "Data tidak ditemukan", "status" => "400"];

        $project_id = $request->project_id ?? NULL;
        if($projectTask->project_id != $project_id) return ["success" => false, "message" => "Project dari task yang telah dibuat, tidak dapat diganti.", "status" => "400"];

        $project_staffs_ids = [];
        if($project_id){
            $project = Project::with("project_staffs")->find($project_id);
            if(!$project) return ["success" => false, "message" => "Data Project tidak ditemukan.", "status" => 400]; 
            $project_staffs_ids = $project->project_staffs->pluck("id")->toArray();
        }

        
        $status_id = $request->status_id;
        $projectStatus = ProjectStatus::find($status_id);
        if(!$projectStatus) return ["success" => false, "message" => "Project  status tidak ditemukan", "status" => 400];
        
        //oldLog
        $logDataOld = clone $projectTask;
        $logDataOld->task_staffs = $logDataOld->task_staffs()->pluck("id")->toArray();

        $projectTask->name = $request->name;
        $projectTask->start_date = $request->start_date;
        $projectTask->end_date = $request->end_date;
        $currect_time = date("Y-m-d H:i:s");
        $projectTask->updated_at = $currect_time;
        $projectTask->status_id = $request->status_id;
        $projectTask->description = $request->description;

        
        $task_staffs = $request->task_staffs;
        sort($task_staffs);
        if(count($project_staffs_ids)){
            $task_staffs_diff = array_diff($task_staffs,$project_staffs_ids);
            if(count($task_staffs_diff)) return ["success" => false, "message" => "Task Staff Id : [".implode(", ",$task_staffs_diff)."] tidak bagian dari project", "status" => 400];
        }else{
            $task_staffs_arr = User::select("id")->whereIn("id",$task_staffs)->pluck("id")->toArray();
            $task_staffs_diff = array_diff($task_staffs,$task_staffs_arr);
            if(count($task_staffs_diff)) return ["success" => false, "message" => "Task Staff Id : [".implode(", ",$task_staffs_diff)."] tidak ditemukan", "status" => 400];
        }

        $projectTask->save();
        $projectTask->task_staffs()->sync($task_staffs);


        $logDataNew = clone $projectTask;
        $logDataNew->task_staffs = $task_staffs;

        $logProperties = [
            "log_type" => "updated_project",
            "old" => $logDataOld,
            "new" => $logDataNew
        ];

        unset($logDataNew->updated_at);
        unset($logDataOld->updated_at);


        //if the data is not same, then the write the log
        if(json_encode($logDataOld) != json_encode($logDataNew)){
            $this->logService->addLogProjectTask($project_id, $id, auth()->user()->id, "Updated", $logProperties, null);
        }

        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];

    }

    public function deleteProjectTask(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectTask = ProjectTask::find($id);
        if(!$projectTask) return ["success" => false, "message" => "Data project task tidak ditemukan", "status" => 400];

        $logDataOld = clone $projectTask;
        $logDataOld->task_staffs = $logDataOld->task_staffs()->pluck("id")->toArray();

        $projectTask->delete();

        $logProperties = [
            "log_type" => "deleted_project",
            "old" => $logDataOld,
            "new" => null,
        ];

        $this->logService->addLogProjectTask($projectTask->project_id, $id, auth()->user()->id, "Deleted", $logProperties, null);

        return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
    }

    public function updateProjectTask_status(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "status_id" => "numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectTask = ProjectTask::find($id);
        if(!$projectTask) return ["success" => false, "message" => "Data tidak ditemukan", "status" => "400"];
        $project_id = $projectTask->project_id;

        $status_id = $request->status_id;
        $projectStatus = ProjectStatus::find($status_id);
        if(!$projectStatus) return ["success" => false, "message" => "Project  status tidak ditemukan", "status" => 400];
        
        //oldLog
        $logDataOld = clone $projectTask;
        $logDataOld->task_staffs = $logDataOld->task_staffs()->pluck("id")->toArray();

        $currect_time = date("Y-m-d H:i:s");
        $projectTask->updated_at = $currect_time;
        $projectTask->status_id = $request->status_id;

        $projectTask->save();

        $logDataNew = clone $logDataOld;
        $logDataNew->status_id = $request->status_id;

        $logProperties = [
            "log_type" => "updated_project",
            "old" => $logDataOld,
            "new" => $logDataNew
        ];

        unset($logDataNew->updated_at);
        unset($logDataOld->updated_at);


        //if the data is not same, then the write the log
        if(json_encode($logDataOld) != json_encode($logDataNew)){
            $this->logService->addLogProjectTask($project_id, $id, auth()->user()->id, "Updated", $logProperties, null);
        }

        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];

    }

    public function getProjectTasksCount(Request $request, $route_name)
    {

        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "project_id" => "numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $project_id = $request->project_id ?? NULL;

        $projectTasksStatus = new ProjectStatus;
        $projectTasksStatus = $projectTasksStatus->withCount(['project_tasks' => function ($q) use ($project_id){
            if($project_id) $q->where("project_id", $project_id);
            return $q;
        }])->get();

        $projectTasksStatusArray = [];
        
        $total = 0;

        foreach($projectTasksStatus as $p){
            $projectTasksStatusArray[] = $p;
            $total += $p->project_tasks_count;
        }


        $data = [
            "status" => $projectTasksStatusArray,
            "total" => $total
        ];
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }

    // PROJECT STATUS
    public function addProjectStatus(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "color" => "string|nullable",
            "after_id" => "numeric|nullable",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $after_id = $request->after_id ?? NULL;
        if($after_id != NULL){
            $projectStatusAfter = ProjectStatus::find($after_id);
            if(!$projectStatusAfter) return ["success" => true, "message" => "After id tidak ditemukan", "status" => 200];
        }

        $projectStatus = new ProjectStatus();
        $projectStatus->name = $request->name;
        $projectStatus->color = $request->color;
        $projectStatus->name = $request->name;

        
        $projectStatuses = new ProjectStatus();
        if($after_id == NULL){
            $projectStatuses->withTrashed()->increment("display_order");
            $projectStatus->display_order = 1;
        }else{
            $projectStatuses->withTrashed()->where("display_order",">",$projectStatusAfter->display_order)->increment("display_order");
            $projectStatus->display_order = $projectStatusAfter->display_order + 1;
        }

        $projectStatus->save();

        return ["success" => true, "message" => "Data Berhasil Dibuat", "data" => ["id" => $projectStatus->id], "status" => 200];
    }

    public function getProjectStatus(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectStatus = ProjectStatus::find($id);
        if(!$projectStatus) return ["success" => true, "message" => "Project status tidak ditemukan", "status" => 200];

        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $projectStatus, "status" => 200];
    }

    public function getProjectStatuses($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $projectStatuses = ProjectStatus::orderBy("display_order","asc")->get();
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $projectStatuses, "status" => 200];
    }



    public function updateProjectStatus(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
            "name" => "string|required",
            "color" => "string|required",
            "after_id" => "numeric|nullable"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectStatus = ProjectStatus::find($id);
        if(!$projectStatus) return ["success" => false, "message" => "Project status tidak ditemukan", "status" => 400];

        $after_id = $request->after_id ?? NULL;
        if($after_id != NULL){
            $projectStatusAfter = ProjectStatus::find($after_id);
            if(!$projectStatusAfter) return ["success" => false, "message" => "After id tidak ditemukan", "status" => 400];
        }

        if($id == $after_id) return ["success" => false, "message" => "id dan after id tidak boleh sama", "status" => 400]; 

        $projectStatus->name = $request->name;
        $projectStatus->color = $request->color;
        $projectStatus->name = $request->name;

        
        $projectStatuses = new ProjectStatus();
        if($after_id == NULL){
            $projectStatuses->withTrashed()->where("display_order","<",$projectStatus->display_order)->increment("display_order");
            $projectStatus->display_order = 1;
        }else{
            if($projectStatusAfter->display_order < $projectStatus->display_order){
                $projectStatuses->withTrashed()
                ->where("display_order",">",$projectStatusAfter->display_order)
                ->where("display_order","<",$projectStatus->display_order)
                ->increment("display_order");
                $projectStatus->display_order = $projectStatusAfter->display_order + 1;
            }else{
                $projectStatuses->withTrashed()
                ->where("display_order",">",$projectStatus->display_order)
                ->where("display_order","<=",$projectStatusAfter->display_order)
                ->decrement("display_order");
                $projectStatus->display_order = $projectStatusAfter->display_order;
            }
            
        }

        $projectStatus->save();

        return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];

    }

    public function deleteProjectStatus(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $projectStatus = ProjectStatus::find($id);
        if(!$projectStatus) return ["success" => false, "message" => "Project status tidak ditemukan", "status" => 400];

        $projectStatus->delete();

        return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
    }


    //NOTES
    public function addProjectLogNotes($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "project_id" => "numeric|nullable|required_without:task_id",
            "task_id" => "numeric|nullable",
            "notes" => "required",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $project_id = $request->project_id ?? NULL;
        $task_id = $request->task_id ?? NULL;
        $notes = $request->notes ?? NULL;

        $description = "Menambahkan sebuah catatan pada ";

        if(!$task_id) {
            $project = Project::find($project_id);
            if(!$project) return ["success" => false, "message" => "Project tidak ditemukan.", "status" => 400];
            $description .= "proyek $project->name.";
        }
        else{
            $task = ProjectTask::find($task_id);
            if(!$task) return ["success" => false, "message" => "Task tidak ditemukan.", "status" => 400];
            $description .= "task $task->name.";
        }

        if($this->logService->addProjectLogFuntion($project_id, $task_id, auth()->user()->id , "Notes", NULL, $notes, $description)){
            return ["success" => true, "message" => "Notes berhasil ditambahkan.", "status" => 200]; 
        };
        return ["success" => false, "message" => "Gagal menambahkan notes.", "status" => 400];
        
    }

    public function deleteProjectLogNotes($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $logId = $request->logId;
        
        return $this->logService->deleteProjectLogNotes($logId);
        
    }

}