<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyService;
use App\Services\CheckRouteService;

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
        $this->checkRouteService = new CheckRouteService;
    }

    public function getLocations(Request $request)
    {
        $company_id = $request->get('company_id');
        $response = $this->companyService->getLocations($company_id);
        return response()->json($response, $response['status']);
    }

    //MIG Routes

    public function getMainCompanyDetail(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("MAIN_COMPANY_GET");
        if($access["success"] === false) return response()->json($access);
        $response = $this->companyService->getMainCompanyDetail();
        return response()->json($response, $response['status']);
    }

    public function updateMainCompany(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("MAIN_COMPANY_UPDATE");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => 1,
            'company_name' => $request->get('company_name'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo'),
            'singkatan' => $request->get('singkatan'),
            'tanggal_pkp' => $request->get('tanggal_pkp'),
            'penanggung_jawab' => $request->get('penanggung_jawab'),
            'npwp' => $request->get('npwp'),
            'fax' => $request->get('fax'),
            'email' => $request->get('email'),
            'website' => $request->get('website')
        ];

        $response = $this->companyService->updateMainCompany($data_request);
        return response()->json($response, $response['status']);
    }

    //MIG Branch Routes
    
    public function getCompanyBranchDetail(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_GET");
        if($access["success"] === false) return response()->json($access);
        $id = $request->get('id', null);
        $response = $this->companyService->getCompanyBranchDetail($id);
        return response()->json($response, $response['status']);
    }

    public function getBranchCompanyList(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_BRANCHS_GET");
        if($access["success"] === false) return response()->json($access);
        $response = $this->companyService->getBranchCompanyList();
        return response()->json($response, $response['status']);
    }
    
    public function addCompanyBranch(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo', null),
            'parent_id' => $request->get('parent_id')
        ];
        
        $response = $this->companyService->addCompanyBranch($data_request);
        return response()->json($response, $response['status']);
    }

    public function updateCompanyBranch(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_UPDATE");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('id'),
            'company_name' => $request->get('company_name'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo'),
            'singkatan' => $request->get('singkatan'),
            'tanggal_pkp' => $request->get('tanggal_pkp'),
            'penanggung_jawab' => $request->get('penanggung_jawab'),
            'npwp' => $request->get('npwp'),
            'fax' => $request->get('fax'),
            'email' => $request->get('email'),
            'website' => $request->get('website')
        ];

        $response = $this->companyService->updateCompanyBranch($data_request);
        return response()->json($response, $response['status']);
    }

    public function companyBranchActivation(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_STATUS");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('company_id'),
            'is_enabled' => $request->get('is_enabled')
        ];

        $response = $this->companyService->companyBranchActivation($data_request);
        return response()->json($response, $response['status']);
    }
    
    //MIG Client Routes
    
    public function getCompanyClientDetail(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_CLIENT_GET");
        if($access["success"] === false) return response()->json($access);
        $id = $request->get('id', null);
        $response = $this->companyService->getCompanyClientDetail($id);
        return response()->json($response, $response['status']);
    }

    public function getClientCompanyList(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_CLIENTS_GET");
        if($access["success"] === false) return response()->json($access);
        $response = $this->companyService->getClientCompanyList();
        return response()->json($response, $response['status']);
    }
    
    public function getCompanyClientList(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_CLIENTS_GET");
        if($access["success"] === false) return response()->json($access);
        $response = $this->companyService->getCompanyClientList();
        return response()->json($response, $response['status']);
    }

    public function addCompanyClient(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_CLIENT_ADD");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo', null),
            'parent_id' => $request->get('parent_id')
        ];
        
        $response = $this->companyService->addCompanyClient($data_request);
        return response()->json($response, $response['status']);
    }

    public function updateCompanyClient(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_CLIENT_UPDATE");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('id'),
            'company_name' => $request->get('company_name'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo'),
            'singkatan' => $request->get('singkatan'),
            'tanggal_pkp' => $request->get('tanggal_pkp'),
            'penanggung_jawab' => $request->get('penanggung_jawab'),
            'npwp' => $request->get('npwp'),
            'fax' => $request->get('fax'),
            'email' => $request->get('email'),
            'website' => $request->get('website')
        ];

        $response = $this->companyService->updateCompanyClient($data_request);
        return response()->json($response, $response['status']);
    }

    public function companyClientActivation(Request $request)
    {
        $access = $this->checkRouteService->checkRoute("COMPANY_CLIENT_STATUS");
        if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('company_id'),
            'is_enabled' => $request->get('is_enabled')
        ];

        $response = $this->companyService->companyClientActivation($data_request);
        return response()->json($response, $response['status']);
    }

    // public function getCompanyDetail(Request $request)
    // {
    //     $company_id = $request->get('company_id');
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/admin/v1/get-company?id='.$company_id, [
    //                 'headers'  => $headers
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
    //         } else {
    //             try{
    //                 $company = Company::find($response['data']['company_id']);
    //                 if($company === null){
    //                     $response['data']['singkatan'] = '-';
    //                     $response['data']['tanggal_pkp'] = null;
    //                     $response['data']['penanggung_jawab'] = '-';
    //                     $response['data']['npwp'] = '-';
    //                     $response['data']['fax'] = '-';
    //                     $response['data']['email'] = '-';
    //                     $response['data']['website'] = '-';
    //                 } else {
    //                     $response['data']['singkatan'] = $company->singkatan;
    //                     $response['data']['tanggal_pkp'] = $company->tanggal_pkp;
    //                     $response['data']['penanggung_jawab'] = $company->penanggung_jawab;
    //                     $response['data']['npwp'] = $company->npwp;
    //                     $response['data']['fax'] = $company->fax;
    //                     $response['data']['email'] = $company->email;
    //                     $response['data']['website'] = $company->website;
    //                 }
    //             } catch(Exception $err){
    //                 return response()->json(["success" => false, "message" => $err], 400);
    //             } 
    //             return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);
    //         }
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
    
    // public function getCompanyList(Request $request)
    // {
    //     $params = [
    //         'page' => $request->get('page'),
    //         'rows' => $request->get('rows'),
    //         'order_by' => $request->get('order_by'),
    //         'is_enabled' => $request->get('is_enabled', null),
    //         'role' => $request->get('role')
    //     ];
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/admin/v1/get-list-company?page='.$params['page']
    //             .'&rows='.$params['rows']
    //             .'&order_by='.$params['order_by']
    //             .'&is_enabled='.$params['is_enabled']
    //             .'&role='.$params['role'], [
    //                 'headers'  => $headers
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
    //         else return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $response]);
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


    // public function addCompanyMember(Request $request)
    // {
    //     $body = [
    //         'name' => $request->get('name'),
    //         'role' => $request->get('role'),
    //         'address' => $request->get('address'),
    //         'phone_number' => $request->get('phone_number'),
    //         'image_logo' => $request->get('image_logo', null),
    //         'parent_id' => $request->get('parent_id')
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/add-new-company', [
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

    // public function updateCompanyDetail(Request $request)
    // {
    //     $id = $request->get('id');
    //     $body = [
    //         'id' => $id,
    //         'company_name' => $request->get('company_name'),
    //         'role' => $request->get('role'),
    //         'address' => $request->get('address'),
    //         'phone_number' => $request->get('phone_number'),
    //         'image_logo' => $request->get('image_logo', null)
    //     ];
    //     $headers = [
    //         'Authorization' => $request->header("Authorization"),
    //         'content-type' => 'application/json'
    //     ];
    //     try{
    //         $response = $this->client->request('POST', '/admin/v1/update-company', [
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
    //         // else return response()->json(["success" => true, "message" => $response['data']['message']]);
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

    //     try{
    //         $company = Company::find($id);
    //         if($company === null){
    //             $company = new Company;
    //             $company->id = $id;
    //         }
    //         $company->singkatan = $request->get('singkatan');
    //         $company->tanggal_pkp = $request->get('tanggal_pkp');
    //         $company->penanggung_jawab = $request->get('penanggung_jawab');
    //         $company->npwp = $request->get('npwp');
    //         $company->fax = $request->get('fax');
    //         $company->email = $request->get('email');
    //         $company->website = $request->get('website');
    //         $company->save();
    //         return response()->json(["success" => true, "message" => "Company Profile Berhasil Diproses"]);
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err], 400);
    //     }
    // }

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