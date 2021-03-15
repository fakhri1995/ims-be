<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Incident;
use App\Ticket;
use Exception;

class IncidentController extends Controller
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

    // Normal Route

    public function getIncidents(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        try{
            $incidents = Incident::all();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $incidents]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addIncident(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }

        $validator = Validator::make($request->all(), [
            "requester" => "required",
            "associate_asset" => "required",
            "subject" => "required",
            "description" => "required",
            "file" => "file"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }

        try{
            if($request->file('file')){
                $file = $request->file('file')->getClientOriginalName();
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $name = $filename.'_'.time().'.'.$extension;
                // $request->file('file')->move('uploads/incidents', $name);
                Storage::disk('local')->putFileAs('incidents', $request->file('file'), $name);
            } else {
                $name = 'no_file.jpg';
            }

            $incident = new Incident;
            $incident->requester = $request->get('requester');
            $incident->associate_asset = $request->get('associate_asset');
            $incident->subject = $request->get('subject');
            $incident->description = $request->get('description');
            $incident->file = $name;
            $incident->save();

            $ticket = new Ticket;
            $ticket->subject_type_id = $incident->id;
            $ticket->type = "Incident";
            $ticket->status = null;
            $ticket->priority = null;
            $ticket->source = null;
            $ticket->urgency = null;
            $ticket->impact = null;
            $ticket->due_to = null;
            $ticket->asign_to = null;
            $ticket->save();

            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateIncident(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }

        $validator = Validator::make($request->all(), [
            "requester" => "required",
            "associate_asset" => "required",
            "subject" => "required",
            "description" => "required",
            "file" => "file"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }
        
        $id = $request->get('id', null);
        $incident = Incident::find($id);
        if($incident === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        
        try{
            if($request->file('file')){
                if($incident->file !== 'no_file.jpg'){
                    Storage::disk('local')->delete('incidents/' . $incident->file);
                }
                $file = $request->file('file')->getClientOriginalName();
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $name = $filename.'_'.time().'.'.$extension;
                Storage::disk('local')->putFileAs('incidents', $request->file('file'), $name);
                $incident->file = $name;
            }
            $incident->requester = $request->get('requester');
            $incident->associate_asset = $request->get('associate_asset');
            $incident->subject = $request->get('subject');
            $incident->description = $request->get('description');
            $incident->save();

            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteIncident(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        $id = $request->get('id', null);
        $incident = Incident::find($id);
        if($incident === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            if($incident->file !== 'no_file.jpg') Storage::disk('local')->delete('incidents/' . $incident->file);
            $incident->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }   
}