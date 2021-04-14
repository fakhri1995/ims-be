<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function getAgentDetail(Request $request)
    {
        // GET_AGENT
        $account_id = $request->get('account_id');
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            } else if($response['data']['role'] === 2) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);
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
    }

    public function getAgentList(Request $request)
    {
        // GET_AGENTS
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-account?page=1&rows=50', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            } else {
                $list_agent = [];
                foreach($response['data']['accounts'] as $user){
                    if($user['role'] === 1){
                        $list_agent[] = $user;
                    }
                }
              return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $list_agent]);  
            } 

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
    }

    public function addAgentMember(Request $request)
    {
        // ADD_AGENT
        $body = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "role" => 1,
            "phone_number" => $request->get('phone_number')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/add-new-account', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
    }

    public function updateAgentDetail(Request $request)
    {
        // UPDATE_AGENT
        try{
            $account_id = $request->get('id');
            $headers = ['Authorization' => $request->header("Authorization")];
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            if($response['data']['role'] === 2){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else {
                $body = [
                    "id" => $account_id,
                    "fullname" => $request->get('fullname'),
                    "role" => 1,
                    "phone_number" => $request->get('phone_number'),
                    "profile_image" => $request->get('profile_image', null)
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/update-account', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }
                    else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
            }
        } catch(ClientException $err){
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
    }

    public function changeAgentPassword(Request $request)
    {
        // UPDATE_AGENT_PASSWORD
        try{
            $account_id = $request->get('user_id');
            $headers = ['Authorization' => $request->header("Authorization")];
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            if($response['data']['role'] === 2){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else {
                $body = [
                    "user_id" => $account_id,
                    "new_password" => $request->get('new_password')
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/change-password', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }
                    else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
            }
        } catch(ClientException $err){
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
        
    }

    public function agentActivation(Request $request)
    {
        // STATUS_AGENT
        try{
            $account_id = $request->get('user_id');
            $headers = ['Authorization' => $request->header("Authorization")];
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            if($response['data']['role'] === 2){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else {
                $body = [
                    'is_enabled' => $request->get('is_enabled'),
                    'user_id' => $account_id
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/change-status-activation', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }
                    else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
            }
        } catch(ClientException $err){
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
    }

    public function getRequesterDetail(Request $request)
    {
        // GET_REQUESTER
        $account_id = $request->get('account_id');
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            } else if($response['data']['role'] === 1) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);
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
    }

    public function getRequesterList(Request $request)
    {
        // GET_REQUESTERS
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-account?page=1&rows=50', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            } else {
                $list_requester = [];
                foreach($response['data']['accounts'] as $user){
                    if($user['role'] === 2){
                        $list_requester[] = $user;
                    }
                }
              return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $list_requester]);  
            } 

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
    }

    public function addRequesterMember(Request $request)
    {
        // ADD_REQUESTER
        $body = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "role" => 2,
            "phone_number" => $request->get('phone_number')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/add-new-account', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
    }

    public function updateRequesterDetail(Request $request)
    {
        // UPDATE_REQUESTER
        try{
            $account_id = $request->get('id');
            $headers = ['Authorization' => $request->header("Authorization")];
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            if($response['data']['role'] === 1){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else {
                $body = [
                    "id" => $account_id,
                    "fullname" => $request->get('fullname'),
                    "role" => 2,
                    "phone_number" => $request->get('phone_number'),
                    "profile_image" => $request->get('profile_image', null)
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/update-account', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }
                    else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
            }
        } catch(ClientException $err){
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
    }

    public function changeRequesterPassword(Request $request)
    {
        // UPDATE_REQUESTER_PASSWORD
        try{
            $account_id = $request->get('user_id');
            $headers = ['Authorization' => $request->header("Authorization")];
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            if($response['data']['role'] === 1){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else {
                $body = [
                    "user_id" => $account_id,
                    "new_password" => $request->get('new_password')
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/change-password', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }
                    else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
            }
        } catch(ClientException $err){
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
        
    }

    public function requesterActivation(Request $request)
    {
        // STATUS_REQUESTER
        try{
            $account_id = $request->get('user_id');
            $headers = ['Authorization' => $request->header("Authorization")];
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            if($response['data']['role'] === 1){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
                    ]
                ]], 401);
            } else {
                $body = [
                    'is_enabled' => $request->get('is_enabled'),
                    'user_id' => $account_id
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/change-status-activation', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }
                    else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
            }
        } catch(ClientException $err){
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
    }

    public function getAccountDetail(Request $request)
    {
        $account_id = $request->get('account_id');
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);
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
    }

    public function getAccountList(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by'),
            'company_id' => $request->get('company_id')
        ];
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-account?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by']
                .'&company_id='.$params['company_id'], [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);

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
    }

    public function addAccountMember(Request $request)
    {
        $body = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "role" => $request->get('role'),
            "phone_number" => $request->get('phone_number'),
            "profile_image" => $request->get('profile_image')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/add-new-account', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
    }

    public function updateAccountDetail(Request $request)
    {
        $body = [
            "id" => $request->get('id'),
            "fullname" => $request->get('fullname'),
            "role" => $request->get('role'),
            "phone_number" => $request->get('phone_number'),
            "profile_image" => $request->get('profile_image')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/update-account', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
    }

    public function changeAccountPassword(Request $request)
    {
        $body = [
            "user_id" => $request->get('user_id'),
            "new_password" => $request->get('new_password')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/change-password', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
    }

    public function accountActivation(Request $request)
    {
        $body = [
            'is_enabled' => $request->get('is_enabled'),
            'user_id' => $request->get('user_id')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/change-status-activation', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response['data']['message']]);
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
    }
}