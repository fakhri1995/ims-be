<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Bank;
use App\AccessFeature;
use Exception;

class BankController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function checkRoute($name, $auth)
    {
        $protocol = $name;
        $access_feature = AccessFeature::where('name',$protocol)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
                    "server_code" => 400,
                    "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
                ]
            ]];
        }
        $body = [
            'path_url' => $access_feature->feature_key
        ];
        $headers = [
            'Authorization' => $auth,
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/validate-feature', [
                    'headers'  => $headers,
                    'json' => $body
            ]);
            return ["success" => true];
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]];
        }
    }
    // Normal Route

    public function getBanks(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        try{
            $company_id = $request->get('id');
            $banks = Bank::where('company_id', $company_id)->get();
            if($banks->isEmpty()) return response()->json(["success" => false, "message" => "Bank Account Belum Terdaftar"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $banks]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addBank(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        $bank = new Bank;
        $bank->company_id = $request->get('company_id');
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateBank(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $bank->company_id = $request->get('company_id');
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteBank(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $bank->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    // MIG Banks
    public function getMainBanks(Request $request)
    {
        $check = $this->checkRoute("MAIN_BANKS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $banks = Bank::where('company_id', 66)->get();
            if($banks->isEmpty()) return response()->json(["success" => false, "message" => "Bank Account Belum Terdaftar"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $banks]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        //MAIN_BANKS_GET
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // try{
        //     $banks = Bank::where('company_id', 66)->get();
        //     if($banks->isEmpty()) return response()->json(["success" => false, "message" => "Bank Account Belum Terdaftar"]);
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $banks]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function addMainBank(Request $request)
    {
        $check = $this->checkRoute("MAIN_BANK_ADD", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $bank = new Bank;
        $bank->company_id = 66;
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }

        // MAIN_BANK_ADD
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // $bank = new Bank;
        // $bank->company_id = 66;
        // $bank->name = $request->get('name');
        // $bank->account_number = $request->get('account_number');
        // $bank->owner = $request->get('owner');
        // $bank->currency = $request->get('currency');
        // try{
        //     $bank->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function updateMainBank(Request $request)
    {
        $check = $this->checkRoute("MAIN_BANK_UPDATE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        if($bank->company_id !== 66){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                ]
            ]], 401);
        }
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // MAIN_BANK_UPDATE
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // $id = $request->get('id', null);
        // $bank = Bank::find($id);
        // if($bank === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        // if($bank->company_id !== 66){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //         ]
        //     ]], 401);
        // }
        // $bank->name = $request->get('name');
        // $bank->account_number = $request->get('account_number');
        // $bank->owner = $request->get('owner');
        // $bank->currency = $request->get('currency');
        // try{
        //     $bank->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function deleteMainBank(Request $request)
    {
        $check = $this->checkRoute("MAIN_BANK_DELETE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        if($bank->company_id !== 66){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                ]
            ]], 401);
        }
        try{
            $bank->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // MAIN_BANK_DELETE
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // $id = $request->get('id', null);
        // $bank = Bank::find($id);
        // if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // if($bank->company_id !== 66){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //         ]
        //     ]], 401);
        // }
        // try{
        //     $bank->delete();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    // Client Banks
    public function getClientBanks(Request $request)
    {
        $check = $this->checkRoute("CLIENT_BANKS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $company_id = $request->get('id');
            if($company_id === 66){
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 401,
                        "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                        "server_code" => 401,
                        "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                    ]
                ]], 401);
            }
            $banks = Bank::where('company_id', $company_id)->get();
            if($banks->isEmpty()) return response()->json(["success" => false, "message" => "Bank Account Belum Terdaftar"]);
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $banks]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // // CLIENT_BANKS_GET
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // try{
        //     $company_id = $request->get('id');
        //     if($company_id === 66){
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 401,
        //                 "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //                 "server_code" => 401,
        //                 "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //             ]
        //         ]], 401);
        //     }
        //     $banks = Bank::where('company_id', $company_id)->get();
        //     if($banks->isEmpty()) return response()->json(["success" => false, "message" => "Bank Account Belum Terdaftar"]);
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $banks]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function addClientBank(Request $request)
    {
        $check = $this->checkRoute("CLIENT_BANK_ADD", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $company_id = $request->get('company_id');
        if($company_id === 66){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Mengakses Bank Perusahaan Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Mengakses Bank Perusahaan Ini",
                ]
            ]], 401);
        }
        $bank = new Bank;
        $bank->company_id = $company_id;
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // CLIENT_BANK_ADD
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // $company_id = $request->get('company_id');
        // if($company_id === 66){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Mengakses Bank Perusahaan Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Mengakses Bank Perusahaan Ini",
        //         ]
        //     ]], 401);
        // }
        // $bank = new Bank;
        // $bank->company_id = $company_id;
        // $bank->name = $request->get('name');
        // $bank->account_number = $request->get('account_number');
        // $bank->owner = $request->get('owner');
        // $bank->currency = $request->get('currency');
        // try{
        //     $bank->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function updateClientBank(Request $request)
    {
        $check = $this->checkRoute("CLIENT_BANK_UPDATE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        if($bank->company_id === 66){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                ]
            ]], 401);
        }
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // CLIENT_BANK_UPDATE
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // $id = $request->get('id', null);
        // $bank = Bank::find($id);
        // if($bank === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        // if($bank->company_id === 66){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //         ]
        //     ]], 401);
        // }
        // $bank->name = $request->get('name');
        // $bank->account_number = $request->get('account_number');
        // $bank->owner = $request->get('owner');
        // $bank->currency = $request->get('currency');
        // try{
        //     $bank->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function deleteClientBank(Request $request)
    {
        $check = $this->checkRoute("CLIENT_BANK_DELETE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        if($bank->company_id === 66){
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 401,
                    "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                    "server_code" => 401,
                    "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
                ]
            ]], 401);
        }
        try{
            $bank->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // CLIENT_BANK_DELETE
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
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
        // $id = $request->get('id', null);
        // $bank = Bank::find($id);
        // if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // if($bank->company_id === 66){
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 401,
        //             "reason" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //             "server_code" => 401,
        //             "status_detail" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini",
        //         ]
        //     ]], 401);
        // }
        // try{
        //     $bank->delete();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }
}