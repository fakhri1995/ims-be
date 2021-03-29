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

    public function getAccessFeature(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by'),
            'module_id' => $request->get('module_id'),
            'company_id' => $request->get('company_id')
        ];
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/account-feature?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by']
                .'&module_id='.$params['module_id']
                .'&company_id='.$params['company_id'], [
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

    public function addAccessModule(Request $request)
    {
        $body = [
            "name" => $request->get('name'),
            "description" => $request->get('description'),
            "can_mark_as_default" => $request->get('can_mark_as_default')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];    
        try{
            $response = $this->client->request('POST', '/admin/v1/account-module', [
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

    public function addAccessFeature(Request $request)
    {
        $name = $request->get('name');
        $description = $request->get('description');
        $body = [
            "name" => $name,
            "description" => $description,
            "method" => $request->get('method')
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
            $key = json_decode((string) $response->getBody(), true)['data']['feature_detail']['feature_key'];
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
            $access_feature = AccessFeature::where('name',$name)->first();
            if($access_feature === null){
                $access_feature = new AccessFeature;
            }
            $access_feature = new AccessFeature;
            $access_feature->name = $name;
            $access_feature->description = $description;
            $access_feature->key = $key;
            $access_feature->save();
            return response()->json(["success" => true, "message" => "Berhasil Menambahkan Akses Fitur"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        
    }

    public function updateAccessFeature(Request $request)
    {
        $body = [
            "feature_id" => $request->get('feature_id'),
            "name" => $request->get('name'),
            "description" => $request->get('description'),
            "account_module_id" => $request->get('account_module_id'),
            "path_url" => $request->get('path_url'),
            "method" => $request->get('method')
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

    public function updateModuleCompany(Request $request)
    {
        $body = [
            'company_id' => $request->get('company_id'),
            'module_ids' => $request->get('module_ids')
        ];
        $headers = [
            'Authorization' => $request->header("Authorization"),
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/admin/v1/update-module', [
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

    public function updateFeatureAccount(Request $request)
    {
        $body = [
            'account_id' => $request->get('account_id'),
            'feature_ids' => $request->get('feature_ids')
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