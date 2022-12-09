<?php 

namespace App\Services;
use Illuminate\Http\Request;
use App\Services\GlobalService;
use App\Message;
use App\Career;
use App\FormSolution;
use App\FormSolutionDetail;
use Exception;
use Illuminate\Support\Facades\Log;

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
        Log::info("oe bro " .$request);
        $message = new FormSolution;
        $message->company_name = $request->company_name;
        $message->contact_name = $request->getcontact_name;
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
                
                // Log::info("hasfile " . $add_file_response);
           
            return ["success" => true, "message" => "Data Berhasil Disimpan", "status" => 200];
        } catch(Exception $err){
            Log::info("error iki bro ".$err);
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addFormSolutionTalents(Request $request, $route_name)
    {
        // Log::info("oe bro " .$request->get('talent_list'));
        // Log::info("oe bro 2" .$request->talent_list);
        // Log::info("oe bro 3" .$request);
        // Log::info("oe bro 2" .$request->talent_list);
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
            // Log::info("bismillah bro ".$talent_list[0]->kindOfTalent);
            
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
            Log::info("error iki bro ".$err);
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    
}