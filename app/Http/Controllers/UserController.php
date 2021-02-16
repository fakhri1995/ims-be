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
        $response = $this->client->request('POST', '/auth/v1/login', [
                    'headers'  => ['content-type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $body
                ]);      
        return $response;
    }

    public function logout(Request $request)
    {
        $headers = [
            'content-type' => 'application/json',
            'Authorization' => $request->header("Authorization")
        ];
        $response = $this->client->request('POST', '/auth/v1/logout', [
                    'headers'  => $headers
                ]);
        return $response;
    }

    
    public function changePassword(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        $body = ['new_password' => $request->get('new_password')];
        $response = $this->client->request('POST', '/account/v1/change-password', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
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
            return response()->json(["success" => false, "detail" => Psr7\Message::toString($err->getResponse())]);
        }
    }
}