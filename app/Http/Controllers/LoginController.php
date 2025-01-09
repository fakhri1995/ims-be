<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginService;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    
    public function __construct()
    {
        $this->loginService = new LoginService;
        // $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function login(Request $request)
    {
        $email = $request->input("email");
        $password = $request->input("password");

        $response = $this->loginService->login($email, $password);
        return response()->json($response, $response['status']);
    }

    public function logout(Request $request)
    {
        $response = $this->loginService->logout($request);
        return response()->json($response, $response['status']);
        
        // $headers = [
        //     'content-type' => 'application/json',
        //     'Authorization' => $request->header("Authorization")
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/logout', [
        //             'headers'  => $headers
        //         ]);
        //     return response(json_decode((string) $response->getBody(), true));
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
    }

    public function addAndroidToken(Request $request)
    {
        $response = $this->loginService->addAndroidToken($request);
        return response()->json($response, $response['status']);
    }
    
    public function changePassword(Request $request)
    {
        $password = $request->input("new_password");
        $response = $this->loginService->changePassword($password);
        return response()->json($response, $response['status']);
    }

    public function detailProfile(Request $request)
    {
        $response = $this->loginService->detailProfile();
        return response()->json($response, $response['status']);
    }

    public function updateProfile(Request $request)
    {
        $response = $this->loginService->updateProfile($request);
        return response()->json($response, $response['status']);
    }

    public function mailForgetPassword(Request $request)
    {
        $email = $request->input("email");
        $response = $this->loginService->mailForgetPassword($email);
        return response()->json($response, $response['status']);
    }

    public function resetPassword(Request $request)
    {
        $token = $request->input("token");
        $password = $request->input("password");
        $confirm_password = $request->input("confirm_password");
        $response = $this->loginService->resetPassword($password, $confirm_password, $token);
        return response()->json($response, $response['status']);
    }

    public function resetPasswordOtp(Request $request)
    {
        $response = $this->loginService->resetPasswordOtp($request);
        return response()->json($response, $response['status']);
    }

    public function sendOtp(Request $request)
    {
        $response = $this->loginService->sendOtp($request);
        return response()->json($response, $response['status']);
    }
    public function validateOtp(Request $request)
    {
        $response = $this->loginService->validateOtp($request);
        return response()->json($response, $response['status']);
    }
}