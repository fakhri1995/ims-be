<?php 

namespace App\Services;

use App\Blog;
use App\Product;
use Illuminate\Http\Request;
use App\Services\GlobalService;
use App\Message;
use App\Career;
use App\FormSolution;
use App\FormSolutionDetail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyProfileService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    // Message
    public function getMessages(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $messages = Message::get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Message Belum Terdaftar", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addMessage(Request $request, $route_name)
    {
        $message = new Message;
        $message->name = $request->get('name');
        $message->company_email = $request->get('company_email');
        $message->company_name = $request->get('company_name');
        $message->interested_in = $request->get('interested_in');
        $message->message = $request->get('message');
        $message->phone_number = $request->get('phone_number');
        try{
            $message->save();
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteMessage(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $career = Message::find($id);
        if($career === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $career->delete();
            return ["success" => true, "message" => "Message Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Career
    public function getCareers(Request $request, $route_name)
    {
        try{
            $careers = Career::get();
            if($careers->isEmpty()) return ["success" => false, "message" => "Career Belum Terdaftar", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $careers, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCareer(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $career = new Career;
        $career->position_name = $request->get('position_name');
        $career->job_description = $request->get('job_description');
        $career->job_category = $request->get('job_category');
        $career->register_link = $request->get('register_link');
        try{
            $career->save();
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateCareer(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $career = Career::find($id);
        if($career === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $career->position_name = $request->get('position_name');
        $career->job_description = $request->get('job_description');
        $career->job_category = $request->get('job_category');
        $career->register_link = $request->get('register_link');
        try{
            $career->save();
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteCareer(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $career = Career::find($id);
        if($career === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $career->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addFormSolution(Request $request, $route_name)
    {
        $message = new FormSolution;
        $message->company_name = $request->company_name;
        $message->contact_name = $request->contact_name;
        $message->email = $request->company_email;
        $message->phone_number = $request->phone_number;
        $message->kind_project = $request->kind_project;
        $message->type_project = $request->type_project;
        $message->purpose = $request->purpose;
        $message->budget_from = $request->budget_from;
        $message->budget_to = $request->budget_to;
        $message->meeting_schedule = $request->meeting_schedule;
        $message->kind_form = $request->kind_form;
        
        try{
            $message->save();
            if(method_exists($request,'hasFile') && $request->hasFile('attachment')) {
                $fileService = new FileService;
                $file = $request->file('attachment');
                $table = 'App\FormSolution';
                $description = 'attachment_software';
                $folder_detail = 'FormSolution';
                
                $add_file_response = $fileService->addFile($message->id, $file, $table, $description, $folder_detail);
            }
           
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addFormSolutionTalents(Request $request, $route_name)
    {
        $message = new FormSolution;
        $message->company_name = $request->company_name;
        $message->contact_name = $request->getcontact_name;
        $message->email = $request->company_email;
        $message->phone_number = $request->phone_number;
        $message->kind_project = $request->kind_project;
        $message->meeting_schedule = $request->meeting_schedule;
        $message->kind_form = $request->kind_form;
        $message->many_people = $request->many_people;
        
        try{
            $message->save();
            $talent_list =$request->get('talent_list');
            
             for ($a = 0; $a < count($request->talent_list); $a++) {
                $data_talent = $request->talent_list[$a];
                $product_list="";
                for($b=0;$b<count($data_talent['product']);$b++) {
                    $data_product = $data_talent['product'][$b];
                    if($b==(count($data_talent['product'])-1)) {
                        $product_list=$product_list.$data_product;
                    }
                    else {
                        $product_list=$product_list.$data_product . ",";
                    }
                }
                $data_save[] = [
                    'kind_of_product' => $data_talent['kindOfTalent'],
                    'list_product' => $product_list,
                    'level_employee' => $data_talent['levelEmployee'],
                    'many_product' => $data_talent['manyTalent'],
                    'urgently' => $data_talent['urgently'],
                    'time_used' => $data_talent['timeUsed'],
                    'open_remote' => $data_talent['openRemote'],
                    'maximum_budget' => $data_talent['maxBudget'],
                    'details' => $data_talent['details'],
                    'form_solution_id' => $message->id,
                ];
             }
            FormSolutionDetail::insert($data_save);
           
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addFormSolutionHardware(Request $request, $route_name)
    {
        $message = new FormSolution;
        $message->company_name = $request->company_name;
        $message->contact_name = $request->contact_name;
        $message->email = $request->company_email;
        $message->phone_number = $request->phone_number;
        $message->purpose = $request->purpose;
        $message->meeting_schedule = $request->meeting_schedule;
        $message->kind_form = $request->kind_form;
        $talent_list =$request->get('hardware_list');
        try{
            $message->save();
            $talent_list =$request->get('hardware_list');
            
            
             for ($a = 0; $a < count($request->hardware_list); $a++) {
                $hardware=$request->hardware_list[$a];
                $hardware_detail = new FormSolutionDetail;
                $product_list="";
                for($b=0;$b<count($hardware['product']);$b++) {
                    $data_product = $hardware['product'][$b];
                    if($b==(count($hardware['product'])-1)) {
                        $product_list=$product_list.$data_product;
                    }
                    else {
                        $product_list=$product_list.$data_product . ",";
                    }
                }
                $hardware_detail->kind_of_product = $hardware['kind_of_product'];
                $hardware_detail->list_product = $product_list;
                $hardware_detail->many_product = $hardware['manyTalent'];
                $hardware_detail->urgently = $hardware['urgently'];
                $hardware_detail->time_used = $hardware['timeUsed'];
                $hardware_detail->maximum_budget = $hardware['maxBudget'];
                $hardware_detail->details = $hardware['details'];
                $hardware_detail->form_solution_id = $message->id;
                $hardware_detail->save();
                $fileService = new FileService;
                if($hardware['attachment']!=null) {
                    $file = $hardware['attachment'];
                    $table = 'App\FormSolution';
                    $description = 'attachment_hardware';
                    $folder_detail = 'FormSolution';
                    
                    $add_file_response = $fileService->addFile($hardware_detail->id, $file, $table, $description, $folder_detail);
                }
                
                // $file = $request->file('contract_file',NULL);
                
                
             }
            // FormSolutionDetail::insert($data_save);
           
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getFormSolution(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $messages = FormSolution::get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Message Belum Terdaftar", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    public function getFormSolutionDetail($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $id = $request->id;
            $employee = FormSolution::with("details")->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addArticle(Request $request, $route_name)
    {
        $message = new Blog;
        Log::debug($request);
        Log::debug($request->page_path);
        $message->title = $request->title;
        $message->description = $request->description;
        
        
        $message->tags = $request->tags;
        $message->company_name = $request->company_name;
        $message->quote = $request->quote;
        $message->author = $request->author;
        $message->job_title = $request->job_title;
        $message->meta_title = $request->meta_title;
        $message->meta_description = $request->meta_description;
        $message->title_id = $request->title_id;
        $message->description_id = $request->description_id;
        $message->content = $request->content_id;
        $message->page_path = $request->page_path_id;
        $message->quote_id = $request->quote_id;
        $message->tags_id = $request->tags_id;
        $message->job_title_id = $request->job_title_id;
        $message->meta_title_id = $request->meta_title_id;
        $message->meta_description_id = $request->meta_description_id;
        $message->article_type = $request->article_type;
        $message->page_path = $request->page_path;
        $message->content = $request->content;
        
        $message->user_id = auth()->user()->id;
        try{
            $message->save();
            if(method_exists($request,'hasFile') && $request->hasFile('attachment')) {
                $fileService = new FileService;
                $file = $request->file('attachment');
                $table = 'App\Blog';
                $description = 'attachment_article';
                $folder_detail = 'Blog';
                
                $add_file_response = $fileService->addFile($message->id, $file, $table, $description, $folder_detail);
            }
            if(method_exists($request,'hasFile') && $request->hasFile('company_logo')) {
                $fileService = new FileService;
                $file = $request->file('company_logo');
                $table = 'App\Blog';
                $description = 'company_logo';
                $folder_detail = 'Blog';
                
                $add_file_response = $fileService->addFile($message->id, $file, $table, $description, $folder_detail);
            }
           
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTestimonialLandingPage(Request $request, $route_name)
    {
        
        try{
            $messages = Blog::with(["attachment_article","company_logo"])->where('article_type', '=', 'Customer Stories')->skip(0)->take(6)->get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Data Belum Ada", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    public function getArticleList(Request $request, $route_name)
    {
        
        try{
            $messages = Blog::with('attachment_article')->where('article_type', '=', 'Blog')->get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Data Belum Ada", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    public function getArticlePopularList(Request $request, $route_name)
    {
        
        try{
            $messages = Blog::with('attachment_article')->where('article_type', '=', 'Blog')->skip(0)->take(4)->get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Data Belum Ada", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getArticle(Request $request, $route_name)
    {
        
        try{
            $messages = Blog::with('attachment_article')->get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Data Belum Ada", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getArticleDetail($request, $route_name)
    {

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $id = $request->id;
            $employee = Blog::with(['attachment_article','company_logo'])->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getArticleDetailLanding($request, $route_name)
    {

        $validator = Validator::make($request->all(), [
            "page_path" => "required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $page_path = $request->page_path;
            $employee = Blog::with(['attachment_article','company_logo'])->where('page_path', '=', $page_path)->get();
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    public function getTestimonialDetail($request, $route_name)
    {

        $validator = Validator::make($request->all(), [
            "pagepath" => "required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $pagepath = $request->pagepath;
            Log::info($request);
            $employee = Blog::with('attachment_article')->where('page_path','=',$pagepath)->get();
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    

    public function deleteArticle($request, $route_name)
    {

        $id = $request->id;
        $employee = Blog::find($id);
        if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
            $employee->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateArticle($request,$route_name) 
    {
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
            $id = $request->id;
            $article = Blog::with('attachment_article')->find($id);
            if(!$article) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $article->title = $request->title ?? NULL;
            $article->description = $request->description ?? NULL;
            $article->tags = $request->tags ?? NULL;
            $article->company_name = $request->company_name ?? NULL;
            $article->quote = $request->quote ?? NULL;
            $article->author = $request->author ?? NULL;
            $article->job_title = $request->job_title ?? NULL;
            $article->meta_title = $request->meta_title ?? NULL;
            $article->meta_description = $request->meta_description ?? NULL;
            $article->title_id = $request->title_id ?? NULL;
            $article->description_id = $request->description_id ?? NULL;
            $article->content = $request->content_id ?? NULL;
            $article->page_path = $request->page_path_id ?? NULL;
            $article->quote_id = $request->quote_id ?? NULL;
            $article->tags_id = $request->tags_id ?? NULL;
            $article->job_title_id = $request->job_title_id ?? NULL;
            $article->meta_title_id = $request->meta_title_id ?? NULL;
            $article->meta_description_id = $request->meta_description_id ?? NULL;
            $article->article_type = $request->article_type ?? NULL;
            $article->page_path = $request->page_path ?? NULL;
            $article->content = $request->content ?? NULL;
            $article->save();

            $file = $request->file('attachment',NULL);
            $file2 = $request->file('company_logo',NULL);
            if($file){
                $old_file_id = $article->attachment_article->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($article->id, $file, 'App\Blog', 'attachment_article', 'Blog', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }
            if($file2){
                $old_file_id = $article->company_logo->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($article->id, $file, 'App\Blog', 'company_logo', 'Blog', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }
            
            return ["success" => true, "message" => "Data Berhasil Diupdate", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addProduct(Request $request, $route_name)
    {
        $product = new Product;
        $product->name_product = $request->name_product;
        $product->category_product_id = $request->category_product_id;
        try{
            $product->save();
            if(method_exists($request,'hasFile') && $request->hasFile('attachment_product')) {
                $fileService = new FileService;
                $file = $request->file('attachment_product');
                $table = 'App\Product';
                $description = 'attachment_product';
                $folder_detail = 'Product';
                
                $add_file_response = $fileService->addFile($product->id, $file, $table, $description, $folder_detail);
            }
           
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getProduct(Request $request, $route_name)
    {
        
        try{
            $messages = Product::with('attachment_product')->get();
            if($messages->isEmpty()) return ["success" => false, "message" => "Data Belum Ada", "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getProductDetail($request, $route_name)
    {

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

            $id = $request->id;
            $employee = Product::with('attachment_product')->find($id);
            if(!$employee) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];


        try{

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $employee, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteProduct($request, $route_name)
    {

        $id = $request->id;
        $product = Product::find($id);
        if(!$product) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try{
            $product->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $product, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateProduct($request,$route_name) 
    {
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        
            $id = $request->id;
            $product = Product::with('attachment_product')->find($id);
            if(!$product) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $product->name_product = $request->name_product ?? NULL;
            $product->category_product_id = $request->category_product_id ?? NULL;
            $product->save();

            $file = $request->file('attachment_product',NULL);
            if($file){
                $old_file_id = $product->attachment_product->id ?? NULL;
                $fileService = new FileService;
                $add_file_response = $fileService->addFile($product->id, $file, 'App\Product', 'attachment_product', 'Product', false);
                if($add_file_response['success'] && $old_file_id) {
                    $fileService->deleteForceFile($old_file_id);
                }
            }
            
            return ["success" => true, "message" => "Data Berhasil Diupdate", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    
}