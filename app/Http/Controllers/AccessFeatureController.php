<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\AccessFeature;

class AccessFeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    protected $cgx_module_id = 63;
    
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function getAccessModule(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by')
        ];
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/account-module?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by'], [
                    'headers'  => $headers
                ]);
                return response(json_decode((string) $response->getBody(), true));
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

    // public function getAccessFeature(Request $request)
    // {
    //     $params = [
    //         'page' => $request->get('page'),
    //         'rows' => $request->get('rows'),
    //         'order_by' => $request->get('order_by'),
    //         'module_id' => $request->get('module_id'),
    //         'company_id' => $request->get('company_id')
    //     ];
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/admin/v1/account-feature?page='.$params['page']
    //             .'&rows='.$params['rows']
    //             .'&order_by='.$params['order_by']
    //             .'&module_id='.$params['module_id']
    //             .'&company_id='.$params['company_id'], [
    //                 'headers'  => $headers
    //             ]);
    //         return response(json_decode((string) $response->getBody(), true));
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

    // public function addAccessModule(Request $request)
    // {
    //     $body = [
    //         "name" => $request->get('name'),
    //         "description" => $request->get('description'),
    //         "can_mark_as_default" => $request->get('can_mark_as_default')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];    
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/account-module', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         return response(json_decode((string) $response->getBody(), true));
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

    // public function updateAccessFeature(Request $request)
    // {
    //     $body = [
    //         "feature_id" => $request->get('feature_id'),
    //         "name" => $request->get('name'),
    //         "description" => $request->get('description'),
    //         "account_module_id" => $request->get('account_module_id'),
    //         "path_url" => $request->get('path_url'),
    //         "method" => $request->get('method')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/account-feature', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         return response(json_decode((string) $response->getBody(), true));
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

    // public function updateModuleCompany(Request $request)
    // {
    //     $body = [
    //         'company_id' => $request->get('company_id'),
    //         'module_ids' => $request->get('module_ids')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/update-module', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         return response(json_decode((string) $response->getBody(), true));
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

    // Feature
    public function getFeatures(Request $request)
    {
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('GET', '/admin/v1/module-member?module_id='.$this->cgx_module_id , [
                    'headers'  => $headers
                ]);
            $data = json_decode((string) $response->getBody(), true)['data']['features'];
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $data]);
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

    public function addFeature(Request $request)
    {
        $name = $request->get('name');
        $description = $request->get('description');
        $body = [
            "name" => $name,
            "description" => $description,
            "method" => "CONNECT"
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/account-feature', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            $feature_key = json_decode((string) $response->getBody(), true)['data']['feature_detail']['feature_key'];
            $feature_id = json_decode((string) $response->getBody(), true)['data']['feature_detail']['feature_id'];
            
            $response_module_member = $this->client->request('GET', '/admin/v1/module-member?module_id='.$this->cgx_module_id, [
                    'headers'  => $headers
                ]);
            $list_data = json_decode((string) $response_module_member->getBody(), true)['data']['features'];
            $list_feature_ids = [];
            if($list_data !== null){
                foreach($list_data as $data){
                    $list_feature_ids[] = (int)$data["id"];
                }
            }
            $list_feature_ids[] = $feature_id;
            $body_update_list_feature = [
                'module_id' => $this->cgx_module_id,
                'feature_ids' => $list_feature_ids
            ];
            $response_update_module_feature = $this->client->request('POST', '/admin/v1/registering-feature', [
                'headers'  => $headers,
                'json' => $body_update_list_feature
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
            $access_feature = new AccessFeature;
            $access_feature->feature_key = $feature_key;
            $access_feature->feature_id = $feature_id;
            $access_feature->name = $name;
            $access_feature->description = $description;
            $access_feature->save();
            return response()->json(["success" => true, "message" => "Berhasil Menambahkan Fitur"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteFeature(Request $request)
    {
        $id = $request->get('id');
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        $access_feature = AccessFeature::where('feature_id',$id)->first();
        if($access_feature === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $response_module_member = $this->client->request('GET', '/admin/v1/module-member?module_id='.$this->cgx_module_id, [
                    'headers'  => $headers
                ]);
            $list_data = json_decode((string) $response_module_member->getBody(), true)['data']['features'];
            $list_feature_ids = [];
            foreach($list_data as $data){
                $value = (int)$data["id"];
                if($value !== $id){
                    $list_feature_ids[] = (int)$data["id"];
                }
            }
            $body_update_list_feature = [
                'module_id' => $this->cgx_module_id,
                'feature_ids' => $list_feature_ids
            ];
            $response_update_module_feature = $this->client->request('POST', '/admin/v1/registering-feature', [
                'headers'  => $headers,
                'json' => $body_update_list_feature
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
            $access_feature->delete();
            return response()->json(["success" => true, "message" => "Berhasil Menghapus Fitur"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    //Module (Group in CGX)
    public function getModules(Request $request)
    {
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('GET', '/admin/v1/group-feature?page=1&rows=50', [
                    'headers'  => $headers
                ]);
            $groups = json_decode((string) $response->getBody(), true)['data']['group_features'];
            $data = [];
            foreach($groups as $group){
                $detail_group_response = $this->client->request('GET', '/admin/v1/group-feature?key='.$group["key"], [
                    'headers'  => $headers
                ]);
                $detail = json_decode((string) $detail_group_response->getBody(), true)['data']['group_features'][0];
                $data[] = $detail;
            }
            return response(["success" => true, "message" => "Data Berhasil Diambil", "data" => $data]);
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

    public function addModule(Request $request)
    {
        $body_group = [
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('PUT', '/admin/v1/group-feature', [
                'headers'  => $headers,
                'json' => $body_group
            ]);
            $key = json_decode((string) $response->getBody(), true)['data']['key'];
            $body_feature = [
                'group_feature_key' => $key,
                'feature_ids' => $request->get('feature_ids', [])
            ];
            $response_feature = $this->client->request('POST', '/admin/v1/group-feature', [
                'headers'  => $headers,
                'json' => $body_feature
            ]);
            // return response(json_decode((string) $response_feature->getBody(), true));
            $response_feature = json_decode((string) $response_feature->getBody(), true);
            if(array_key_exists('error', $response_feature)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response_feature['error']['detail'],
                        "server_code" => $response_feature['error']['code'],
                        "status_detail" => $response_feature['error']['detail']
                    ]
                ]], 400);
            }
            else return response()->json(["success" => true, "message" => $response_feature['data']['message']]);
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

    public function deleteModule(Request $request)
    {
        $id = $request->get('id');
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('GET', '/admin/v1/group-feature?page=1&rows=50', [
                    'headers'  => $headers
                ]);
            $data = json_decode((string) $response->getBody(), true)['data']['group_features'];
            $search = array_search($id, array_column($data, 'id'));
            $selected_data = $data[$search];
            if($selected_data['id'] === $id){
                $body_group = [
                    'key' => $selected_data['key']
                ];
                $response_delete = $this->client->request('DELETE', '/admin/v1/group-feature', [
                    'headers'  => $headers,
                    'json' => $body_group
                ]);
                $response_delete = json_decode((string) $response_delete->getBody(), true)['data']['message'];
                return response()->json(["success" => true, "message" => "Berhasil Menghapus Data Module"]);
            } else {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Module Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Module Tidak Ditemukan"
                    ]
                ]], 400);
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

    public function updateModuleFeature(Request $request)
    {
        $body_group = [
            'group_feature_key' => $request->get('group_feature_key'),
            'feature_ids' => $request->get('feature_ids')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/group-feature', [
                'headers'  => $headers,
                'json' => $body_group
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

    // Account Feature
    public function updateFeatureAccount(Request $request)
    {
        // $group_ids = $request->get('group_ids');
        // feature ids for accessing cgx's company and account feature
        $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62];
        $group_ids = [
            [96]
        ];
        $feature_ids = [];
        foreach($group_ids as $group_id){
            foreach($group_id as $feature_id){
                $feature_ids[] = $feature_id; 
            }
        }
        $unique = array_unique($feature_ids);
        $uniques = [];
        foreach($unique as $u){
            $uniques[] = $u;
        }
        // return response()->json([$feature_ids, $uniques, array_merge($default_feature, $uniques)]);
        $body = [
            // 'account_id' => $request->get('account_id'),
            // 'feature_ids' => $request->get('feature_ids')
            'account_id' => 66,
            'feature_ids' => array_merge($default_feature, $uniques)
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
            return response(json_decode((string) $response->getBody(), true));
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