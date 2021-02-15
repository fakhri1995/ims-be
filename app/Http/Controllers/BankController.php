<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use App\Bank;
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
        $headers = ['Authorization' => $request->get('token')];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            return response()->json(["success" => false, "message" => "Invalid Token", "detail" => Psr7\Message::toString($err->getResponse())]);
        }
        try{
            $bank = Bank::all();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $bank]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err]);
        }
    }

    public function addBank(Request $request)
    {
        $headers = ['Authorization' => $request->get('token')];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            return response()->json(["success" => false, "message" => "Invalid Token", "detail" => Psr7\Message::toString($err->getResponse())]);
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
            return response()->json(["success" => false, "message" => $err]);
        }
    }

    public function updateBank(Request $request)
    {
        $headers = ['Authorization' => $request->get('token')];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            return response()->json(["success" => false, "message" => "Invalid Token", "detail" => Psr7\Message::toString($err->getResponse())]);
        }
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $bank->company_id = $request->get('company_id');
        $bank->name = $request->get('name');
        $bank->account_number = $request->get('account_number');
        $bank->owner = $request->get('owner');
        $bank->currency = $request->get('currency');
        try{
            $bank->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err]);
        }
    }

    public function deleteBank(Request $request)
    {
        $headers = ['Authorization' => $request->get('token')];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            return response()->json(["success" => false, "message" => "Invalid Token", "detail" => Psr7\Message::toString($err->getResponse())]);
        }
        $id = $request->get('id', null);
        $bank = Bank::find($id);
        if($bank === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $bank->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err]);
        }
    }

    
}