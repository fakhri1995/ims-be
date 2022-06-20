<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AndroidService;

class AndroidController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->androidService = new AndroidService;
    }

    public function getMainAndroid(Request $request)
    {
        $response = $this->androidService->getMainAndroid($request);
        return response()->json($response, $response['status']);
    }
}