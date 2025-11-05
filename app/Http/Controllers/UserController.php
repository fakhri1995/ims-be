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
    }

    public function getFilterUsers(Request $request)
    {
        $route_name = "USERS_GET";
        
        $response = $this->userService->getFilterUsers($request, $route_name);
        return response()->json($response, $response['status']);
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
        
        $response = $this->userService->getAgentList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAgentMember(Request $request)
    {
        $route_name = "AGENT_ADD";
        
        $response = $this->userService->addAgentMember($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAgentDetail(Request $request)
    {
        $route_name = "AGENT_UPDATE";

        $response = $this->userService->updateAgentDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeAgentPassword(Request $request)
    {
        $route_name = "AGENT_PASSWORD_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "password" => $request->get('new_password')
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
        
        $response = $this->userService->getRequesterList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRequesterMember(Request $request)
    {
        $route_name = "REQUESTER_ADD";
        
        $response = $this->userService->addRequesterMember($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRequesterDetail(Request $request)
    {
        $route_name = "REQUESTER_UPDATE";

        $response = $this->userService->updateRequesterDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeRequesterPassword(Request $request)
    {
        $route_name = "REQUESTER_PASSWORD_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "password" => $request->get('new_password')
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

    //Guest

    public function getGuestDetail(Request $request)
    {
        $route_name = "GUEST_GET";
        
        $account_id = $request->get('account_id');
        $response = $this->userService->getGuestDetail($account_id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getGuestList(Request $request)
    {
        $route_name = "GUESTS_GET";
        
        $response = $this->userService->getGuestList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addGuestMember(Request $request)
    {
        $route_name = "GUEST_ADD";
        
        $response = $this->userService->addGuestMember($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateGuestDetail(Request $request)
    {
        $route_name = "GUEST_UPDATE";

        $response = $this->userService->updateGuestDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function changeGuestPassword(Request $request)
    {
        $route_name = "GUEST_PASSWORD_UPDATE";
        
        $data_request = [
            "id" => $request->get('id'),
            "password" => $request->get('new_password')
        ];

        $response = $this->userService->changeGuestPassword($data_request, $route_name);
        return response()->json($response, $response['status']);     
    }

    public function guestActivation(Request $request)
    {
        $route_name = "GUEST_STATUS";
        
        $data_request = [
            "id" => $request->get('user_id'),
            "is_enabled" => $request->get('is_enabled')
        ];

        $response = $this->userService->guestActivation($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteGuest(Request $request)
    {
        $route_name = "GUEST_DELETE";
        
        $id = $request->get('id', null);
        $response = $this->userService->deleteGuest($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAgentEmployeeList(Request $request)
    {
        $route_name = "AGENT_EMPLOYEE_GET";
        
        $id = $request->get('id', null);
        $response = $this->userService->getAgentEmployeeList($id, $route_name);
        return response()->json($response, $response['status']);
    }
     public function addConnectAgent(Request $request)
    {
        $route_name = "AGENT_CONNECT_ADD";
        
        $response = $this->userService->addConnectAgent($request, $route_name);
        return response()->json($response, $response['status']);
    }

    
}