<?php 

namespace App\Services;
use Illuminate\Http\Request;
use App\Services\CheckRouteService;
use App\Message;
use App\Career;
use Exception;

class CompanyProfileService{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }

    // Message
    public function getMessages(Request $request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
        $access = $this->checkRouteService->checkRoute($route_name);
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
}