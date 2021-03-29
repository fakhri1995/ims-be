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

    // Normal Route

    public function getBanks(Request $request)
    {
        // $protocol = "FITUR TEST Last Local";
        // $access_feature = AccessFeature::where('name',$protocol)->first();
        // if($access_feature === null) {
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
        //             "server_code" => 400,
        //             "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
        //         ]
        //     ]], 400);
        // }
        // $body = [
        //     'path_url' => $access_feature->key
        // ];
        // $headers = [
        //     'Authorization' => $request->header("Authorization"),
        //     'content-type' => 'application/json'
        // ];
        // try{
        //     $response = $this->client->request('POST', '/auth/v1/validate-feature', [
        //             'headers'  => $headers,
        //             'json' => $body
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     return $response;
        //     return $response['data']['is_success'];
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

    
}