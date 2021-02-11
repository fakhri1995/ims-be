<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    protected $client;
    // protected $token = "eyJjb21wYW55X2lkIjo2NiwiY29tcGFueV9yb2xlIjoxLCJleHAiOjE2MTI5NDY1ODksImlhdCI6MTYxMjkyODU4OSwiaXNzIjoiY2d4LmNvLmlkIiwiand0X2NyZWF0ZV90aW1lIjoiMjAyMS0wMi0xMFQwMzo0MzowOS4wNDY4NTE5NDFaIiwicmVnaXN0ZXJlZF9mZWF0dXJlcyI6IjU2Njc3NjAzMTUyMDc0MDg3ODU4MTc2Iiwicm9sZSI6MSwidXNlcl9pZCI6NjN9.1Rpa5i5r9TjfRdWNjKPH4Fo6fBiEWTYiNgLucRpPUt8";

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function getCompanyDetail(Request $request)
    {
        $params = [
            'id' => 66
        ];
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/admin/v1/get-company?id='.$params['id'], [
                    'headers'  => $headers
                ]);
        return $response;
    }

    public function getCompanyList(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by'),
            'is_enabled' => $request->get('is_enabled', null),
            'role' => $request->get('role')
        ];
        $headers = ['Authorization' => $request->get('token')];
        $response = $this->client->request('GET', '/admin/v1/get-list-company?page='.$params['page']
                .'&rows='.$params['rows']
                .'&order_by='.$params['order_by']
                .'&is_enabled='.$params['is_enabled']
                .'&role='.$params['role'], [
                    'headers'  => $headers
                ]);
        
        return $response;
    }

    public function addCompanyMember(Request $request)
    {
        $body = [
            'name' => $request->get('name'),
            'role' => $request->get('role'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo'),
            'member_of_company' => $request->get('member_of_company')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/add-new-company', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }

    public function updateCompanyDetail(Request $request)
    {
        $body = [
            'id' => $request->get('id'),
            'company_name' => $request->get('company_name'),
            'role' => $request->get('role'),
            'address' => $request->get('address'),
            'phone_number' => $request->get('phone_number'),
            'image_logo' => $request->get('image_logo')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/update-company', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }

    public function companyActivation(Request $request)
    {
        $body = [
            'is_enabled' => $request->get('is_enabled'),
            'company_id' => $request->get('company_id')
        ];
        $headers = [
            'Authorization' => $request->get('token'),
            'content-type' => 'application/json'
        ];
        $response = $this->client->request('POST', '/admin/v1/change-status-activation-company', [
                    'headers'  => $headers,
                    'json' => $body
                ]);
        return $response;
    }
}