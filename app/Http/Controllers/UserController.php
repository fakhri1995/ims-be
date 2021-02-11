<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    // protected $token = "eyJjb21wYW55X2lkIjo2NiwiY29tcGFueV9yb2xlIjoxLCJleHAiOjE2MTI5NDY1ODksImlhdCI6MTYxMjkyODU4OSwiaXNzIjoiY2d4LmNvLmlkIiwiand0X2NyZWF0ZV90aW1lIjoiMjAyMS0wMi0xMFQwMzo0MzowOS4wNDY4NTE5NDFaIiwicmVnaXN0ZXJlZF9mZWF0dXJlcyI6IjU2Njc3NjAzMTUyMDc0MDg3ODU4MTc2Iiwicm9sZSI6MSwidXNlcl9pZCI6NjN9.1Rpa5i5r9TjfRdWNjKPH4Fo6fBiEWTYiNgLucRpPUt8";

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function login(Request $request)
    {
        $body = [
            // 'email' => 'hanif@mitramas.com',
            // 'password' => 'm1tramas'
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
            'Authorization' => $request->get('token')
        ];
        $response = $this->client->request('POST', '/auth/v1/logout', [
                    'headers'  => $headers
                ]);
        
        return $response;
    }

    
    public function changePassword(Request $request)
    {
        $headers = ['Authorization' => $request->get('token')];
        $body = ['new_password' => $request->get('new_password')];
        $response = $this->client->request('POST', '/account/v1/change-password', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        
        return $response;
    }

    public function detailProfile(Request $request)
    {
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        
        return $response;
    }
}