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

    public function __construct()
    {
        
    }

    public function login()
    {
        // $client = new Client(['base_uri' => 'https://peaceful-meadow-45867.herokuapp.com/']);
        // $response = $client->request('GET', '/programs');

        $client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
        $body = [
            'email' => 'hanif@mitramas.com',
            'password' => 'm1tramas'
        ];
        $response = $client->request('POST', '/auth/v1/login', [
                    'headers'  => ['content-type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $body
                ]);
        
        return $response;
    }

    // Temp Token
    // eyJjb21wYW55X2lkIjo2NiwiY29tcGFueV9yb2xlIjoxLCJleHAiOjE2MTI4Njk3NjMsImlhdCI6MTYxMjg1MTc2MywiaXNzIjoiY2d4LmNvLmlkIiwiand0X2NyZWF0ZV90aW1lIjoiMjAyMS0wMi0wOVQwNjoyMjo0My4yMjQwMDYwNjRaIiwicmVnaXN0ZXJlZF9mZWF0dXJlcyI6IjU2Njc3NjAzMTUyMDc0MDg3ODU4MTc2Iiwicm9sZSI6MSwidXNlcl9pZCI6NjN9.v9XiuMSXYX7Fu92rnbQAArLB7k-YOuRNPSLxwZTIDao
    
    public function detailProfile()
    {
        $client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
        $token = "eyJjb21wYW55X2lkIjo2NiwiY29tcGFueV9yb2xlIjoxLCJleHAiOjE2MTI4NzM3NzIsImlhdCI6MTYxMjg1NTc3MiwiaXNzIjoiY2d4LmNvLmlkIiwiand0X2NyZWF0ZV90aW1lIjoiMjAyMS0wMi0wOVQwNzoyOTozMi4yMDAxMzYyNDJaIiwicmVnaXN0ZXJlZF9mZWF0dXJlcyI6IjU2Njc3NjAzMTUyMDc0MDg3ODU4MTc2Iiwicm9sZSI6MSwidXNlcl9pZCI6NjN9.DjguhA94VRizZyMlGraraI4M7RZnoN9Io3z0xSsA1Ko";
        $headers = [
            'Authorization' => $token
        ];
        $response = $client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        
        return $response;
    }
}