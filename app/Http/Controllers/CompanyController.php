<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyService;

class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    
    public function __construct()
    {
        $this->companyService = new CompanyService;
    }

    // General Company

    public function getAllCompanyList(Request $request)
    {
        $route_name = "COMPANY_LISTS_GET";
        $response = $this->companyService->getAllCompanyList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCompanyClientList(Request $request)
    {
        $route_name = "COMPANY_CLIENTS_GET";
        
        $response = $this->companyService->getCompanyClientList($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getMainLocations(Request $request)
    {
        $route_name = "COMPANY_MAIN_LOCATIONS_GET";
        
        $response = $this->companyService->getMainLocations($route_name);
        return response()->json($response, $response['status']);
    }

    public function getLocations(Request $request)
    {
        $route_name = "COMPANY_LOCATIONS_GET";

        $response = $this->companyService->getLocations($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getSubLocations(Request $request)
    {
        $route_name = "COMPANY_SUB_LOCATIONS_GET";
        
        $response = $this->companyService->getSubLocations($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getCompanyDetail(Request $request)
    {
        $route_name = "COMPANY_DETAIL_GET";
        
        $response = $this->companyService->getCompanyDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getSubCompanyDetail(Request $request)
    {
        $route_name = "COMPANY_SUB_DETAIL_GET";
        
        $response = $this->companyService->getSubCompanyDetail($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getSubCompanyProfile(Request $request)
    {
        $route_name = "COMPANY_SUB_PROFILE_GET";
        
        $response = $this->companyService->getSubCompanyProfile($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateMainCompany(Request $request)
    {
        $route_name = "COMPANY_MAIN_UPDATE";
        
        $response = $this->companyService->updateMainCompany($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateCompany(Request $request)
    {
        $route_name = "COMPANY_UPDATE";
        
        $response = $this->companyService->updateSpecificCompany($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function companyActivation(Request $request)
    {
        $route_name = "COMPANY_STATUS";

        $response = $this->companyService->companyActivation($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteCompany(Request $request)
    {
        $route_name = "COMPANY_DELETE";
        
        $response = $this->companyService->deleteCompany($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //Company Tree List Routes

    public function getBranchCompanyList(Request $request)
    {
        $route_name = "COMPANY_BRANCHS_GET";
        
        $response = $this->companyService->getBranchCompanyList($route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getClientCompanyList(Request $request)
    {
        $route_name = "COMPANY_CLIENTS_GET";
        
        $response = $this->companyService->getClientCompanyList($route_name);
        return response()->json($response, $response['status']);
    }
    
    //Add Company Routes
    
    public function addCompanyBranch(Request $request)
    {
        $route_name = "COMPANY_BRANCH_ADD";
        
        $response = $this->companyService->addCompanyBranch($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addCompanyClient(Request $request)
    {
        $route_name = "COMPANY_CLIENT_ADD";
        
        $response = $this->companyService->addCompanyClient($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addCompanySub(Request $request)
    {
        $route_name = "COMPANY_SUB_ADD";
        
        $response = $this->companyService->addCompanySub($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // public function companyActivation(Request $request)
    // {
    //     $body = [
    //         'is_enabled' => $request->get('is_enabled'),
    //         'company_id' => $request->get('company_id')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/change-status-activation-company', [
    //                 'headers'  => $headers,
    //                 'json' => $body
    //             ]);
    //         $response = json_decode((string) $response->getBody(), true);
    //         if(array_key_exists('error', $response)) {
    //             return response()->json(["success" => false, "message" => (object)[
    //                 "errorInfo" => [
    //                     "status" => 400,
    //                     "reason" => $response['error']['detail'],
    //                     "server_code" => $response['error']['code'],
    //                     "status_detail" => $response['error']['detail']
    //                 ]
    //             ]], 400);
    //         }
    //         else return response()->json(["success" => true, "message" => $response['data']['message']]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]], $error_response->getStatusCode());
    //     }
    // }
}