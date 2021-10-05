<?php 

namespace App\Services;
use App\Services\CheckRouteService;
use App\Bank;
use Exception;

class BankService{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }

    // MIG Banks
    public function getMainBanks($route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        $session_company_id = auth()->user()->company_id;
        try{
            $banks = Bank::where('company_id', $session_company_id)->get();
            if($banks->isEmpty()) return ["success" => true, "message" => "Bank Account Belum Terdaftar", "data" => $banks, "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $banks, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addMainBank($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $session_company_id = auth()->user()->company_id;
        $bank = new Bank;
        $bank->company_id = $session_company_id;
        $bank->name = $data['name'];
        $bank->account_number = $data['account_number'];
        $bank->owner = $data['owner'];
        $bank->currency = $data['currency'];
        try{
            $bank->save();
            return ["success" => true, "message" => "Bank Account berhasil ditambahkan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateMainBank($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $session_company_id = auth()->user()->company_id;
        $id = $data['id'];
        $bank = Bank::find($id);
        if($bank === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($bank->company_id !== $session_company_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini", "status" => 401];
        
        $bank->name = $data['name'];
        $bank->account_number = $data['account_number'];
        $bank->owner = $data['owner'];
        $bank->currency = $data['currency'];
        try{
            $bank->save();
            return ["success" => true, "message" => "Bank Account berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteMainBank($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $session_company_id = auth()->user()->company_id;
        $bank = Bank::find($id);
        if($bank === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($bank->company_id !== $session_company_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini", "status" => 401];
        
        try{
            $bank->delete();
            return ["success" => true, "message" => "Bank Account berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Client Banks
    public function getClientBanks($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            if($id === 1) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini", "status" => 401];
            $banks = Bank::where('company_id', $id)->get();
            if($banks->isEmpty()) return ["success" => true, "message" => "Bank Account Belum Terdaftar", "data" => $banks, "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "status" => 200, "data" => $banks];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addClientBank($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $company_id = $data['company_id'];
        if($company_id === 1) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini", "status" => 401];
        
        $bank = new Bank;
        $bank->company_id = $company_id;
        $bank->name = $data['name'];
        $bank->account_number = $data['account_number'];
        $bank->owner = $data['owner'];
        $bank->currency = $data['currency'];
        try{
            $bank->save();
            return ["success" => true, "message" => "Bank Account berhasil dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateClientBank($data, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $data['id'];
        $bank = Bank::find($id);
        if($bank === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($bank->company_id === 1) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini", "status" => 401];
        
        $bank->name = $data['name'];
        $bank->account_number = $data['account_number'];
        $bank->owner = $data['owner'];
        $bank->currency = $data['currency'];
        try{
            $bank->save();
            return ["success" => true, "message" => "Bank Account berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteClientBank($id, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $bank = Bank::find($id);
        if($bank === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($bank->company_id === 1) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Bank Ini", "status" => 401];
        try{
            $bank->delete();
            return ["success" => true, "message" => "Bank Account berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}