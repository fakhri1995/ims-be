<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AccessFeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    protected $token = "eyJjb21wYW55X2lkIjo2NiwiY29tcGFueV9yb2xlIjoxLCJleHAiOjE2MTI5NDY1ODksImlhdCI6MTYxMjkyODU4OSwiaXNzIjoiY2d4LmNvLmlkIiwiand0X2NyZWF0ZV90aW1lIjoiMjAyMS0wMi0xMFQwMzo0MzowOS4wNDY4NTE5NDFaIiwicmVnaXN0ZXJlZF9mZWF0dXJlcyI6IjU2Njc3NjAzMTUyMDc0MDg3ODU4MTc2Iiwicm9sZSI6MSwidXNlcl9pZCI6NjN9.1Rpa5i5r9TjfRdWNjKPH4Fo6fBiEWTYiNgLucRpPUt8";

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
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/admin/v1/account-module?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by'], [
                    'headers'  => $headers
                ]);
        return $response;
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
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/admin/v1/account-feature?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by']
                .'&module_id='.$params['module_id']
                .'&company_id='.$params['company_id'], [
                    'headers'  => $headers
                ]);
        
        return $response;
    }

    public function addAccessModule(Request $request)
    {
        $body = [
            "name" => $request->get('name'),
            "description" => $request->get('description'),
            "can_mark_as_default" => $request->get('can_mark_as_default')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/account-module', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }

    public function addAccessFeature(Request $request)
    {
        $body = [
            "name" => $request->get('name'),
            "description" => $request->get('description'),
            "account_module_id" => $request->get('account_module_id'),
            "path_url" => $request->get('path_url'),
            "method" => $request->get('method')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/account-feature', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
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
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/account-feature', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }

    public function updateModuleCompany(Request $request)
    {
        $body = [
            'company_id' => $request->get('company_id'),
            'module_ids' => $request->get('module_ids')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/update-module', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }

    public function updateFeatureAccount(Request $request)
    {
        $body = [
            'account_id' => $request->get('account_id'),
            'feature_ids' => $request->get('feature_ids')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/update-feature', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }
}