<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Role;
use App\RoleFeaturePivot;
use App\UserRolePivot;
use App\AccessFeature;

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
        // $protocol = "AGENT_GET";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('account_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     } else if($response['data']['role'] === 2) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response['data']]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENT_GET
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
            } else {
                $response['data']['feature_roles'] = UserRolePivot::where('user_id', $response['data']['user_id'])->pluck('role_id')->toArray();
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response['data']]);
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

    public function getAgentList(Request $request)
    {
        // $protocol = "AGENTS_GET";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true&order_by=asc', [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     } else {
        //         $list_agent = [];
        //         foreach($response['data']['accounts'] as $user){
        //             if($user['role'] === 1){
        //                 $list_agent[] = $user;
        //             }
        //         }
        //       return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $list_agent]);  
        //     } 
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENTS_GET
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true&order_by=asc', [
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
        // $protocol = "AGENT_ADD";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $body = [
        //         "fullname" => $request->get('fullname'),
        //         "company_id" => $request->get('company_id'),
        //         "email" => $request->get('email'),
        //         "role" => 1,
        //         "phone_number" => $request->get('phone_number'),
        //         "profile_image" => $request->get('profile_image', null)
        //     ];
        //     $response = $this->client->request('POST', '/admin/v1/add-new-account', [
        //             'headers'  => $headers,
        //             'json' => $body
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     else return response()->json(["success" => true, "message" => $response['data']['message']]);
        
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENT_ADD
        $body = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "role" => 1,
            "phone_number" => $request->get('phone_number'),
            "profile_image" => $request->get('profile_image', null)
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
        // $protocol = "AGENT_UPDATE";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 2){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         $body = [
        //             "id" => $account_id,
        //             "fullname" => $request->get('fullname'),
        //             "role" => 1,
        //             "phone_number" => $request->get('phone_number'),
        //             "profile_image" => $request->get('profile_image', null)
        //         ];
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
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENT_UPDATE
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
        // $protocol = "AGENT_PASSWORD_UPDATE";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('user_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 2){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         $body = [
        //             "user_id" => $account_id,
        //             "new_password" => $request->get('new_password')
        //         ];
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
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENT_PASSWORD_UPDATE
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
        // $protocol = "AGENT_STATUS";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('user_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 2){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         $body = [
        //             'is_enabled' => $request->get('is_enabled'),
        //             'user_id' => $account_id
        //         ];
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
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENT_STATUS
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

    public function updateFeatureAgent(Request $request)
    {
        // $protocol = "AGENT_UPDATE_FEATURE";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('account_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //         'headers'  => $headers
        //     ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 2){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         // feature ids for accessing cgx's company and account feature
        //         $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62, 74, 75];

        //         $role_ids = $request->get('role_ids', []);
        //         $feature_ids = [];
        //         foreach($role_ids as $role_id){
        //             $role = Role::find($role_id);
        //             if($role !== null){
        //                 $role_feature_ids = RoleFeaturePivot::where('role_id', $role->id)->pluck('feature_id');
        //                 foreach($role_feature_ids as $feature_id){
        //                     $feature_ids[] = $feature_id; 
        //                 }
        //             }
        //         }
        //         $unique_ids = array_unique($feature_ids);
        //         $account_feature_ids = array_merge($default_feature, $unique_ids);
        //         $body = [
        //             'account_id' => $account_id,
        //             'feature_ids' => $account_feature_ids
        //         ];
        //         $response = $this->client->request('POST', '/admin/v1/update-feature', [
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
        //         } else {
        //             try{
        //                 $user_role_ids = UserRolePivot::where('user_id', $account_id)->pluck('role_id')->toArray();
        //                 if(!count($user_role_ids)) {
        //                     foreach($role_ids as $role_id){
        //                         $pivot = new UserRolePivot;
        //                         $pivot->user_id = $account_id;
        //                         $pivot->role_id = $role_id;
        //                         $pivot->save();
        //                     }
        //                 } else {
        //                     $difference_array_new = array_diff($role_ids, $user_role_ids);
        //                     $difference_array_delete = array_diff($user_role_ids, $role_ids);
        //                     $difference_array_new = array_unique($difference_array_new);
        //                     $difference_array_delete = array_unique($difference_array_delete);
        //                     foreach($difference_array_new as $role_id){
        //                         $pivot = new UserRolePivot;
        //                         $pivot->user_id = $account_id;
        //                         $pivot->role_id = $role_id;
        //                         $pivot->save();
        //                     }
        //                     $user = UserRolePivot::where('user_id', $account_id)->get();
        //                     foreach($difference_array_delete as $role_id){
        //                         $role_user = $user->where('role_id', $role_id)->first();
        //                         $role_user->delete();
        //                     }
        //                 }
        //                 return response()->json(["success" => true, "message" => "Berhasil Merubah Fitur Akun"]);
        //             } catch(Exception $err){
        //                 return response()->json(["success" => false, "message" => $err], 400);
        //             }
        //         }
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // AGENT_UPDATE_FEATURE
        $account_id = $request->get('account_id');
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
            // feature ids for accessing cgx's company and account feature
            $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62, 74, 75];

            $role_ids = $request->get('role_ids', []);
            $feature_ids = [];
            foreach($role_ids as $role_id){
                $role = Role::find($role_id);
                if($role !== null){
                    $role_feature_ids = RoleFeaturePivot::where('role_id', $role->id)->pluck('feature_id');
                    foreach($role_feature_ids as $feature_id){
                        $feature_ids[] = $feature_id; 
                    }
                }
            }
            $unique_ids = array_unique($feature_ids);
            $account_feature_ids = array_merge($default_feature, $unique_ids);
            $body = [
                'account_id' => $account_id,
                'feature_ids' => $account_feature_ids
            ];
            $headers = [
                'Authorization' => $request->header("Authorization"),
                'content-type' => 'application/json'
            ];
            try{
                $response = $this->client->request('POST', '/admin/v1/update-feature', [
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
                } else {
                    try{
                        $user_role_ids = UserRolePivot::where('user_id', $account_id)->pluck('role_id')->toArray();
                        if(!count($user_role_ids)) {
                            foreach($role_ids as $role_id){
                                $pivot = new UserRolePivot;
                                $pivot->user_id = $account_id;
                                $pivot->role_id = $role_id;
                                $pivot->save();
                            }
                        } else {
                            $difference_array_new = array_diff($role_ids, $user_role_ids);
                            $difference_array_delete = array_diff($user_role_ids, $role_ids);
                            $difference_array_new = array_unique($difference_array_new);
                            $difference_array_delete = array_unique($difference_array_delete);
                            foreach($difference_array_new as $role_id){
                                $pivot = new UserRolePivot;
                                $pivot->user_id = $account_id;
                                $pivot->role_id = $role_id;
                                $pivot->save();
                            }
                            $user = UserRolePivot::where('user_id', $account_id)->get();
                            foreach($difference_array_delete as $role_id){
                                $role_user = $user->where('role_id', $role_id)->first();
                                $role_user->delete();
                            }
                        }
                        return response()->json(["success" => true, "message" => "Berhasil Merubah Fitur Akun"]);
                    } catch(Exception $err){
                        return response()->json(["success" => false, "message" => $err], 400);
                    }
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
    }

    public function getRequesterDetail(Request $request)
    {
        // $protocol = "REQUESTER_GET";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('account_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     } else if($response['data']['role'] === 1) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response['data']]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTER_GET
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
            } else {
                $response['data']['feature_roles'] = UserRolePivot::where('user_id', $response['data']['user_id'])->pluck('role_id')->toArray();
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response['data']]);
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

    public function getRequesterList(Request $request)
    {
        // $protocol = "REQUESTERS_GET";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true&order_by=asc', [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     } else {
        //         $list_requester = [];
        //         foreach($response['data']['accounts'] as $user){
        //             if($user['role'] === 2){
        //                 $list_requester[] = $user;
        //             }
        //         }
        //       return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $list_requester]);  
        //     } 
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTERS_GET
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true&order_by=asc', [
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
        // $protocol = "REQUESTER_ADD";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $body = [
        //         "fullname" => $request->get('fullname'),
        //         "company_id" => $request->get('company_id'),
        //         "email" => $request->get('email'),
        //         "role" => 2,
        //         "phone_number" => $request->get('phone_number'),
        //         "profile_image" => $request->get('profile_image', null)
        //     ];
        //     $response = $this->client->request('POST', '/admin/v1/add-new-account', [
        //             'headers'  => $headers,
        //             'json' => $body
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     else return response()->json(["success" => true, "message" => $response['data']['message']]);
        
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTER_ADD
        $body = [
            "fullname" => $request->get('fullname'),
            "company_id" => $request->get('company_id'),
            "email" => $request->get('email'),
            "role" => 2,
            "phone_number" => $request->get('phone_number'),
            "profile_image" => $request->get('profile_image', null)
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
        // $protocol = "REQUESTER_UPDATE";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 1){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         $body = [
        //             "id" => $account_id,
        //             "fullname" => $request->get('fullname'),
        //             "role" => 2,
        //             "phone_number" => $request->get('phone_number'),
        //             "profile_image" => $request->get('profile_image', null)
        //         ];
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
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTER_UPDATE
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
        
        // $protocol = "REQUESTER_PASSWORD_UPDATE";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('user_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 1){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         $body = [
        //             "user_id" => $account_id,
        //             "new_password" => $request->get('new_password')
        //         ];
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
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTER_PASSWORD_UPDATE
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
        // $protocol = "REQUESTER_STATUS";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('user_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 1){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         $body = [
        //             'is_enabled' => $request->get('is_enabled'),
        //             'user_id' => $account_id
        //         ];
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
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTER_STATUS
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

    public function updateFeatureRequester(Request $request)
    {
        // $protocol = "REQUESTER_UPDATE_FEATURE";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->feature_key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //     ]);
        //     $account_id = $request->get('account_id');
        //     $response = $this->client->request('GET', '/admin/v1/get-account?id='.$account_id, [
        //         'headers'  => $headers
        //     ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     if($response['data']['role'] === 1){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Ini",
        //             ]
        //         ]], 401);
        //     } else {
        //         // feature ids for accessing cgx's company and account feature
        //         $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62, 74, 75];

        //         $role_ids = $request->get('role_ids', []);
        //         $feature_ids = [];
        //         foreach($role_ids as $role_id){
        //             $role = Role::find($role_id);
        //             if($role !== null){
        //                 $role_feature_ids = RoleFeaturePivot::where('role_id', $role->id)->pluck('feature_id');
        //                 foreach($role_feature_ids as $feature_id){
        //                     $feature_ids[] = $feature_id; 
        //                 }
        //             }
        //         }
        //         $unique_ids = array_unique($feature_ids);
        //         $account_feature_ids = array_merge($default_feature, $unique_ids);
        //         $body = [
        //             'account_id' => $account_id,
        //             'feature_ids' => $account_feature_ids
        //         ];
        //         $response = $this->client->request('POST', '/admin/v1/update-feature', [
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
        //         } else {
        //             try{
        //                 $user_role_ids = UserRolePivot::where('user_id', $account_id)->pluck('role_id')->toArray();
        //                 if(!count($user_role_ids)) {
        //                     foreach($role_ids as $role_id){
        //                         $pivot = new UserRolePivot;
        //                         $pivot->user_id = $account_id;
        //                         $pivot->role_id = $role_id;
        //                         $pivot->save();
        //                     }
        //                 } else {
        //                     $difference_array_new = array_diff($role_ids, $user_role_ids);
        //                     $difference_array_delete = array_diff($user_role_ids, $role_ids);
        //                     $difference_array_new = array_unique($difference_array_new);
        //                     $difference_array_delete = array_unique($difference_array_delete);
        //                     foreach($difference_array_new as $role_id){
        //                         $pivot = new UserRolePivot;
        //                         $pivot->user_id = $account_id;
        //                         $pivot->role_id = $role_id;
        //                         $pivot->save();
        //                     }
        //                     $user = UserRolePivot::where('user_id', $account_id)->get();
        //                     foreach($difference_array_delete as $role_id){
        //                         $role_user = $user->where('role_id', $role_id)->first();
        //                         $role_user->delete();
        //                     }
        //                 }
        //                 return response()->json(["success" => true, "message" => "Berhasil Merubah Fitur Akun"]);
        //             } catch(Exception $err){
        //                 return response()->json(["success" => false, "message" => $err], 400);
        //             }
        //         }
        //     }
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // REQUESTER_UPDATE_FEATURE
        $account_id = $request->get('account_id');
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
            // feature ids for accessing cgx's company and account feature
            $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62, 74, 75];

            $role_ids = $request->get('role_ids', []);
            $feature_ids = [];
            foreach($role_ids as $role_id){
                $role = Role::find($role_id);
                if($role !== null){
                    $role_feature_ids = RoleFeaturePivot::where('role_id', $role->id)->pluck('feature_id');
                    foreach($role_feature_ids as $feature_id){
                        $feature_ids[] = $feature_id; 
                    }
                }
            }
            $unique_ids = array_unique($feature_ids);
            $account_feature_ids = array_merge($default_feature, $unique_ids);
            $body = [
                'account_id' => $account_id,
                'feature_ids' => $account_feature_ids
            ];
            $headers = [
                'Authorization' => $request->header("Authorization"),
                'content-type' => 'application/json'
            ];
            try{
                $response = $this->client->request('POST', '/admin/v1/update-feature', [
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
                } else {
                    try{
                        $user_role_ids = UserRolePivot::where('user_id', $account_id)->pluck('role_id')->toArray();
                        if(!count($user_role_ids)) {
                            foreach($role_ids as $role_id){
                                $pivot = new UserRolePivot;
                                $pivot->user_id = $account_id;
                                $pivot->role_id = $role_id;
                                $pivot->save();
                            }
                        } else {
                            $difference_array_new = array_diff($role_ids, $user_role_ids);
                            $difference_array_delete = array_diff($user_role_ids, $role_ids);
                            $difference_array_new = array_unique($difference_array_new);
                            $difference_array_delete = array_unique($difference_array_delete);
                            foreach($difference_array_new as $role_id){
                                $pivot = new UserRolePivot;
                                $pivot->user_id = $account_id;
                                $pivot->role_id = $role_id;
                                $pivot->save();
                            }
                            $user = UserRolePivot::where('user_id', $account_id)->get();
                            foreach($difference_array_delete as $role_id){
                                $role_user = $user->where('role_id', $role_id)->first();
                                $role_user->delete();
                            }
                        }
                        return response()->json(["success" => true, "message" => "Berhasil Merubah Fitur Akun"]);
                    } catch(Exception $err){
                        return response()->json(["success" => false, "message" => $err], 400);
                    }
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