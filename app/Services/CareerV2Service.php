<?php 

namespace App\Services;
use App\CareerV2;
use App\CareerV2Apply;
use App\CareerV2ApplyStatus;
use App\CareerV2Experience;
use App\CareerV2Question;
use App\CareerV2RoleType;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CareerV2Service{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    public function getCareer(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required_without:slug|exists:career_v2,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id ?? NULL;
        $slug = $request->slug ?? NULL;
        
        if($id) $career = CareerV2::with(["roleType","experience", "question"])->find($id);
        else $career = CareerV2::with(["roleType","experience", "question"])->where("slug",$slug)->first();
        
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
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
            "rows" => "numeric|between:1,100",
            "from" => "date",
            "to" => "date",
            "sort_by" => "in:name,role_type,experience,created_at,is_posted,total",
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

        $keyword = $request->keyword ?? NULL;
        $role_type_id = $request->role_type_id ? explode(",",$request->role_type_id) : NULL;
        $experience_id = $request->experience_id ? explode(",",$request->experience_id) : NULL;
        $from = $request->from ?? NULL;
        $to = $request->to ?? NULL;
        $is_posted = isset($request->is_posted) ? $request->is_posted : NULL;
        
        $rows = $request->rows ?? 5;
        $career = CareerV2::with(["roleType" ,"experience", "question"])->withCount("apply");
        
        // filter
        if($keyword) $career = $career->where("name","LIKE", "%$keyword%");
        if($role_type_id) $career = $career->whereIn("career_role_type_id", $role_type_id);
        if($experience_id) $career = $career->whereIn("career_experience_id", $experience_id);
        if($from) $career = $career->where("created_at", ">=", $from);
        if($to) $career = $career->where("created_at", "<=", $to);
        if($is_posted != NULL) $career = $career->where("is_posted", $is_posted);
        
        // sort_by
        $sort_type = $request->sort_type == 'desc' ? 'desc' : 'asc';
        $sort_by = $request->sort_by ?? NULL;
        if($sort_by == "name") $career = $career->orderBy('name',$sort_type);
        if($sort_by == "created_at") $career = $career->orderBy('created_at',$sort_type);
        if($sort_by == "is_posted") $career = $career->orderBy('is_posted',$sort_type);
        if($sort_by == "total") $career = $career->orderBy('apply_count',$sort_type);
        if($sort_by == "experience") $career = $career->orderBy(CareerV2Experience::select('min')->whereColumn('career_v2_experiences.id', 'career_v2.career_experience_id'),$sort_type);
        if($sort_by == "role_type") $career = $career->orderBy(CareerV2RoleType::select('name')->whereColumn('career_v2_role_types.id', 'career_v2.career_role_type_id'),$sort_type);




        $career = $career->paginate($rows);
        
        if($career->count() == 0){
            return ["success" => true, "message" => "Data Tidak Tersedia", "data" => $career, "status" => 200];
        }

        $careerCount = count($career);
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPostedCareers($request, $route_name){

        $rules = [
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            "from" => "date",
            "to" => "date",
            "sort_by" => "in:name,role_type,experience,created_at,is_posted,total",
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

        $keyword = $request->keyword ?? NULL;
        $role_type_id = $request->role_type_id ? explode(",",$request->role_type_id) : NULL;
        $experience_id = $request->experience_id ? explode(",",$request->experience_id) : NULL;
        $from = $request->from ?? NULL;
        $to = $request->to ?? NULL;
        $is_posted = 1;
        
        $rows = $request->rows ?? 5;
        $career = CareerV2::with(["roleType" ,"experience","question"])->withCount("apply");
        
        // filter
        if($keyword) $career = $career->where("name","LIKE", "%$keyword%");
        if($role_type_id) $career = $career->whereIn("career_role_type_id", $role_type_id);
        if($experience_id) $career = $career->whereIn("career_experience_id", $experience_id);
        if($from) $career = $career->where("created_at", ">=", $from);
        if($to) $career = $career->where("created_at", "<=", $to);
        if($is_posted != NULL) $career = $career->where("is_posted", $is_posted);
        
        // sort_by
        $sort_type = $request->sort_type ?? 'asc';
        $sort_by = $request->sort_by ?? NULL;
        if($sort_by == "name") $career = $career->orderBy('name',$sort_type);
        if($sort_by == "created_at") $career = $career->orderBy('created_at',$sort_type);
        if($sort_by == "is_posted") $career = $career->orderBy('is_posted',$sort_type);
        if($sort_by == "total") $career = $career->orderBy('apply_count',$sort_type);
        if($sort_by == "experience") $career = $career->orderBy(CareerV2Experience::select('min')->whereColumn('career_v2_experiences.id', 'career_v2.career_experience_id'),$sort_type);
        if($sort_by == "role_type") $career = $career->orderBy(CareerV2RoleType::select('name')->whereColumn('career_v2_role_types.id', 'career_v2.career_role_type_id'),$sort_type);




        $career = $career->paginate($rows);
        if($career->count() == 0){
            return ["success" => true, "message" => "Data Tidak Tersedia", "data" => $career, "status" => 200];
        }

        $careerCount = count($career);
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPostedCareer(Request $request, $route_name){
        $validator = Validator::make($request->all(), [
            "id" => "required_without:slug|exists:career_v2,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        
        $id = $request->id ?? NULL;
        $slug = $request->slug ?? NULL;
        if($id) $career = CareerV2::with(["roleType","experience"])->where(['id' => $id,'is_posted' => 1])->first();
        else $career = CareerV2::with(["roleType","experience"])->where(['slug' => $slug,'is_posted' => 1])->first();

        
        
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTopFiveCareers(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $careers = CareerV2::select('id', 'name')->withCount('apply')->orderBy('apply_count', 'desc')->take(5)->get();

        $career_ids = $careers->pluck('id');
        $status_count = [];
        foreach($career_ids as $id){
            $status_count[] = CareerV2ApplyStatus::select('name')->withCount(['applicants as applicants_count' => function($q) use($id){
                $q->where('career_id', $id);
            }])->get();
        }
        $data = [
            "careers" => $careers,
            "status_count" => $status_count
        ];
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
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
            "is_posted" => "required|boolean",
            "qualification" => "required",
            "question" => "array"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $random = random_int(0000,9999);


        $career = new CareerV2();
        $career->name = $request->name;
        $career->slug = Str::slug($request->name, '-').'-'.$random;
        $career->career_role_type_id = $request->career_role_type_id;
        $career->career_experience_id = $request->career_experience_id;
        $career->salary_min = $request->salary_min;
        $career->salary_max = $request->salary_max;
        $career->overview = $request->overview;
        $career->description = $request->description;
        $career->is_posted = $request->is_posted;
        $career->qualification = $request->qualification;
        $career->created_at = Date('Y-m-d H:i:s');
        $career->updated_at = Date('Y-m-d H:i:s');
        $career->created_by = auth()->user()->id;
        $question = $request->question ?? NULL;
        try{
            $career->save();
            if($question){
                $questions = (object)$question;
                $this->addCareerQuestion($career->id, $questions->name, $questions->description, $questions->details);
            }
            return ["success" => true, "message" => "Career Berhasil Ditambahkan", "id" => $career->id, "status" => 201];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateCareer(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:career_v2,id",
            "name" => "filled",
            "career_role_type_id" => "exists:career_v2_role_types,id|numeric",
            "career_experience_id" => "exists:career_v2_experiences,id|numeric",
            "salary_min" => "filled|numeric",
            "salary_max" => "filled|numeric",
            "overview" => "filled",
            "description" => "filled",
            "is_posted" => "filled|boolean",
            "qualification" => "filled"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $fillable = ["name","career_role_type_id","career_experience_id","salary_min","salary_max", "overview","description","is_posted","qualification"];

        $id = $request->id;
        $career = CareerV2::find($id);
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        foreach($request->all() as $key => $value){
            if(in_array($key,$fillable)){
                $career->$key = $request->$key;
            }
        }
        $career->updated_at = Date('Y-m-d H:i:s');
        
        if($request->name ?? NULL){
            $random = random_int(0000,9999);
            $career->slug = Str::slug($request->name, '-').'-'.$random;
        } 

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
            "id" => "required|exists:career_v2,id",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
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


    public function getCareerApplyStatuses(Request $request, $route_name){
        $careerApplyStatuses = CareerV2ApplyStatus::withCount('applicants')->orderBy('display_order')->get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careerApplyStatuses, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCareerExperiences(Request $request, $route_name){
        $careerExperiences = CareerV2Experience::orderBy('min')->get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careerExperiences, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCareerRoleTypes(Request $request, $route_name){
        $careerRoleTypes = CareerV2RoleType::get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careerRoleTypes, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCareerQuestion($career_id, $name, $description, $details)
    {
        $career_question = new CareerV2Question;
        $career_question->career_id = $career_id;
        $career_question->name = $name;
        $career_question->description = $description;
        $career_question->updated_at = date('Y-m-d H:i:s');
        $career_question->created_by = auth()->user()->id;
        $details = $details;
        try{
            $i = 1;
            if(count($details)){
                foreach($details as &$detail){
                    if(!isset($detail['required'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada required", "status" => 400];
                    if(gettype($detail['required']) !== "boolean") return ["success" => false, "message" => "Detail pertanyaan $i pada required harus bertipe boolean", "status" => 400];
                    if(!isset($detail['name'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada name", "status" => 400];
                    if(gettype($detail['name']) !== "string") return ["success" => false, "message" => "Detail pertanyaan $i pada name harus bertipe string", "status" => 400];
                    if(!isset($detail['description'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada description", "status" => 400];
                    if(gettype($detail['description']) !== "string") return ["success" => false, "message" => "Detail pertanyaan $i pada description harus bertipe string", "status" => 400];
                    if(!isset($detail['type'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada type", "status" => 400];
                    if(gettype($detail['type']) !== "integer") return ["success" => false, "message" => "Detail pertanyaan $i pada type harus bertipe integer", "status" => 400];
                    if(in_array($detail['type'], [3,5])){
                        if(!isset($detail['list'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada list", "status" => 400];
                        if(gettype($detail['list']) !== "array") return ["success" => false, "message" => "Detail pertanyaan $i pada list harus bertipe array", "status" => 400];
                    }
                    $detail['key'] = Str::uuid()->toString();
                    $i++;
                }
            }
            $career_question->details = $details;
            $career_question->save();
            return ["success" => true, "message" => "Career Question Berhasil Ditambahkan", "id" => $career_question->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateCareerQuestion($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $career_question = CareerV2Question::find($id);
        if($career_question === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $career_question->name = $request->get('name');
        $career_question->description = $request->get('description');
        $career_question->updated_at = date('Y-m-d H:i:s');
        $details = $request->details;
        try{
            $i = 1;
            if(count($details)){
                foreach($details as &$detail){
                    if(!isset($detail['required'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada required", "status" => 400];
                    if(gettype($detail['required']) !== "boolean") return ["success" => false, "message" => "Detail pertanyaan $i pada required harus bertipe boolean", "status" => 400];
                    if(!isset($detail['name'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada name", "status" => 400];
                    if(gettype($detail['name']) !== "string") return ["success" => false, "message" => "Detail pertanyaan $i pada name harus bertipe string", "status" => 400];
                    if(!isset($detail['description'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada description", "status" => 400];
                    if(gettype($detail['description']) !== "string") return ["success" => false, "message" => "Detail pertanyaan $i pada description harus bertipe string", "status" => 400];
                    if(!isset($detail['type'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada type", "status" => 400];
                    if(gettype($detail['type']) !== "integer") return ["success" => false, "message" => "Detail pertanyaan $i pada type harus bertipe integer", "status" => 400];
                    if(in_array($detail['type'], [3,5])){
                        if(!isset($detail['list'])) return ["success" => false, "message" => "Detail pertanyaan $i masih kosong pada list", "status" => 400];
                        if(gettype($detail['list']) !== "array") return ["success" => false, "message" => "Detail pertanyaan $i pada list harus bertipe array", "status" => 400];
                    }
                    $detail['key'] = Str::uuid()->toString();
                    $i++;
                }
            }
            $career_question->details = $details;

            $career_question->save();
            return ["success" => true, "message" => "Career Question Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteCareerQuestion($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $career_question = CareerV2Question::find($id);
        if($career_question === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];

        try{
            $career_question->delete();
            return ["success" => true, "message" => "Career Question berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}