<?php

namespace App\Services;
use App\User;
use Exception;
use App\Mail\ForgetPasswordMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class LoginService
{
    public function generate_token($email, $password){
        $token = Http::asForm()->post(config('service.passport.login_endpoint'), [
            'grant_type' => 'password',
            'client_id' => config('service.passport.client_id'),
            'client_secret' => config('service.passport.client_secret'),
            'username' => $email,
            'password' => $password,
        ]);

        return $token;
    }

    public function login($email, $password){
        $login = $this->generate_token($email, $password);

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
            if(strlen($password) < 8) return ["success" => false, "data" => "Password Minimal 8 Karakter", "status" => 400];
            auth()->user()->password = Hash::make($password);
            auth()->user()->save();
            return ["success" => true, "message" => "Password Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateProfile($request){
        try{
            $password = $request->get('password');
            if($password === '' || $password === null) {
                auth()->user()->name = $request->get('name');
                auth()->user()->phone_number = $request->get('phone_number');
                auth()->user()->profile_image = $request->get('profile_image');
                auth()->user()->save();
            } else {
                if(strlen($password) < 8) return ["success" => false, "data" => "Password Minimal 8 Karakter", "status" => 400];
                $confirm_password = $request->get('confirm_password');
                if($password !== $confirm_password) return ["success" => false, "data" => "Password Tidak Sama!", "status" => 400];
                auth()->user()->name = $request->get('name');
                auth()->user()->phone_number = $request->get('phone_number');
                auth()->user()->profile_image = $request->get('profile_image');
                auth()->user()->password = Hash::make($password);
                auth()->user()->save();
            }
            return ["success" => true, "message" => "Berhasil Memperbarui Data Profile", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function detailProfile(){
        auth()->user()->company;
        auth()->user()->company->makeHidden(['parent_id','singkatan','tanggal_pkp','penanggung_jawab','npwp','fax','email','website','deleted_at']);
        auth()->user()->makeHidden(['deleted_at', 'is_enabled', 'company_id']);
        auth()->user()->groups;
        auth()->user()->groups->makeHidden(['pivot']);
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

    public function mailForgetPassword($email){
        if(!$email) return ["success" => false, "data" => "Email Belum Diisi", "status" => 400];
        $user = User::where('email', $email)->first();
        if(!$user) return ["success" => false, "data" => "Email Belum Terdaftar", "status" => 400];
        
        $token_reset = Str::random(100);
        DB::table('password_resets')->insert(['email' => $email, 'token' => $token_reset, 'created_at' => date("Y-m-d H:i:s")]);

        $data = [
            'username' => $user->name,
            'token' => $token_reset,
            'subject' => 'Reset Password MIG Token'
        ];
        Mail::to($email)->send(new ForgetPasswordMail($data));
        return ["success" => true, "data" => "Email Token Lupa Password Telah Dikirim Ke Email Anda", "status" => 200];
    }

    public function resetPassword($password, $confirm_password, $token_reset){
        $token = DB::table('password_resets')->where('token', $token_reset)->first();
        if(!$token) return ["success" => false, "data" => "Invalid Token", "status" => 400];
        if(!$password) return ["success" => false, "data" => "Password Belum Terisi", "status" => 400];
        if($password !== $confirm_password) return ["success" => false, "data" => "Password Tidak Sama", "status" => 400];
        
        $user = User::where('email', $token->email)->first();
        if(!$user) return ["success" => false, "message" => "Id Dengan Email Tersebut Tidak Ditemukan", "status" => 400];
        try{
            $user->password = Hash::make($password);
            $user->save();
            DB::table('password_resets')->where('token', $token_reset)->delete();
            return ["success" => true, "data" => "Berhasil Merubah Password Akun", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
        
    }
}