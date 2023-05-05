<?php 

namespace App\Services;
use App\Services\GlobalService;
use Illuminate\Http\Request;
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

        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $this->projectJson, "status" => 200];
    }

    public function getProjects($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $data = $this->paginateTemplate($request, [$this->projectJson, $this->projectJson]);
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
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


        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $this->statusJson, "status" => 200];
    }

    public function getProjectStatuses($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [$this->statusJson, $this->statusJson], "status" => 200];
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

        return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
    }


}