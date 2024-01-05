<?php 

namespace App\Services;

use App\CareerV2;
use App\CareerV2Apply;
use App\CareerV2ApplyQuestion;
use App\CareerV2ApplyStatus;
use App\CareerV2Question;
use App\Exports\CareerApplicantsExport;
use App\File;
use App\Mail\CareerApplyMail;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class CareerV2ApplyService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->table = 'App\CareerV2Apply';
        $this->folder_detail = 'CareerV2Apply';
    }
    
    private function addResume($id, $file, $description)
    {
        $fileService = new FileService;
        $add_file_response = $fileService->addFile($id, $file, $this->table, $description, $this->folder_detail, false);
        return $add_file_response;
    }

    public function getCareerApply(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|exists:career_v2_applies,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->id;
        $career = CareerV2Apply::with(["resume","role","role.experience","role.roleType","status","question"])->find($id);
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];   
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCareerApplys($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $rules = [
            "page" => "numeric",
            "limit" => "numeric|between:1,100",
            "career_id" => "numeric|exists:career_v2,id|nullable",
            "from" => "date",
            "to" => "date",
            "sort_by" => "in:apply_date,apply_status",
            "sort_type" => "in:asc,desc",
            "has_career" => "numeric|in:0,1|nullable"
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
        $career_id = $request->career_id ?? NULL;
        $from = $request->from ?? NULL;
        $to = $request->to ?? NULL;
        $limit = $request->limit ?? 5;
        $career_apply_status_id = isset($request->career_apply_status_id) ? $request->career_apply_status_id : NULL;
        $has_career = $request->has_career ?? NULL;
        
        $careerApply = CareerV2Apply::with(["resume","role","role.experience","role.roleType","status","question"]);
        if($career_id) $careerApply = $careerApply->where('career_id',$career_id);
        if($from) $careerApply = $careerApply->where("created_at", ">=", $from);
        if($to) $careerApply = $careerApply->where("created_at", "<=", $to);
        if($keyword) $careerApply = $careerApply->where(function($q) use ($keyword){
            $q->where("name","LIKE", "%$keyword%")
            ->orWhere("email","LIKE", "%$keyword%")
            ->orWhere("phone","LIKE", "%$keyword%");
        });
        if($career_apply_status_id != NULL) $careerApply = $careerApply->where("career_apply_status_id", $career_apply_status_id);
        if($has_career !== NULL){
            if($has_career == 1){
                $careerApply = $careerApply->whereNotNull('career_id');
            } else {
                $careerApply = $careerApply->whereNull('career_id');
            }
        }

        // sort_by
        $sort_type = $request->sort_type ?? 'asc';
        $sort_by = $request->sort_by ?? NULL;
        if($sort_by == "apply_date") $careerApply = $careerApply->orderBy('created_at',$sort_type);
        if($sort_by == "apply_status") $careerApply = $careerApply->orderBy('career_apply_status_id',$sort_type);

        $careerApply = $careerApply->paginate($limit);
        if($careerApply->count() == 0){
            return ["success" => true, "message" => "Data Tidak Tersedia", "data" => $careerApply, "status" => 200];
        }
        $careerApplyCount = count($careerApply);
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careerApply, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCareerApply(Request $request, $route_name){
        $auth = $request->headers->get('Authorization',NULL);
        $request->headers->set('Authorization', "Bearer ".$auth);
        $rules = [
            "name" => "required",
            "email" => "required|email",
            "phone" => "required|numeric",
            "career_id" => "required|exists:career_v2,id|numeric",
            "resume" => "required|mimes:pdf|mimetypes:application/pdf|file|max:5120",
        ];
        
        if(!isset(auth()->user()->id) || empty($auth)){
            $rules["g-recaptcha-response"] = "required";
        }

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        if(!isset(auth()->user()->id) || empty($auth)){
            try{
                $gCaptchaResponse = $request->get('g-recaptcha-response', NULL);
                $validate = $this->globalService->validateGoogleReCaptcha($gCaptchaResponse);
                if(!$validate["success"]){
                    return ["success" => false, "message" => "Error captcha validation", "data" => $validate["error-codes"], "status" => 400];
                }
                
            }catch(Exception $err){
                return ["success" => false, "message" => $err, "status" => 400];
            }
        }

        try{
            $file = $request->file('resume',NULL);
            $current_timestamp = date('Y-m-d H:i:s');

            $career = CareerV2::find($request->career_id);
            if(!$career) return ["success" => false, "message" => "Role Tidak Tersedia", "status" => 400];

            $careerApply = new CareerV2Apply();
            $careerApply->name = $request->name;
            $careerApply->email = $request->email;
            $careerApply->phone = $request->phone;
            $careerApply->career_id = $request->career_id;
            $careerApply->career_apply_status_id = 1; //Unprocessed
            $careerApply->created_at = $current_timestamp;
            $careerApply->updated_at = $current_timestamp;
            $careerApply->created_by = isset(auth()->user()->id) ? auth()->user()->id : NULL;
            $careerApply->save();

            if($file) $addResume = $this->addResume($careerApply->id, $file, "resume");

            $data = (object) array(
                'name' => $request->name,
                'role_name' => $career->name,
                'subject' => "Thank you for applying to MIG",
                'url' => env('APP_URL_WEB'),
            );

            $sendMail = Mail::to($careerApply->email)->send(new CareerApplyMail($data));
            return ["success" => true, "message" => "Apply Career Berhasil Ditambahkan", "id" => $careerApply->id, "status" => 201];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateCareerApply(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $validator = Validator::make($request->all(), [
            "id" => "required|exists:career_v2_applies,id",
            "career_id" => "filled|exists:career_v2,id",
            "name" => "filled",
            "email" => "filled|email",
            "phone" => "filled|numeric",
            "resume" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
            "career_apply_status_id" => "filled|exists:career_v2_apply_statuses,id"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try{

            $fillable = ["career_id","name","email","phone","career_apply_status_id"];

            $id = $request->id;
            $careerApply = CareerV2Apply::find($id);
            if(!$careerApply) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

            foreach($request->all() as $key => $value){
                if(in_array($key,$fillable)){
                    $careerApply->$key = $request->$key;
                }
            }

            $careerApply->updated_at = Date('Y-m-d H:i:s');
            $file = $request->file('resume');
            if($file){
                $oldFile = File::where(['fileable_id' => $careerApply->id, 'fileable_type' => $this->table])->first(); //using first() because resume just single file
                $addResume = $this->addResume($careerApply->id, $file, "resume");
                $fileService = new FileService;
                if($oldFile){
                    $deleteResume = $fileService->deleteForceFile($oldFile->id);
                }
            }
            $careerApply->save();
            return ["success" => true, "message" => "Career Apply Berhasil Diubah", "id" => $careerApply->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

    }

    public function deleteCareerApply(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|exists:career_v2_applies,id",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try{
            $id = $request->id;
            $careerApply = CareerV2Apply::find($id);
            if(!$careerApply) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
            $oldFile = File::where(['fileable_id' => $careerApply->id, 'fileable_type' => $this->table])->first(); //using first() because resume just single file
            $fileService = new FileService;
            $deleteResume = $fileService->deleteForceFile($oldFile->id);
            $careerApply->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $careerApply, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCountCareerApplicant($request, $route_name, $is_all = false){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $career_id = $request->career_id ?? NULL;
        if(!$is_all){
            $validator = Validator::make($request->all(), [
                "career_id" => "required|exists:career_v2,id"
            ]);

            if($validator->fails()){
                $errors = $validator->errors()->all();
                return ["success" => false, "message" => $errors, "status" => 400];
            }
            
        }
        
       
        $career_id = $request->career_id;
        $careerV2ApplyStatus = CareerV2ApplyStatus::withCount(['applicants' => function ($query) use ($career_id,$is_all) {
            if(!$is_all) return $query->where('career_id','=',$career_id);
            return $query;
           }]);
        $careerV2ApplyStatus = $careerV2ApplyStatus->get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careerV2ApplyStatus, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getMostCareersApplicant(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "limit" => "filled|numeric",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $career = CareerV2::select('id','name')->withCount(['apply'])->with(['apply' => function ($query){
            return $query->selectRaw("career_id, career_apply_status_id , count(*) as total")->groupBy("career_id","career_apply_status_id");  
        },'apply.status'])->get();
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function reCaptcha(Request $request, $route_name){
        
        
        $data = $this->globalService->validateGoogleReCaptcha($request->get('g-recaptcha-response'));
        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function exportCareersApplicant($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $rules = [
            "career_id" => "filled|numeric|exists:career_v2,id",
            "from" => "date",
            "to" => "date",
            "career_apply_status_id" => "array|exists:career_v2_apply_statuses,id",
            "column" => "array|in:0,1|min:4|max:4",
            "column.*" => "boolean"
        ];

        // column filled by boolean array ['1','1','1','1']
        // boolean for select filter column -> ['name','email','phone','created_at']

        if($request->to && $request->from){
            $rules["from"] = "date|before:to";
            $rules["to"] = "date|after:from";
        }

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $career_id = $request->career_id ?? NULL;
        $from = $request->from ?? NULL;
        $to = $request->to ?? NULL;
        $career_apply_status_id =  $request->career_apply_status_id ?? NULL;
        $column = $request->column ?? ['1','1','1','1'];

        $careerApply = CareerV2Apply::select(['id','name','email','phone','created_at','career_id','career_apply_status_id'])->with(["status"]);
        if($career_id) $careerApply = $careerApply->where('career_id',$career_id);
        if($from) $careerApply = $careerApply->where("created_at", ">=", $from);
        if($to) $careerApply = $careerApply->where("created_at", "<=", $to);
        if($career_apply_status_id) $careerApply = $careerApply->whereIn('career_apply_status_id',$career_apply_status_id);
        
        $careerApply = $careerApply->get();
        if($careerApply->count() == 0){
            return ["success" => false, "message" => "Data Tidak Tersedia", "data" => $careerApply, "status" => 200];
        }
        $filename = $careerApply[0]->role->name;
        try{
            $excel = Excel::download(new CareerApplicantsExport($careerApply, $column), "$filename.xlsx");
            return ["success" => true, "message" => "Data Berhasil Diexport", "data" => $excel, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }

    }

    public function addCareerApplyQuestion($request, $route_name)
    {
        $career_apply_id = $request->get('career_apply_id');
        $career_question_id = $request->get('career_question_id');
        $career_question = CareerV2Question::find($career_question_id);
        if($career_question === null) return ["success" => false, "message" => "Id Question Tidak Ditemukan", "status" => 400];
        $question_details = $request->get('details', []);
        ksort($question_details);
        $fileArray = []; // index => [ "key" : value, "value" : value ]
        foreach($career_question->details as $question_detail){
            $search = array_search($question_detail['key'], array_column($question_details, 'key'));
            if($search === false) return ["success" => false, "message" => "Pertanyaan career dengan nama ".$question_detail['name']." belum diisi" , "status" => 400];
            if($question_detail['type'] === 6){
                $file = $request->file("details.$search.value",NULL);
                $isFile = is_file($file);
                if($question_detail['required'] && !$isFile) return ["success" => false, "message" => "Value pada pertanyaan career dengan nama ".$question_detail['name']." harus bertipe file" , "status" => 400];
                else if($isFile) {
                    $question_details[$search]['value'] = true;
                }
                else $question_details[$search]['value'] = NULL;

                $fileArray[$search] = [
                    "key" => $question_detail['key'],
                    "file" => $file
                ];
            }
            if(!isset($question_details[$search]['value']) && $question_detail['required']) return ["success" => false, "message" => "pertanyaan career dengan nama ".$question_detail['name']." belum memiliki value" , "status" => 400];
            if($question_detail['type'] === 3){
                if(gettype($question_details[$search]['value']) !== "array") return ["success" => false, "message" => "Value pada pertanyaan career dengan nama ".$question_detail['name']." harus bertipe array", "status" => 400];
            } else if($question_detail['type'] !== 6) {
                if(gettype($question_details[$search]['value']) !== "string") return ["success" => false, "message" => "Value pada pertanyaan career dengan nama ".$question_detail['name']." harus bertipe string", "status" => 400];
            }
        }
        $career_apply_question = new CareerV2ApplyQuestion;
        $career_apply_question->apply_id = $career_apply_id;
        $career_apply_question->career_question_id = $career_question_id;
        $career_apply_question->updated_at = date('Y-m-d H:i:s');
        $career_apply_question->details = $question_details;

        try{
            $career_apply_question->save();
            
            $career_apply_question_id = $career_question->id;
            foreach($fileArray as $index => $value){
                if(!$question_details[$index]['value']) continue;
                $uploadFile = $this->addCareerApplyFile($career_apply_question_id, $value['file'], $value['key']);
                if($uploadFile['success']) $question_details[$index]['value'] = $uploadFile['new_data']->link;
            }
            $career_question->details = $question_details;
            $career_question->save();

            return ["success" => true, "message" => "Carrer Apply Question Berhasil Dibuat", "id" => $career_question->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    private function addCareerApplyFile($id, $file, $description)
    {
        $fileService = new FileService;
        $add_file_response = $fileService->addFile($id, $file, 'App\CareerApply', $description, 'CareerApply', false);
        return $add_file_response;
    }

}