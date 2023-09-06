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

    protected $contractService;

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

    //Invoice
    public function getContractInvoices(Request $request){
        $route_name = "CONTRACT_INVOICES_GET";

        $response = $this->contractService->getContractInvoices($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getContractInvoice(Request $request){
        $route_name = "CONTRACT_INVOICE_GET";

        $response = $this->contractService->getContractInvoice($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addContractInvoice(Request $request){
        $route_name = "CONTRACT_INVOICES_ADD";

        $response = $this->contractService->addContractInvoice($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateContractInvoice(Request $request){
        $route_name = "CONTRACT_INVOICES_UPDATE";

        $response = $this->contractService->updateContractInvoice($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteContractInvoice(Request $request){
        $route_name = "CONTRACT_INVOICES_DELETE";

        $response = $this->contractService->deleteContractInvoice($request, $route_name);
        return response()->json($response, $response['status']);
    }

     // list for detail contrack
     public function getContractHistories(Request $request){
        $route_name = "CONTRACT_HISTORIES_GET";

        $response = $this->contractService->getContractHistories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getContractHistory(Request $request){
        $route_name = "CONTRACT_HISTORY_GET";

        $response = $this->contractService->getContractHistory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addContractHistory(Request $request){
        $route_name = "CONTRACT_HISTORY_ADD";

        $response = $this->contractService->addContractHistory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateContractHistory(Request $request){
        $route_name = "CONTRACT_HISTORY_UPDATE";

        $response = $this->contractService->updateContractHistory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteContractHistory(Request $request){
        $route_name = "CONTRACT_HISTORY_DELETE";

        $response = $this->contractService->deleteContractHistory($request, $route_name);
        return response()->json($response, $response['status']);
    }

}
