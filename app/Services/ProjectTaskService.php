<?php 

namespace App\Services;

use App\Project;
use App\ProjectStatus;
use App\ProjectTask;
use App\Services\GlobalService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectTaskService{
    public $projectJson = [
        "id" => 1,
        "name" => "Fikri Ilmi",
        "proposed_bys" => [
            [
                [
                    "id" => 1,
                    "name" => "Lesti",
                    "profile_image" => [
                        "id" => 0,
                        "link" => "staging\/Users\/default_user.png",
                        "description" => "profile_image"
                    ]
                ]
            ],
        ],
        "start_date" => "2022-01-01",
        "end_date" => "2022-02-02",
        "description" => "",
        "project_staffs" => [
            [
                "id" => 1,
                "name" => "Yasmin",
                "profile_image" => [
                    "id" => 0,
                    "link" => "staging\/Users\/default_user.png",
                    "description" => "profile_image"
                ]
            ]
        ],
        "status_id" => 1,
        "created_by" => 1,
        "created_at" => "2022-01-01 11:50:20",
        "updated_at" => "2022-01-01 11:50:20",
        "deleted_at" => NULL,
    ];

    public $taskJson1 = [
        "id" => 1,
        "project_id" => NULL,
        "project" => NULL,
        "name" => "Nama Task",
        "start_date" => "2022-01-01",
        "end_date" => "2022-01-02",
        "task_staffs" => [
            [
                "id" => 1,
                "name" => "Yasmin",
                "profile_image" => [
                    "id" => 0,
                    "link" => "staging\/Users\/default_user.png",
                    "description" => "profile_image"
                ]
            ]
        ],
        "description" => "text",
        "status_id" => 1,
        "created_by" => 1,
        "created_at" => "2022-01-01 11:50:20",
        "updated_at" => "2022-01-01 11:50:20",
        "deleted_at" => NULL,
    ];

    public $taskJson2 = [
        "id" => 2,
        "project_id" => 1,
        "name" => "Nama Task",
        "start_date" => "2022-01-01",
        "end_date" => "2022-01-02",
        "task_staffs" => [
            [
                "id" => 1,
                "name" => "Yasmin",
                "profile_image" => [
                    "id" => 0,
                    "link" => "staging\/Users\/default_user.png",
                    "description" => "profile_image"
                ]
            ]
        ],
        "description" => "description",
        "status_id" => 1,
        "created_by" => 1,
        "created_at" => "2022-01-01 11:50:20",
        "updated_at" => "2022-01-01 11:50:20",
        "deleted_at" => NULL,
    ];

    public $statusJson = [
        "id" => 1,
        "name" => "On-Going",
        "color" => "#ABC123",
        "display_order" => 1
    ];

    public function paginateTemplate($request, $data){
        return [
            "current_page" => 2,
            "data" => $data,
            "first_page_url" => env("APP_URL")."/".$request->path()."?page=1",
            "from" => 11,
            "last_page" => 2,
            "last_page_url" => env("APP_URL")."/".$request->path()."?page=2",
            "next_page_url" => env("APP_URL")."/".$request->path()."?page=3",
            "path" => env("APP_URL")."/".$request->path()."?",
            "per_page" => "10",
            "prev_page_url" => env("APP_URL")."/".$request->path()."?page=1",
            "to" => 20,
            "total" => 30
        ];
    }

    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->taskJson2['project'] = $this->projectJson;
        $this->projectJson['status'] = $this->statusJson;
        $this->taskJson1['status'] = $this->statusJson;
        $this->taskJson2['status'] = $this->statusJson;
    }

    /**
     * @param array $user_ids
     * @param array $optional_column
     */
    private function validateUsersById($user_ids, $optional_column){
        $project_staffs = $request->project_staffs;
        $project_staffs_arr = User::select("id")->whereIn("id",$project_staffs)->pluck("id")->toArray();
        $project_staffs_diff = array_diff($project_staffs,$project_staffs_arr);
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

        // $proposed_bys = $request->proposed_bys;
        // $proposed_bys_arr = User::select("id")->whereIn("id",$proposed_bys)->pluck("id")->toArray();
        // $proposed_bys_diff = array_diff($proposed_bys,$proposed_bys_arr);
        // if(count($proposed_bys_diff)) return ["success" => false, "message" => "Proposed By Id : [".implode(", ",$proposed_bys_diff)."] tidak ditemukan", "status" => 400];


        $project->save();
        $project->project_staffs()->attach($project_staffs_arr);
        $project->proposed_bys()->attach(auth()->user()->id);

        return ["success" => true, "message" => "Data Berhasil Dibuat", "status" => 200];
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
        
        $projects = Project::with(["proposed_bys","status"])
            ->join('projects_proposes', 'projects.id', '=', 'projects_proposes.project_id')
            ->join(
                DB::raw('(SELECT id,name FROM users ORDER BY name) users_sorting'), 
                function($join)
                {
                    $join->on('projects_proposes.user_id', '=', 'users_sorting.id');
                }
            )->select('projects.*','users_sorting.name as proposed_by');
            
        $projects = $projects->paginate();
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

        $project->delete();

        return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
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
        if($project_id){
            $project = Project::find($project_id);
            if(!$project) return ["success" => false, "message" => "Data Project tidak ditemukan.", "status" => 400]; 
        }

        $projectTask = new ProjectTask();


        return ["success" => true, "message" => "Data Berhasil Dibuat", "status" => 200];
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

        if(rand(0,1)) $taskJson = $this->taskJson1;
        else $taskJson = $this->taskJson2;

        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $taskJson, "status" => 200];
    }

    public function getProjectTasks($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $data = $this->paginateTemplate($request, [$this->taskJson1, $this->taskJson2]);
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
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

        return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
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

        return ["success" => true, "message" => "Data Berhasil Dibuat", "status" => 200];
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


}