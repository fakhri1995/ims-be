<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AccountController extends Controller
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

    public function getAccountDetail(Request $request)
    {
        $params = [
            'id' => $request->get('id')
        ];
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/admin/v1/get-account?id='.$params['id'], [
                    'headers'  => $headers
                ]);
        return $response;
    }

    public function getAccountList(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by'),
            'company_id' => $request->get('company_id')
        ];
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/admin/v1/get-list-account?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by']
                .'&company_id='.$params['company_id'], [
                    'headers'  => $headers
                ]);
        
        return $response;
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
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/add-new-account', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
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
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/update-account', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }

    public function accountActivation(Request $request)
    {
        $body = [
            'is_enabled' => $request->get('is_enabled'),
            'user_id' => $request->get('user_id')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/change-status-activation', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }
}