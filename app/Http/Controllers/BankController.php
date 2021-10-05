<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BankService;

class BankController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->bankService = new BankService;
    }

    // MIG Banks
    public function getMainBanks(Request $request)
    {
        $route_name = "MAIN_BANKS_GET";

        $response = $this->bankService->getMainBanks($route_name);
        return response()->json($response, $response['status']);
    }

    public function addMainBank(Request $request)
    {
        $route_name = "MAIN_BANK_ADD";
        $data_request = [
            'name' => $request->get('name'),
            'account_number' => $request->get('account_number'),
            'owner' => $request->get('owner'),
            'currency' => $request->get('currency')
        ];
        
        $response = $this->bankService->addMainBank($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateMainBank(Request $request)
    {
        $route_name = "MAIN_BANK_UPDATE";
        $data_request = [
            'id' => $request->get('id', null),
            'name' => $request->get('name'),
            'account_number' => $request->get('account_number'),
            'owner' => $request->get('owner'),
            'currency' => $request->get('currency')
        ];
        
        $response = $this->bankService->updateMainBank($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteMainBank(Request $request)
    {
        $route_name = "MAIN_BANK_DELETE";
        $id = $request->get('id', null);
        
        $response = $this->bankService->deleteMainBank($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // Client Banks
    public function getClientBanks(Request $request)
    {
        $route_name = "CLIENT_BANKS_GET";
        $id = $request->get('id', null);
        
        $response = $this->bankService->getClientBanks($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addClientBank(Request $request)
    {
        $route_name = "CLIENT_BANK_ADD";
        $data_request = [
            'company_id' => $request->get('company_id'),
            'name' => $request->get('name'),
            'account_number' => $request->get('account_number'),
            'owner' => $request->get('owner'),
            'currency' => $request->get('currency')
        ];
        
        $response = $this->bankService->addClientBank($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateClientBank(Request $request)
    {
        $route_name = "CLIENT_BANK_UPDATE";
        $data_request = [
            'id' => $request->get('id', null),
            'name' => $request->get('name'),
            'account_number' => $request->get('account_number'),
            'owner' => $request->get('owner'),
            'currency' => $request->get('currency')
        ];
        
        $response = $this->bankService->updateClientBank($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteClientBank(Request $request)
    {
        $route_name = "CLIENT_BANK_DELETE";
        $id = $request->get('id', null);
        
        $response = $this->bankService->deleteClientBank($id, $route_name);
        return response()->json($response, $response['status']);
    }
}