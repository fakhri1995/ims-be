<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\AccessFeature;
use App\Message;
use App\Career;
use Exception;

class CompanyProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function checkRoute($name, $auth)
    {
        $protocol = $name;
        $access_feature = AccessFeature::where('name',$protocol)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
                    "server_code" => 400,
                    "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
                ]
            ]];
        }
        $body = [
            'path_url' => $access_feature->feature_key
        ];
        $headers = [
            'Authorization' => $auth,
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/validate-feature', [
                    'headers'  => $headers,
                    'json' => $body
            ]);
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            $log_user_id = $response['data']['user_id'];
            return ["success" => true, "id" => $log_user_id];
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]];
        }
    }

    // Message
    public function getMessages(Request $request)
    {
        $check = $this->checkRoute("MESSAGES_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $messages = Message::get();
            if($messages->isEmpty()) return response()->json(["success" => false, "message" => "Message Belum Terdaftar"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $messages]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addMessage(Request $request)
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
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteMessage(Request $request)
    {
        $check = $this->checkRoute("CAREER_DELETE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $career = Message::find($id);
        if($career === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $career->delete();
            return response()->json(["success" => true, "message" => "Message Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // Career
    public function getCareers(Request $request)
    {
        try{
            $careers = Career::get();
            if($careers->isEmpty()) return response()->json(["success" => false, "message" => "Career Belum Terdaftar"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $careers]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addCareer(Request $request)
    {
        $check = $this->checkRoute("CAREER_ADD", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $career = new Career;
        $career->position_name = $request->get('position_name');
        $career->job_description = $request->get('job_description');
        $career->job_category = $request->get('job_category');
        $career->register_link = $request->get('register_link');
        try{
            $career->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateCareer(Request $request)
    {
        $check = $this->checkRoute("CAREER_UPDATE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $career = Career::find($id);
        if($career === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $career->position_name = $request->get('position_name');
        $career->job_description = $request->get('job_description');
        $career->job_category = $request->get('job_category');
        $career->register_link = $request->get('register_link');
        try{
            $career->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteCareer(Request $request)
    {
        $check = $this->checkRoute("CAREER_DELETE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $career = Career::find($id);
        if($career === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $career->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}