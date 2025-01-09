<?php

namespace App\Services;
use App\User;
use Exception;
use Illuminate\Support\Str;
use App\FirebaseAndroidToken;
use App\Services\FileService;
use App\Mail\ForgetPasswordMail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Services\OtpService;

class LoginService
{   
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

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
        $user = User::with('company:id,is_enabled')->where('email', $email)->first();
        if($user){
            if(!$user->company->is_enabled && $user->role != $this->globalService->guest_role_id){
                return [
                    "success" => false, 
                    "message" => "Perusahaan user sedang dinonaktifkan",
                    "status" => 400, 
                ];
            }

            if(!$user->is_enabled){
                return [
                    "success" => false, 
                    "message" => "User sedang dinonaktifkan",
                    "status" => 400, 
                ];
            }
        } else {
            return [
                "success" => false, 
                "message" => "Email Belum Terdaftar!",
                "status" => 400
            ];
        }
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

    public function logout($request){
        try {
            // Logout all token (all devices)
            // auth()->user()->tokens()->each(function ($token) {
            //     $token->delete();
            // });

            // Logout only one token 
            auth()->user()->token()->delete();

            $token = $request->get('token');
            if($token){
                FirebaseAndroidToken::where('token', $token)->delete();
            }

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

    public function addAndroidToken($request)
    {
        $token = $request->get('token');
        if(!$token) return ["success" => false, "message" => "Token Masih Kosong", "status" => 400];
        $firebase_android_token = FirebaseAndroidToken::where('token', $token)->first();
        $expires_at = date("Y-m-d H:i:s", strtotime("+10 years"));
        if($firebase_android_token) {
            $firebase_android_token->user_id = auth()->user()->id;
        } else {
            $firebase_android_token = new FirebaseAndroidToken;
            $firebase_android_token->token = $token;
            $firebase_android_token->user_id = auth()->user()->id;
        }
        $firebase_android_token->expires_at = $expires_at;
        $firebase_android_token->save();
        return ["success" => true, "message" => "Token android berhasil dimasukkan", "status" => 200];
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
                auth()->user()->save();
            } else {
                if(strlen($password) < 8) return ["success" => false, "data" => "Password Minimal 8 Karakter", "status" => 400];
                $confirm_password = $request->get('confirm_password');
                if($password !== $confirm_password) return ["success" => false, "data" => "Password Tidak Sama!", "status" => 400];
                auth()->user()->name = $request->get('name');
                auth()->user()->phone_number = $request->get('phone_number');
                auth()->user()->password = Hash::make($password);
                auth()->user()->save();
            }

            if($request->hasFile('profile_image')) {
                $fileService = new FileService;
                $file = $request->file('profile_image');
                $table = 'App\User';
                $description = 'profile_image';
                $folder_detail = 'Users';
                if(auth()->user()->profileImage->id){
                    $delete_file_response = $fileService->deleteForceFile(auth()->user()->profileImage->id);
                }
                $add_file_response = $fileService->addFile(auth()->user()->id, $file, $table, $description, $folder_detail);
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
        auth()->user()->profileImage;
        auth()->user()->attendanceForms;
        auth()->user()->attendanceForms->makeHidden(['updated_at', 'deleted_at', 'created_by']);
        auth()->user()->employee;
        if(auth()->user()->employee != null){  
            if(auth()->user()->employee->contract != null){
                auth()->user()->employee->contract->makeHidden('benefit','gaji_pokok','bpjs_ks','bpjs_tk_jht','bpjs_tk_jkk','bpjs_tk_jkm','bpjs_tk_jp','pph21');
            }
        }
       
        
        $list_feature = [];
        foreach(auth()->user()->roles as $role)
        {
            foreach($role->features as $feature){
                $list_feature[] = (object)[
                    "id" => $feature->id, 
                    "name" => $feature->name
                ];
            } 
            auth()->user()->roles->makeHidden(['features', 'description', 'deleted_at']);
        }
        auth()->user()->features = array_values(array_unique($list_feature, SORT_REGULAR));
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
            'url' => env('APP_URL_WEB'),
            'username' => $user->name,
            'token' => $token_reset,
            'subject' => 'Reset Password Akun MIG'
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

    public function resetPasswordOtp($request){
        $token = $request->token;
        $email = $request->email;
        $password = $request->password;
        $confirm_password = $request->confirm_password;

        $validate = DB::table('password_resets')->where('token', $token)->first();
        if(!$validate) return ["success" => false, "message" => "OTP tidak valid", "status" => 400];
        if(!$password) return ["success" => false, "data" => "Password Belum Terisi", "status" => 400];
        if($password !== $confirm_password) return ["success" => false, "data" => "Password Tidak Sama", "status" => 400];

        DB::table('password_resets')->delete($validate->id);
        DB::commit();
        $user = User::where('email', $email)->first();
        if(!$user) return ["success" => false, "message" => "Id Dengan Email Tersebut Tidak Ditemukan", "status" => 400];
        try{
            $user->password = Hash::make($password);
            $user->save();
            return ["success" => true, "data" => "Berhasil Merubah Password Akun", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function sendOtp($request){
        $otp_service = new OtpService;
        $email = $request->email;
        $otp = $otp_service->generate($email, 'numeric', 4, 10);
        Mail::to($email)->send(new OtpMail($otp));
        return ["success" => true, "data" => $otp, "status" => 200];
    }

    public function validateOtp($request){
        $otp_service = new OtpService;
        $token = $request->token;
        $email = $request->email;
        $res = $otp_service->validate($email, $token);
        if($res->status == false){
            return ["success" => false, "data" => $res->message, "status" => 200];
        }
        DB::table('password_resets')->insert(['email' => $email, 'token' => $token, 'created_at' => date("Y-m-d H:i:s")]);
        return ["success" => true, "data" => $res->message, "status" => 200];
    }
}