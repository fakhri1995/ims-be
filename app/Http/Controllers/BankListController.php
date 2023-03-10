<?php

namespace App\Http\Controllers;

use App\Services\BankListService;
use Illuminate\Http\Request;

class BankListController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->bankService = new BankListService;
    }

    // MIG Banks
    public function getBankLists(Request $request)
    {
        $route_name = "BANK_LISTS_GET";

        $response = $this->bankService->getBankLists($request, $route_name);
        return response()->json($response, $response['status']);
    }
}