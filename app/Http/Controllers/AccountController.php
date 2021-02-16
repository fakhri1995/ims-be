<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
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

    public function getAccountDetail(Request $request)
    {
        $login_id = $request->get('login_id');
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-account?id='.$login_id, [
                    'headers'  => $headers
                ]);
            return $response;
        }catch(ClientException $err){
            return response()->json(["success" => false, "detail" => Psr7\Message::toString($err->getResponse())]);
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
            return $response;
        }catch(ClientException $err){
            return response()->json(["success" => false, "detail" => Psr7\Message::toString($err->getResponse())]);
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
            return $response;
        }catch(ClientException $err){
            return response()->json(["success" => false, "detail" => Psr7\Message::toString($err->getResponse())]);
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
            return $response;
        }catch(ClientException $err){
            return response()->json(["success" => false, "detail" => Psr7\Message::toString($err->getResponse())]);
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
            return $response;
        }catch(ClientException $err){
            return response()->json(["success" => false, "detail" => Psr7\Message::toString($err->getResponse())]);
        }
    }
}