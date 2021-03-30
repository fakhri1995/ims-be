<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Role;
use App\RoleGroupPivot;
use Exception;

class RoleController extends Controller
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

    public function getRoles(Request $request)
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
            $role = Role::all();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $role]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRole(Request $request)
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
            $id = $request->get('id', null);
            $role = Role::find($id);
            if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            $role_group = RoleGroupPivot::where('role_id', $id)->pluck('group_id');
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["role_detail" => $role, "role_group" => $role_group]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addRole(Request $request)
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
        $role = new Role;
        $role->name = $request->get('name');
        $group_ids = $request->get('group_ids',[]);
        try{
            $role->save();

            $role_id = $role->id;
            $group_ids = array_unique($group_ids);
            foreach($group_ids as $group_id){
                $pivot = new RoleGroupPivot;
                $pivot->role_id = $role_id;
                $pivot->group_id = $group_id;
                $pivot->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateRole(Request $request)
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
        $role = Role::find($id);
        if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $role->name = $request->get('name');
        $group_ids = $request->get('group_ids',[]);
        try{
            $role->save();

            $role_group_ids = RoleGroupPivot::where('role_id', $id)->pluck('group_id')->toArray();
            if(!count($role_group_ids)) {
                $group_ids = array_unique($group_ids);
                foreach($group_ids as $group_id){
                    $pivot = new RoleGroupPivot;
                    $pivot->role_id = $id;
                    $pivot->group_id = $group_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($group_ids, $role_group_ids);
                $difference_array_delete = array_diff($role_group_ids, $group_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $group_id){
                    $pivot = new RoleGroupPivot;
                    $pivot->role_id = $id;
                    $pivot->group_id = $group_id;
                    $pivot->save();
                }
                $role = RoleGroupPivot::where('role_id', $id)->get();
                foreach($difference_array_delete as $group_id){
                    $group_role = $role->where('group_id', $group_id)->first();
                    $group_role->delete();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteRole(Request $request)
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
        $role = Role::find($id);
        if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $role->delete();
            $role_group = RoleGroupPivot::where('role_id', $id)->get();
            foreach($role_group as $group){
                $group->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }   
}