<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;


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
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function login(Request $request)
    {
        $body = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/login', [
                    'headers'  => ['content-type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $body
                ]);      
            return $response;
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "error" => (object)[
                "status" => $error_response->getStatusCode(),
                "reason" => $error_response->getReasonPhrase(),
                "server_code" => json_decode($error_response->getBody())->error->code,
                "status_detail" => json_decode($error_response->getBody())->error->detail
            ]]);
        }
    }

    public function logout(Request $request)
    {
        $headers = [
            'content-type' => 'application/json',
            'Authorization' => $request->header("Authorization")
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/logout', [
                    'headers'  => $headers
                ]);
            return $response;
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "error" => (object)[
                "status" => $error_response->getStatusCode(),
                "reason" => $error_response->getReasonPhrase(),
                "server_code" => json_decode($error_response->getBody())->error->code,
                "status_detail" => json_decode($error_response->getBody())->error->detail
            ]]);
        }
    }

    
    public function changePassword(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        $body = ['new_password' => $request->get('new_password')];
        try{
            $response = $this->client->request('POST', '/account/v1/change-password', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
            return $response;
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "error" => (object)[
                "status" => $error_response->getStatusCode(),
                "reason" => $error_response->getReasonPhrase(),
                "server_code" => json_decode($error_response->getBody())->error->code,
                "status_detail" => json_decode($error_response->getBody())->error->detail
            ]]);
        }
    }

    public function detailProfile(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
            return $response;
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "error" => (object)[
                "status" => $error_response->getStatusCode(),
                "reason" => $error_response->getReasonPhrase(),
                "server_code" => json_decode($error_response->getBody())->error->code,
                "status_detail" => json_decode($error_response->getBody())->error->detail
            ]]);
        }
    }
}