<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Vendor;
use Exception;

class VendorController extends Controller
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

    public function getVendors(Request $request)
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
            $id = $request->get('id', null);
            if($id === null){
                $vendors = Vendor::all();
                if($vendors->isEmpty()) return response()->json(["success" => false, "message" => "Vendor Account Belum Terdaftar"]);
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $vendors]);
            } else {
                $vendor = Vendor::find($id);
                if($vendor === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $vendor]);
            }
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addVendor(Request $request)
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
        $vendor = new Vendor;
        $vendor->name = $request->get('name');
        $vendor->singkatan_nama = $request->get('singkatan_nama');
        $vendor->npwp = $request->get('npwp');
        $vendor->pic = $request->get('pic');
        $vendor->jabatan_pic = $request->get('jabatan_pic');
        $vendor->alamat = $request->get('alamat');
        $vendor->provinsi = $request->get('provinsi');
        $vendor->kab_kota = $request->get('kab_kota');
        $vendor->kode_pos = $request->get('kode_pos');
        $vendor->telepon = $request->get('telepon');
        $vendor->fax = $request->get('fax');
        $vendor->email = $request->get('email');
        $vendor->website = $request->get('website');
        try{
            $vendor->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateVendor(Request $request)
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
        $vendor = Vendor::find($id);
        if($vendor === null) return response()->json(["success" => false, "message" => "Id Tidak Ditemukan"]);
        $vendor->name = $request->get('name');
        $vendor->singkatan_nama = $request->get('singkatan_nama');
        $vendor->npwp = $request->get('npwp');
        $vendor->pic = $request->get('pic');
        $vendor->jabatan_pic = $request->get('jabatan_pic');
        $vendor->alamat = $request->get('alamat');
        $vendor->provinsi = $request->get('provinsi');
        $vendor->kab_kota = $request->get('kab_kota');
        $vendor->kode_pos = $request->get('kode_pos');
        $vendor->telepon = $request->get('telepon');
        $vendor->fax = $request->get('fax');
        $vendor->email = $request->get('email');
        $vendor->website = $request->get('website');
        try{
            $vendor->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteVendor(Request $request)
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
        $vendor = Vendor::find($id);
        if($vendor === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $vendor->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }   
}