<?php 

namespace App\Services;

use App\CareerV2;
use App\CareerV2Apply;
use App\CareerV2ApplyStatus;
use App\Exports\CareerApplicantsExport;
use App\File;
use App\Mail\CareerApplyMail;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class CareerV2ApplyService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->fileService = new FileService;
        $this->table = 'App\CareerV2Apply';
        $this->folder_detail = 'CareerV2Apply';
    }
    
    private function addResume($id, $file, $description)
    {
        $add_file_response = $this->fileService->addFile($id, $file, $this->table, $description, $this->folder_detail, false);
        return $add_file_response;
    }

    public function getCareerApply(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "career_apply_id" => "required|exists:career_v2_applies,id"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        
        $id = $request->get("career_apply_id");
        $career = CareerV2Apply::with(["resume"])->find($id);
        if(!$career) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        // $career->experience->makeHidden("id");
        // $career->roleType->makeHidden("id");
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
            "limit" => "numeric|in:5,10,25,50",
            "career_id" => "filled|numeric|exists:career_v2,id",
            "date_from" => "date",
            "date_to" => "date",
            "sort" => "in:apply_date,apply_status",
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
        $career_id = $request->career_id ? $request->career_id : NULL;
        $date_from = $request->date_from ? $request->date_from : NULL;
        $date_to = $request->date_to ? $request->date_to : NULL;
        $limit = $request->limit ? $request->limit : 5;
        $career_apply_status_id = isset($request->career_apply_status_id) ? $request->career_apply_status_id : NULL;
        
        $careerApply = CareerV2Apply::with(["resume","role","role.experience","role.roleType","status"]);
        if($career_id) $careerApply = $careerApply->where('career_id',$career_id);
        if($date_from) $careerApply = $careerApply->where("created_at", ">=", $date_from);
        if($date_to) $careerApply = $careerApply->where("created_at", "<=", $date_to);
        if($search) $careerApply = $careerApply->where(function($q) use ($search){
            $q->where("name","LIKE", "%$search%")
            ->orWhere("email","LIKE", "%$search%")
            ->orWhere("phone","LIKE", "%$search%");
        });
        if($career_apply_status_id != NULL) $careerApply = $careerApply->where("career_apply_status_id", $career_apply_status_id);

        // sort
        $order = $request->get('order','asc');
        $sort = $request->sort ? $request->sort : NULL;
        if($sort == "apply_date") $careerApply = $careerApply->orderBy('created_at',$order);
        if($sort == "apply_status") $careerApply = $careerApply->orderBy('career_apply_status_id',$order);

        $careerApply = $careerApply->paginate($limit);
        if($careerApply->count() == 0){
            return ["success" => true, "message" => "Data Tidak Tersedia", "data" => $careerApply, "status" => 200];
        }
        $careerApplyCount = count($careerApply);
        for($i=0;$i<$careerApplyCount;$i++){
            $careerApply[$i]->role->makeHidden(["id","created_at","created_by","updated_at"]);
            $careerApply[$i]->role->experience->makeHidden("id");
            $careerApply[$i]->role->roleType->makeHidden("id");    
            $careerApply[$i]->status->makeHidden(["id","display_order"]);    
        }
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careerApply, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCareerApply(Request $request, $route_name){
        $auth = $request->headers->get('Authorization',NULL);
        $request->headers->set('Authorization', "Bearer ".$auth);
        if(!isset(auth()->user()->id) && $auth){
            try{
                $validate = $this->globalService->validateGoogleReCaptcha($request->captcha);
                if(!$validate["success"]){
                    return ["success" => false, "message" => "Error captcha validation", "data" => $validate["error-codes"], "status" => 400];
                }
                
            }catch(Exception $err){
                return ["success" => false, "message" => $err, "status" => 400];
            }
        }

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email",
            "phone" => "required|numeric",
            "career_id" => "required|exists:career_v2,id|numeric",
            "resume" => "mimes:pdf|mimetypes:application/pdf|file|max:5120"
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try{
            $file = $request->file('resume');
            $current_timestamp = date('Y-m-d H:i:s');

            $career = CareerV2::find($request->career_id);
            if(!$career) return ["success" => true, "message" => "Data Tidak Tersedia", "status" => 200];

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
            $addResume = $this->addResume($careerApply->id, $file, "resume");

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
            "career_apply_id" => "required|exists:career_v2_applies,id",
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

            $id = $request->get("career_apply_id");
            $careerApply = CareerV2Apply::find($id);
            if(!$careerApply) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 4];

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
                $deleteResume = $this->fileService->deleteFile($oldFile->id);
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
            "career_apply_id" => "required|exists:career_v2_applies,id",
        ]);
        
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try{
            $id = $request->get("career_apply_id");
            $careerApply = CareerV2Apply::find($id);
            if(!$careerApply) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        
            $oldFile = File::where(['fileable_id' => $careerApply->id, 'fileable_type' => $this->table])->first(); //using first() because resume just single file
            $deleteResume = $this->fileService->deleteFile($oldFile->id);
            $careerApply->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $careerApply, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getCountCareerApplicant($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $career_id = $request->career_id ? $request->career_id : NULL;
        $is_all = true;
        if($career_id == NULL || $career_id != "all"){
            $validator = Validator::make($request->all(), [
                "career_id" => "required|exists:career_v2,id"
            ]);

            if($validator->fails()){
                $errors = $validator->errors()->all();
                return ["success" => false, "message" => $errors, "status" => 400];
            }
            $is_all = false;
        }
        

        $career_id = $request->get("career_id");
        $ca = "career_v2_applies";
        $cas = "career_v2_apply_statuses";
        $data = CareerV2Apply::rightJoin("$cas","$cas.id","=","$ca.career_apply_status_id")
            ->selectRaw("count(*) as total, $cas.name as status")->groupBy('career_apply_status_id');
        if(!$is_all) $data = $data->where(["career_id" => $career_id]);
        $data = $data->get();
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
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
        
        $limit = $request->limit ? $request->limit : 5;
        
        $career = CareerV2::select('id','name')->withCount('apply')
            ->orderBy('apply_count','desc')->get();
        $ca = "career_v2_applies";
        $cas = "career_v2_apply_statuses";
        foreach($career as $c){
            $apply_details = CareerV2Apply::rightJoin("$cas","$cas.id","=","$ca.career_apply_status_id")
            ->selectRaw("count(*) as total, $cas.name as status")->groupBy('career_apply_status_id')->where(["career_id" => $c->id])->get();
            $c->apply_details = $apply_details;
        }

        
        try{
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $career, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function reCaptcha(Request $request, $route_name){
        
        
        $data = $this->globalService->validateGoogleReCaptcha($request->captcha);
        
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
            "date_from" => "date",
            "date_to" => "date",
            "career_apply_status_id" => "array|exists:career_v2_apply_statuses,id",
            "column" => "array|in:0,1|min:4|max:4",
            "column.*" => "boolean"
        ];

        // column filled by boolean array ['1','1','1','1']
        // boolean for select filter column -> ['name','email','phone','created_at']

        if($request->date_to && $request->date_from){
            $rules["date_from"] = "date|before:date_to";
            $rules["date_to"] = "date|after:date_from";
        }

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $career_id = $request->career_id ? $request->career_id : NULL;
        $date_from = $request->date_from ? $request->date_from : NULL;
        $date_to = $request->date_to ? $request->date_to : NULL;
        $career_apply_status_id =  $request->career_apply_status_id ? $request->career_apply_status_id : NULL;
        $column = $request->column ? $request->column : ['1','1','1','1'];

        $careerApply = CareerV2Apply::select(['id','name','email','phone','created_at','career_id','career_apply_status_id'])->with(["status"]);
        if($career_id) $careerApply = $careerApply->where('career_id',$career_id);
        if($date_from) $careerApply = $careerApply->where("created_at", ">=", $date_from);
        if($date_to) $careerApply = $careerApply->where("created_at", "<=", $date_to);
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

}