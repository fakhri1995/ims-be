<?php

namespace App\Http\Controllers;

use App\Services\ContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->contractService = new ContractService;
    }

    public function getContracts(Request $request){
        $route_name = "CONTRACTS_GET";

        $response = $this->contractService->getContracts($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getContract(Request $request){
        $route_name = "CONTRACT_GET";

        $response = $this->contractService->getContract($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addContract(Request $request){
        $route_name = "CONTRACT_ADD";

        $response = $this->contractService->addContract($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateContract(Request $request){
        $route_name = "CONTRACT_UPDATE";

        $response = $this->contractService->updateContract($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteContract(Request $request){
        $route_name = "CONTRACT_DELETE";

        $response = $this->contractService->deleteContract($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getContractActiveCount(Request $request){
        $route_name = "CONTRACT_ACTIVE_COUNT_GET";

        $response = $this->contractService->getContractActiveCount($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Notes
    public function addContractLogNotes(Request $request){
        $route_name = "CONTRACT_LOG_NOTES_ADD";   
        
        $response = $this->contractService->addContractLogNotes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteContractLogNotes(Request $request){
        $route_name = "CONTRACT_LOG_NOTES_DELETE";   
        
        $response = $this->contractService->deleteContractLogNotes($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // Template
    public function updateContractTemplate(Request $request){
        $route_name = "CONTRACT_TEMPLATE_UPDATE";   
        
        $response = $this->contractService->updateContractTemplate($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getContractTemplate(Request $request){
        $route_name = "CONTRACT_TEMPLATE_GET";   
        
        $response = $this->contractService->getContractTemplate($request, $route_name);
        return response()->json($response, $response['status']);
    }

}