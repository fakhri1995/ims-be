<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Depreciation;
use App\AccessFeature;
use Exception;

class DepreciationController extends Controller
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

    public function getDepreciations(Request $request)
    {
        // $check = $this->checkRoute("DEPRECATIONS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $depreciations = Depreciation::orderBy('nama','asc')->get();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $depreciations]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "DEPRECATIONS_GET";
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
            $depreciations = Depreciation::orderBy('nama','asc')->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $depreciations]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addDepreciation(Request $request)
    {
        // $check = $this->checkRoute("DEPRECATION_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $depreciation = new Depreciation;
        // $depreciation->nama = $request->get('nama');
        // $depreciation->jenis = $request->get('jenis');
        // $depreciation->tahun_penggunaan = $request->get('tahun_penggunaan');
        // $depreciation->deskripsi = $request->get('deskripsi');
        // try{
        //     $depreciation->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "DEPRECATION_ADD";
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
        $depreciation = new Depreciation;
        $depreciation->nama = $request->get('nama');
        $depreciation->jenis = $request->get('jenis');
        $depreciation->tahun_penggunaan = $request->get('tahun_penggunaan');
        $depreciation->deskripsi = $request->get('deskripsi');
        try{
            $depreciation->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateDepreciation(Request $request)
    {
        // $check = $this->checkRoute("DEPRECATION_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $depreciation = Depreciation::find($id);
        // if($depreciation === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        // $depreciation->nama = $request->get('nama');
        // $depreciation->jenis = $request->get('jenis');
        // $depreciation->tahun_penggunaan = $request->get('tahun_penggunaan');
        // $depreciation->deskripsi = $request->get('deskripsi');
        // try{
        //     $depreciation->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "DEPRECATION_UPDATE";
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
        $depreciation = Depreciation::find($id);
        if($depreciation === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $depreciation->nama = $request->get('nama');
        $depreciation->jenis = $request->get('jenis');
        $depreciation->tahun_penggunaan = $request->get('tahun_penggunaan');
        $depreciation->deskripsi = $request->get('deskripsi');
        try{
            $depreciation->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteDepreciation(Request $request)
    {
        // $check = $this->checkRoute("DEPRECATION_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $depreciation = Depreciation::find($id);
        // if($depreciation === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        // try{
        //     $depreciation->delete();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "DEPRECATION_DELETE";
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
        $depreciation = Depreciation::find($id);
        if($depreciation === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $depreciation->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    
}