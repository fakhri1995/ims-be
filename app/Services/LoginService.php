<?php

namespace App\Services;
use App\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Exception;

class LoginService
{
    public function login($email, $password){
        $login = Http::asForm()->post(config('service.passport.login_endpoint'), [
            'grant_type' => 'password',
            'client_id' => config('service.passport.client_id'),
            'client_secret' => config('service.passport.client_secret'),
            'username' => $email,
            'password' => $password,
        ]);

        if(isset($login['error'])){
            $response = [
                "success" => false, 
                "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => $login['error'],
                        "server_code" => 401,
                        "status_detail" => $login['error_description']
                    ]
                ],
                "status" => 401
            ];
        } else {
            $response = [
                "success" => true,
                "data" => [
                    "message" => "success generate token",
                    "token" => $login['access_token']
                ],
                "status" => 200
            ];
        }

        return $response;
    }

    public function logout(){
        try {
            auth()->user()->tokens()->each(function ($token) {
                $token->delete();
            });

            return [
                "success" => true,
                "message" => "You have been successfully logged out",
                "status" => 200
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => $e->getMessage(),
                "status" => 400
            ];
        }
    }

    public function changePassword($password){
        try{
            auth()->user()->password = Hash::make($password);
            auth()->user()->save();
            return ["success" => true, "message" => "Password Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function detailProfile(){
        auth()->user()->company;
        auth()->user()->company->makeHidden(['parent_id','singkatan','tanggal_pkp','penanggung_jawab','npwp','fax','email','website','deleted_at']);
        auth()->user()->makeHidden(['deleted_at', 'is_enabled', 'company_id']);
        $list_feature = [];
        foreach(auth()->user()->roles as $role)
        {
            foreach($role->features as $feature) $list_feature[] = $feature->id;
            auth()->user()->roles->makeHidden(['features', 'description', 'deleted_at']);
        }
        auth()->user()->features = array_values(array_unique($list_feature, SORT_NUMERIC));
        return [
                "success" => true,
                "data" => auth()->user(),
                "status" => 200
            ];
    }
}