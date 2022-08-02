<?php 

namespace App\Services;
use App\CareerV2;
use App\CareerV2Apply;
use App\CareerV2Experience;
use App\CareerV2RoleType;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CareerV2Service{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function getCareer(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "career_id" => "required|exists:career_v2,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->get("career_id");
        $career = CareerV2::with(["roleType","experience"])->find($id);
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        $career->experience->makeHidden("id");
        $career->roleType->makeHidden("id");
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCareers($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $rules = [
            "page" => "numeric",
            "limit" => "numeric|in:5,10,25,50",
            "date_from" => "date",
            "date_to" => "date",
            "sort" => "in:name,role_type,experience,created_at,is_posted,total",
            "order" => "in:asc,desc"
        ];

        if($request->date_to && $request->date_from){
            $rules["date_from"] = "date|before:date_to";
            $rules["date_to"] = "date|after:date_from";
        }

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $search = $request->search ? $request->search : NULL;
        $role = $request->role ? explode(",",$request->role) : NULL;
        $experience = $request->experience ? explode(",",$request->experience) : NULL;
        $date_from = $request->date_from ? $request->date_from : NULL;
        $date_to = $request->date_to ? $request->date_to : NULL;
        $is_posted = isset($request->is_posted) ? $request->is_posted : NULL;
        
        $limit = $request->limit ? $request->limit : 5;
        $career = CareerV2::with(["roleType" ,"experience"])->withCount("apply");
        
        // filter
        if($search) $career = $career->where("name","LIKE", "%$search%");
        if($role) $career = $career->whereIn("career_role_type_id", $role);
        if($experience) $career = $career->whereIn("career_experience_id", $experience);
        if($date_from) $career = $career->where("created_at", ">=", $date_from);
        if($date_to) $career = $career->where("created_at", "<=", $date_to);
        if($is_posted != NULL) $career = $career->where("is_posted", $is_posted);
        
        // sort
        $order = $request->get('order','asc');
        $sort = $request->sort ? $request->sort : NULL;
        if($sort == "name") $career = $career->orderBy('name',$order);
        if($sort == "created_at") $career = $career->orderBy('created_at',$order);
        if($sort == "is_posted") $career = $career->orderBy('is_posted',$order);
        if($sort == "total") $career = $career->orderBy('apply_count',$order);
        if($sort == "experience") $career = $career->orderBy(CareerV2Experience::select('min')->whereColumn('career_v2_experiences.id', 'career_v2.career_experience_id'),$order);
        if($sort == "role_type") $career = $career->orderBy(CareerV2RoleType::select('name')->whereColumn('career_v2_role_types.id', 'career_v2.career_role_type_id'),$order);




        $career = $career->paginate($limit);
        
        if($career->count() == 0){
            return ["success" => true, "message" => "Data Tidak Tersedia", "data" => $career, "status" => 200];
        }

        $careerCount = count($career);
        for($i=0;$i<$careerCount;$i++){
            $career[$i]->experience->makeHidden("id");
            $career[$i]->roleType->makeHidden("id");   
        }
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPostedCareers($request, $route_name){

        $rules = [
            "page" => "numeric",
            "limit" => "numeric|in:5,10,25,50",
            "date_from" => "date",
            "date_to" => "date",
            "sort" => "in:name,role_type,experience,created_at,is_posted,total",
            "order" => "in:asc,desc"
        ];

        if($request->date_to && $request->date_from){
            $rules["date_from"] = "date|before:date_to";
            $rules["date_to"] = "date|after:date_from";
        }

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $search = $request->search ? $request->search : NULL;
        $role = $request->role ? explode(",",$request->role) : NULL;
        $experience = $request->experience ? explode(",",$request->experience) : NULL;
        $date_from = $request->date_from ? $request->date_from : NULL;
        $date_to = $request->date_to ? $request->date_to : NULL;
        $is_posted = 1;
        
        $limit = $request->limit ? $request->limit : 5;
        $career = CareerV2::with(["roleType" ,"experience"])->withCount("apply");
        
        // filter
        if($search) $career = $career->where("name","LIKE", "%$search%");
        if($role) $career = $career->whereIn("career_role_type_id", $role);
        if($experience) $career = $career->whereIn("career_experience_id", $experience);
        if($date_from) $career = $career->where("created_at", ">=", $date_from);
        if($date_to) $career = $career->where("created_at", "<=", $date_to);
        if($is_posted != NULL) $career = $career->where("is_posted", $is_posted);
        
        // sort
        $order = $request->get('order','asc');
        $sort = $request->sort ? $request->sort : NULL;
        if($sort == "name") $career = $career->orderBy('name',$order);
        if($sort == "created_at") $career = $career->orderBy('created_at',$order);
        if($sort == "is_posted") $career = $career->orderBy('is_posted',$order);
        if($sort == "total") $career = $career->orderBy('apply_count',$order);
        if($sort == "experience") $career = $career->orderBy(CareerV2Experience::select('min')->whereColumn('career_v2_experiences.id', 'career_v2.career_experience_id'),$order);
        if($sort == "role_type") $career = $career->orderBy(CareerV2RoleType::select('name')->whereColumn('career_v2_role_types.id', 'career_v2.career_role_type_id'),$order);




        $career = $career->paginate($limit);
        if($career->count() == 0){
            return ["success" => true, "message" => "Data Tidak Tersedia", "data" => $career, "status" => 200];
        }

        $careerCount = count($career);
        for($i=0;$i<$careerCount;$i++){
            $career[$i]->experience->makeHidden("id");
            $career[$i]->roleType->makeHidden("id");   
        }
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPostedCareer(Request $request, $route_name){
        $validator = Validator::make($request->all(), [
            "career_id" => "required|exists:career_v2,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->get("career_id");
        $career = CareerV2::with(["roleType","experience"])->where(['is_posted' => 1, 'id' => $id])->first();
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        $career->experience->makeHidden("id");
        $career->roleType->makeHidden("id");
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCareer(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "career_role_type_id" => "required|exists:career_v2_role_types,id|numeric",
            "career_experience_id" => "required|exists:career_v2_experiences,id|numeric",
            "salary_min" => "required|numeric",
            "salary_max" => "required|numeric",
            "overview" => "required",
            "description" => "required",
            "is_posted" => "required",
            "qualification" => "required"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $career = new CareerV2();
        $career->name = $request->name;
        $career->career_role_type_id = $request->career_role_type_id;
        $career->career_experience_id = $request->career_experience_id;
        $career->salary_min = $request->salary_min;
        $career->salary_max = $request->salary_max;
        $career->overview = $request->overview;
        $career->description = $request->description;
        $career->is_posted = $request->is_posted;
        $career->created_at = Date('Y-m-d H:i:s');
        $career->updated_at = Date('Y-m-d H:i:s');
        $career->created_by = auth()->user()->id;
        try{
            $career->save();
            return ["success" => true, "message" => "Career Berhasil Ditambahkan", "id" => $career->id, "status" => 201];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateCareer(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "career_id" => "required|exists:career_v2,id",
            "name" => "filled",
            "career_role_type_id" => "exists:career_v2_role_types,id|numeric",
            "career_experience_id" => "exists:career_v2_experiences,id|numeric",
            "salary_min" => "filled|numeric",
            "salary_max" => "filled|numeric",
            "overview" => "filled",
            "description" => "filled",
            "is_posted" => "filled",
            "qualification" => "filled"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $fillable = ["name","career_role_type_id","career_experience_id","salary_min","salary_max", "overview","description","is_posted"];

        $id = $request->get("career_id");
        $career = CareerV2::find($id);
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        foreach($request->all() as $key => $value){
            if(in_array($key,$fillable)){
                $career->$key = $request->$key;
            }
        }
        $career->updated_at = Date('Y-m-d H:i:s');

        try{
            $career->save();
            return ["success" => true, "message" => "Career Berhasil Diubah", "id" => $career->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
        
    }

    public function deleteCareer(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "career_id" => "required|exists:career_v2,id",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->get("career_id");
        $career = CareerV2::find($id);
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
            $career->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }

    public function getCountCareerPosted(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $career = DB::table('career_v2')->selectRaw('is_posted, count(*) as total')->groupBy('is_posted')->get();
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }


}