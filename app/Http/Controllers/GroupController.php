<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Group;
use App\GroupUserPivot;
use App\AccessFeature;
use Exception;

class GroupController extends Controller
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
            return ["success" => true];
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

    public function getAgentGroups(Request $request)
    {
        // $check = $this->checkRoute("AGENT_GROUPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $group = Group::where('is_agent', 1)->get();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $group]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // AGENT_GROUPS_GET
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
            $group = Group::where('is_agent', 1)->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $group]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getAgentGroup(Request $request)
    {
        // $check = $this->checkRoute("AGENT_GROUP_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $id = $request->get('id', null);
        //     $group = Group::find($id);
        //     if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        //     if($group->is_agent === 0){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //             ]
        //         ]], 401);
        //     }
        //     $group_user = GroupUserPivot::where('group_id', $id)->pluck('user_id');
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["group_detail" => $group, "group_user" => $group_user]]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // AGENT_GROUP_GET
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
            $group = Group::find($id);
            if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            if($group->is_agent === 0){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                    ]
                ]], 401);
            }
            $group_user = GroupUserPivot::where('group_id', $id)->pluck('user_id');
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["group_detail" => $group, "group_user" => $group_user]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addAgentGroup(Request $request)
    {
        // $check = $this->checkRoute("AGENT_GROUP_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $group = new Group;
        // $group->name = $request->get('name');
        // $group->description = $request->get('description');
        // $group->is_agent = 1;
        // $group->group_head = $request->get('group_head');
        // $user_ids = $request->get('user_ids',[]);
        // try{
        //     $group->save();

        //     $group_id = $group->id;
        //     $user_ids = array_unique($user_ids);
        //     foreach($user_ids as $user_id){
        //         $pivot = new GroupUserPivot;
        //         $pivot->group_id = $group_id;
        //         $pivot->user_id = $user_id;
        //         $pivot->save();
        //     }
            
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // AGENT_GROUP_ADD
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
        $group = new Group;
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->is_agent = 1;
        $group->group_head = $request->get('group_head');
        $user_ids = $request->get('user_ids',[]);
        try{
            $group->save();

            $group_id = $group->id;
            $user_ids = array_unique($user_ids);
            foreach($user_ids as $user_id){
                $pivot = new GroupUserPivot;
                $pivot->group_id = $group_id;
                $pivot->user_id = $user_id;
                $pivot->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateAgentGroup(Request $request)
    {
        // $check = $this->checkRoute("AGENT_GROUP_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $group = Group::find($id);
        // if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // if($group->is_agent === 0){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //         ]
        //     ]], 401);
        // }
        // $group->name = $request->get('name');
        // $group->description = $request->get('description');
        // $group->group_head = $request->get('group_head');
        // $user_ids = $request->get('user_ids',[]);
        // try{
        //     $group->save();

        //     $group_user_ids = GroupUserPivot::where('group_id', $id)->pluck('user_id')->toArray();
        //     if(!count($group_user_ids)) {
        //         $user_ids = array_unique($user_ids);
        //         foreach($user_ids as $user_id){
        //             $pivot = new GroupUserPivot;
        //             $pivot->group_id = $id;
        //             $pivot->user_id = $user_id;
        //             $pivot->save();
        //         }
        //     } else {
        //         $difference_array_new = array_diff($user_ids, $group_user_ids);
        //         $difference_array_delete = array_diff($group_user_ids, $user_ids);
        //         $difference_array_new = array_unique($difference_array_new);
        //         $difference_array_delete = array_unique($difference_array_delete);
        //         foreach($difference_array_new as $user_id){
        //             $pivot = new GroupUserPivot;
        //             $pivot->group_id = $id;
        //             $pivot->user_id = $user_id;
        //             $pivot->save();
        //         }
        //         $group = GroupUserPivot::where('group_id', $id)->get();
        //         foreach($difference_array_delete as $user_id){
        //             $user_group = $group->where('user_id', $user_id)->first();
        //             $user_group->delete();
        //         }
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // AGENT_GROUP_UPDATE
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
        $group = Group::find($id);
        if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        if($group->is_agent === 0){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                ]
            ]], 401);
        }
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->group_head = $request->get('group_head');
        $user_ids = $request->get('user_ids',[]);
        try{
            $group->save();

            $group_user_ids = GroupUserPivot::where('group_id', $id)->pluck('user_id')->toArray();
            if(!count($group_user_ids)) {
                $user_ids = array_unique($user_ids);
                foreach($user_ids as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($user_ids, $group_user_ids);
                $difference_array_delete = array_diff($group_user_ids, $user_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
                $group = GroupUserPivot::where('group_id', $id)->get();
                foreach($difference_array_delete as $user_id){
                    $user_group = $group->where('user_id', $user_id)->first();
                    $user_group->delete();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteAgentGroup(Request $request)
    {
        // $check = $this->checkRoute("AGENT_GROUP_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $group = Group::find($id);
        // if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // if($group->is_agent === 0){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //         ]
        //     ]], 401);
        // }
        // try{
        //     $group->delete();
        //     $group_user = GroupUserPivot::where('group_id', $id)->get();
        //     foreach($group_user as $user){
        //         $user->delete();
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // AGENT_GROUP_DELETE
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
        $group = Group::find($id);
        if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        if($group->is_agent === 0){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                ]
            ]], 401);
        }
        try{
            $group->delete();
            $group_user = GroupUserPivot::where('group_id', $id)->get();
            foreach($group_user as $user){
                $user->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRequesterGroups(Request $request)
    {
        // $check = $this->checkRoute("REQUESTER_GROUPS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $group = Group::where('is_agent', 0)->get();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $group]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // REQUESTER_GROUPS_GET
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
            $group = Group::where('is_agent', 0)->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $group]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRequesterGroup(Request $request)
    {
        // $check = $this->checkRoute("REQUESTER_GROUP_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $id = $request->get('id', null);
        //     $group = Group::find($id);
        //     if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        //     if($group->is_agent === 1){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //             ]
        //         ]], 401);
        //     }
        //     $group_user = GroupUserPivot::where('group_id', $id)->pluck('user_id');
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["group_detail" => $group, "group_user" => $group_user]]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // REQUESTER_GROUP_GET
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
            $group = Group::find($id);
            if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            if($group->is_agent === 1){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                    ]
                ]], 401);
            }
            $group_user = GroupUserPivot::where('group_id', $id)->pluck('user_id');
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["group_detail" => $group, "group_user" => $group_user]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addRequesterGroup(Request $request)
    {
        // $check = $this->checkRoute("REQUESTER_GROUP_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $group = new Group;
        // $group->name = $request->get('name');
        // $group->description = $request->get('description');
        // $group->is_agent = 0;
        // $group->group_head = $request->get('group_head');
        // $user_ids = $request->get('user_ids',[]);
        // try{
        //     $group->save();

        //     $group_id = $group->id;
        //     $user_ids = array_unique($user_ids);
        //     foreach($user_ids as $user_id){
        //         $pivot = new GroupUserPivot;
        //         $pivot->group_id = $group_id;
        //         $pivot->user_id = $user_id;
        //         $pivot->save();
        //     }
            
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // REQUESTER_GROUP_ADD
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
        $group = new Group;
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->is_agent = 0;
        $group->group_head = $request->get('group_head');
        $user_ids = $request->get('user_ids',[]);
        try{
            $group->save();

            $group_id = $group->id;
            $user_ids = array_unique($user_ids);
            foreach($user_ids as $user_id){
                $pivot = new GroupUserPivot;
                $pivot->group_id = $group_id;
                $pivot->user_id = $user_id;
                $pivot->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateRequesterGroup(Request $request)
    {
        // $check = $this->checkRoute("REQUESTER_GROUP_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $group = Group::find($id);
        // if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // if($group->is_agent === 1){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //         ]
        //     ]], 401);
        // }
        // $group->name = $request->get('name');
        // $group->description = $request->get('description');
        // $group->group_head = $request->get('group_head');
        // $user_ids = $request->get('user_ids',[]);
        // try{
        //     $group->save();

        //     $group_user_ids = GroupUserPivot::where('group_id', $id)->pluck('user_id')->toArray();
        //     if(!count($group_user_ids)) {
        //         $user_ids = array_unique($user_ids);
        //         foreach($user_ids as $user_id){
        //             $pivot = new GroupUserPivot;
        //             $pivot->group_id = $id;
        //             $pivot->user_id = $user_id;
        //             $pivot->save();
        //         }
        //     } else {
        //         $difference_array_new = array_diff($user_ids, $group_user_ids);
        //         $difference_array_delete = array_diff($group_user_ids, $user_ids);
        //         $difference_array_new = array_unique($difference_array_new);
        //         $difference_array_delete = array_unique($difference_array_delete);
        //         foreach($difference_array_new as $user_id){
        //             $pivot = new GroupUserPivot;
        //             $pivot->group_id = $id;
        //             $pivot->user_id = $user_id;
        //             $pivot->save();
        //         }
        //         $group = GroupUserPivot::where('group_id', $id)->get();
        //         foreach($difference_array_delete as $user_id){
        //             $user_group = $group->where('user_id', $user_id)->first();
        //             $user_group->delete();
        //         }
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // REQUESTER_GROUP_UPDATE
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
        $group = Group::find($id);
        if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        if($group->is_agent === 1){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                ]
            ]], 401);
        }
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->group_head = $request->get('group_head');
        $user_ids = $request->get('user_ids',[]);
        try{
            $group->save();

            $group_user_ids = GroupUserPivot::where('group_id', $id)->pluck('user_id')->toArray();
            if(!count($group_user_ids)) {
                $user_ids = array_unique($user_ids);
                foreach($user_ids as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($user_ids, $group_user_ids);
                $difference_array_delete = array_diff($group_user_ids, $user_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
                $group = GroupUserPivot::where('group_id', $id)->get();
                foreach($difference_array_delete as $user_id){
                    $user_group = $group->where('user_id', $user_id)->first();
                    $user_group->delete();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteRequesterGroup(Request $request)
    {
        // $check = $this->checkRoute("REQUESTER_GROUP_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $group = Group::find($id);
        // if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // if($group->is_agent === 1){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
        //         ]
        //     ]], 401);
        // }
        // try{
        //     $group->delete();
        //     $group_user = GroupUserPivot::where('group_id', $id)->get();
        //     foreach($group_user as $user){
        //         $user->delete();
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // REQUESTER_GROUP_DELETE
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
        $group = Group::find($id);
        if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        if($group->is_agent === 1){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Group Ini",
                ]
            ]], 401);
        }
        try{
            $group->delete();
            $group_user = GroupUserPivot::where('group_id', $id)->get();
            foreach($group_user as $user){
                $user->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getGroups(Request $request)
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
            $is_agent = $request->get('is_agent', 1);
            $group = Group::where('is_agent', $is_agent)->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $group]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getGroup(Request $request)
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
            $group = Group::find($id);
            if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            $group_user = GroupUserPivot::where('group_id', $id)->pluck('user_id');
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["group_detail" => $group, "group_user" => $group_user]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addGroup(Request $request)
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
        $group = new Group;
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->is_agent = $request->get('is_agent');
        $group->group_head = $request->get('group_head');
        $user_ids = $request->get('user_ids',[]);
        try{
            $group->save();

            $group_id = $group->id;
            $user_ids = array_unique($user_ids);
            foreach($user_ids as $user_id){
                $pivot = new GroupUserPivot;
                $pivot->group_id = $group_id;
                $pivot->user_id = $user_id;
                $pivot->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateGroup(Request $request)
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
        $group = Group::find($id);
        if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->group_head = $request->get('group_head');
        $user_ids = $request->get('user_ids',[]);
        try{
            $group->save();

            $group_user_ids = GroupUserPivot::where('group_id', $id)->pluck('user_id')->toArray();
            if(!count($group_user_ids)) {
                $user_ids = array_unique($user_ids);
                foreach($user_ids as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($user_ids, $group_user_ids);
                $difference_array_delete = array_diff($group_user_ids, $user_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
                $group = GroupUserPivot::where('group_id', $id)->get();
                foreach($difference_array_delete as $user_id){
                    $user_group = $group->where('user_id', $user_id)->first();
                    $user_group->delete();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteGroup(Request $request)
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
        $group = Group::find($id);
        if($group === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $group->delete();
            $group_user = GroupUserPivot::where('group_id', $id)->get();
            foreach($group_user as $user){
                $user->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    
}