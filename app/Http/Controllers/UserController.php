<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    
    public function __construct()
    {
        $this->userService = new UserService;
        // $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function getAgentDetail(Request $request)
    {
        $route_name = "AGENT_GET";

        $account_id = $request->get('account_id');
        $response = $this->userService->getAgentDetail($account_id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAgentList(Request $request)
    {
        $route_name = "AGENTS_GET";
        
        $response = $this->userService->getAgentList($route_name);
        return response()->json($response, $response['status']);
    }

    public function addAgentMember(Request $request)
    {
        $route_name = "AGENT_ADD";
        
        $data_request = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "phone_number" => $request->get('phone_number'),
            "profile_image" => $request->get('profile_image', null),
            "password" => $request->get('password'),
            "confirm_password" => $request->get('confirm_password'),
            "role_ids" => $request->get('role_ids', [])
        ];
        
        $response = $this->userService->addAgentMember($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAgentDetail(Request $request)
    {
        $route_name = "AGENT_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "fullname" => $request->get('fullname'),
            "phone_number" => $request->get('phone_number'),
            "role_ids" => $request->get('role_ids', [])
        ];

        $response = $this->userService->updateAgentDetail($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeAgentPassword(Request $request)
    {
        $route_name = "AGENT_PASSWORD_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "password" => $request->get('password')
        ];

        $response = $this->userService->changeAgentPassword($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function agentActivation(Request $request)
    {
        $route_name = "AGENT_STATUS";
        
        $data_request = [
            "id" => $request->get('user_id'),
            "is_enabled" => $request->get('is_enabled')
        ];

        $response = $this->userService->agentActivation($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    // public function updateFeatureAgent(Request $request)
    // {
    //     $route_name = "AGENT_UPDATE_FEATURE";
        
    //     $data_request = [
    //         "id" => $request->get('account_id', null),
    //         "role_ids" => $request->get('role_ids', [])
    //     ];

    //     $response = $this->userService->updateFeatureAgent($data_request, $route_name);
    //     return response()->json($response, $response['status']);
    // }

    public function deleteAgent(Request $request)
    {
        $route_name = "AGENT_DELETE";
        
        $id = $request->get('id', null);
        $response = $this->userService->deleteAgent($id, $route_name);
        return response()->json($response, $response['status']);
    }

    //Requester

    public function getRequesterDetail(Request $request)
    {
        $route_name = "REQUESTER_GET";
        
        $account_id = $request->get('account_id');
        $response = $this->userService->getRequesterDetail($account_id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRequesterList(Request $request)
    {
        $route_name = "REQUESTERS_GET";
        
        $response = $this->userService->getRequesterList($route_name);
        return response()->json($response, $response['status']);
    }

    public function addRequesterMember(Request $request)
    {
        $route_name = "REQUESTER_ADD";
        
        $data_request = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "phone_number" => $request->get('phone_number'),
            "profile_image" => $request->get('profile_image', null),
            "password" => $request->get('password'),
            "confirm_password" => $request->get('confirm_password'),
            "role_ids" => $request->get('role_ids', [])
        ];
        
        $response = $this->userService->addRequesterMember($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRequesterDetail(Request $request)
    {
        $route_name = "REQUESTER_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "fullname" => $request->get('fullname'),
            "phone_number" => $request->get('phone_number'),
            "role_ids" => $request->get('role_ids', [])
        ];

        $response = $this->userService->updateRequesterDetail($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeRequesterPassword(Request $request)
    {
        $route_name = "REQUESTER_PASSWORD_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "password" => $request->get('password')
        ];

        $response = $this->userService->changeRequesterPassword($data_request, $route_name);
        return response()->json($response, $response['status']);     
    }

    public function requesterActivation(Request $request)
    {
        $route_name = "REQUESTER_STATUS";
        
        $data_request = [
            "id" => $request->get('user_id'),
            "is_enabled" => $request->get('is_enabled')
        ];

        $response = $this->userService->requesterActivation($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    // public function updateFeatureRequester(Request $request)
    // {
    //     $route_name = "REQUESTER_UPDATE_FEATURE";
        
    //     $data_request = [
    //         "id" => $request->get('account_id', null),
    //         "role_ids" => $request->get('role_ids', [])
    //     ];

    //     $response = $this->userService->updateFeatureRequester($data_request, $route_name);
    //     return response()->json($response, $response['status']);
    // }

    public function deleteRequester(Request $request)
    {
        $route_name = "REQUESTER_DELETE";
        
        $id = $request->get('id', null);
        $response = $this->userService->deleteRequester($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // public function getAccountDetail(Request $request)
    // {
    //     $account_id = $request->get('account_id');
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
    //                 'headers'  => $headers
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }

    // public function getAccountList(Request $request)
    // {
    //     $params = [
    //         'page' => $request->get('page'),
    //         'rows' => $request->get('rows'),
    //         'order_by' => $request->get('order_by'),
    //         'company_id' => $request->get('company_id')
    //     ];
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/admin/v1/get-list-account?page='.$params['page']
    //             .'&rows='.$params['rows']
    //             .'&order_by='.$params['order_by']
    //             .'&company_id='.$params['company_id'], [
    //                 'headers'  => $headers
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);

    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }

    // public function addAccountMember(Request $request)
    // {
    //     $body = [
    //         "fullname" => $request->get('fullname'),
    //         "company_id" => $request->get('company_id'),
    //         "email" => $request->get('email'),
    //         "role" => $request->get('role'),
    //         "phone_number" => $request->get('phone_number'),
    //         "profile_image" => $request->get('profile_image')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/add-new-account', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => $response['data']['message']]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }

    // public function updateAccountDetail(Request $request)
    // {
    //     $body = [
    //         "id" => $request->get('id'),
    //         "fullname" => $request->get('fullname'),
    //         "role" => $request->get('role'),
    //         "phone_number" => $request->get('phone_number'),
    //         "profile_image" => $request->get('profile_image')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/update-account', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => $response['data']['message']]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }

    // public function changeAccountPassword(Request $request)
    // {
    //     $body = [
    //         "user_id" => $request->get('user_id'),
    //         "new_password" => $request->get('new_password')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/change-password', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => $response['data']['message']]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }

    // public function accountActivation(Request $request)
    // {
    //     $body = [
    //         'is_enabled' => $request->get('is_enabled'),
    //         'user_id' => $request->get('user_id')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/change-status-activation', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => $response['data']['message']]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }

    // public function updateFeatureRequester(Request $request)
    // {
    //     $header = $request->header("Authorization");
    //     $headers = ['Authorization' => $header];
    //     $check = $this->checkRoute("REQUESTER_UPDATE_FEATURE", $header);
    //     if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
    
    //     $headers = [
    //         'Authorization' => $header,
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $account_id = $request->get('account_id');
    //         $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
    //             'headers'  => $headers
    //         ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         if($response['data']['role'] === 1){
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 401,
    //                     "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
    //                     "server_code" => 401,
    //                     "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
    //                 ]
    //             ]], 401);
    //         } else {
    //             // feature ids for accessing cgx's company and account feature
    //             $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62, 74, 75];

    //             $role_ids = $request->get('role_ids', []);
    //             $feature_ids = [];
    //             foreach($role_ids as $role_id){
    //                 $role = Role::find($role_id);
    //                 if($role !== null){
    //                     $role_feature_ids = RoleFeaturePivot::where('role_id', $role->id)->pluck('feature_id');
    //                     foreach($role_feature_ids as $feature_id){
    //                         $feature_ids[] = $feature_id; 
    //                     }
    //                 }
    //             }
    //             $unique_ids = array_unique($feature_ids);
    //             $account_feature_ids = array_merge($default_feature, $unique_ids);
    //             $body = [
    //                 'account_id' => $account_id,
    //                 'feature_ids' => $account_feature_ids
    //             ];
    //             $response = $this->client->request('POST', '/admin/v1/update-feature', [
    //                     'headers'  => $headers,
    //                     'json' => $body
    //                 ]);
    //             $response = json_decode((string) $response->getBody(), true);
    //             if(array_key_exists('error', $response)) {
    //                 return response()->json(["success" => false, "message" => (object)[
    //                     "errorInfo" => [
    //                         "status" => 400,
    //                         "reason" => $response['error']['detail'],
    //                         "server_code" => $response['error']['code'],
    //                         "status_detail" => $response['error']['detail']
    //                     ]
    //                 ]], 400);
    //             } else {
    //                 try{
    //                     $user_role_ids = UserRolePivot::where('user_id', $account_id)->pluck('role_id')->toArray();
    //                     if(!count($user_role_ids)) {
    //                         foreach($role_ids as $role_id){
    //                             $pivot = new UserRolePivot;
    //                             $pivot->user_id = $account_id;
    //                             $pivot->role_id = $role_id;
    //                             $pivot->save();
    //                         }
    //                     } else {
    //                         $difference_array_new = array_diff($role_ids, $user_role_ids);
    //                         $difference_array_delete = array_diff($user_role_ids, $role_ids);
    //                         $difference_array_new = array_unique($difference_array_new);
    //                         $difference_array_delete = array_unique($difference_array_delete);
    //                         foreach($difference_array_new as $role_id){
    //                             $pivot = new UserRolePivot;
    //                             $pivot->user_id = $account_id;
    //                             $pivot->role_id = $role_id;
    //                             $pivot->save();
    //                         }
    //                         $user = UserRolePivot::where('user_id', $account_id)->get();
    //                         foreach($difference_array_delete as $role_id){
    //                             $role_user = $user->where('role_id', $role_id)->first();
    //                             $role_user->delete();
    //                         }
    //                     }
    //                     return response()->json(["success" => true, "message" => "Berhasil Merubah Fitur Akun"]);
    //                 } catch(Exception $err){
    //                     return response()->json(["success" => false, "message" => $err], 400);
    //                 }
    //             }
    //         }
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }
}