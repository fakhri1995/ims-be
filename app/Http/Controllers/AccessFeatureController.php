<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccessService;

class AccessFeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    
    public function __construct()
    {
        $this->accessService = new AccessService;
    }

    // Feature
    public function getFeatures(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("MAIN_COMPANY_GET");
        // if($access["success"] === false) return response()->json($access);
        $response = $this->accessService->getFeatures();
        return response()->json($response, $response['status']);
    }

    public function addFeature(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        // if($access["success"] === false) return response()->json($access);
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ];
        
        $response = $this->accessService->addFeature($data_request);
        return response()->json($response, $response['status']);
    }

    public function deleteFeature(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        // if($access["success"] === false) return response()->json($access);
        $id = $request->get('id', null);
        
        $response = $this->accessService->deleteFeature($id);
        return response()->json($response, $response['status']);
    }

    // Module
    public function getModules(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("MAIN_COMPANY_GET");
        // if($access["success"] === false) return response()->json($access);
        $response = $this->accessService->getModules();
        return $response;
        return response()->json($response, $response['status']);
    }

    public function addModule(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        // if($access["success"] === false) return response()->json($access);
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ];
        
        $response = $this->accessService->addModule($data_request);
        return response()->json($response, $response['status']);
    }

    public function addModuleFeature(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        // if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('id', null),
            'feature_ids' => $request->get('feature_ids', [])
        ];
        $response = $this->accessService->addModuleFeature($data_request);
        return response()->json($response, $response['status']);
    }

    // public function updateModule(Request $request)
    // {
    //     // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
    //     // if($access["success"] === false) return response()->json($access);
    //     $data_request = [
    //         'id' => $request->get('id'),
    //         'name' => $request->get('name'),
    //         'description' => $request->get('description')
    //     ];
        
    //     $response = $this->accessService->updateModule($data_request);
    //     return response()->json($response, $response['status']);
    // }

    public function deleteModuleFeature(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        // if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('id', null),
            'feature_ids' => $request->get('feature_ids', [])
        ];
        $response = $this->accessService->deleteModuleFeature($data_request);
        return response()->json($response, $response['status']);
    }

    public function deleteModule(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("COMPANY_BRANCH_ADD");
        // if($access["success"] === false) return response()->json($access);
        $id = $request->get('id', null);
        
        $response = $this->accessService->deleteModule($id);
        return response()->json($response, $response['status']);
    }

    // Role
    public function getRoleUserFeatures(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("ROLE_USER_FEATURES_GET");
        // if($access["success"] === false) return response()->json($access);
        $role_id = $request->get('id');
        $response = $this->accessService->getRoleUserFeatures($role_id);
        return response()->json($response, $response['status']);
    }

    public function getRoles(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("ROLES_GET");
        // if($access["success"] === false) return response()->json($access);
        $response = $this->accessService->getRoles();
        return response()->json($response, $response['status']);
    }

    public function getRole(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("ROLE_GET");
        // if($access["success"] === false) return response()->json($access);
        $id = $request->get('id', null);
        $response = $this->accessService->getRole($id);
        return response()->json($response, $response['status']);
    }

    public function addRole(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("ROLE_ADD");
        // if($access["success"] === false) return response()->json($access);
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'feature_ids' => $request->get('feature_ids', [])
        ];

        $response = $this->accessService->addRole($data_request);
        return response()->json($response, $response['status']);
    }

    public function updateRole(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("ROLE_UPDATE");
        // if($access["success"] === false) return response()->json($access);
        $data_request = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'feature_ids' => $request->get('feature_ids', [])
        ];

        $response = $this->accessService->updateRole($data_request);
        return response()->json($response, $response['status']);
    }

    public function deleteRole(Request $request)
    {
        // $access = $this->checkRouteService->checkRoute("ROLE_DELETE");
        // if($access["success"] === false) return response()->json($access);
        $id = $request->get('id', null);
        $response = $this->accessService->deleteRole($id);
        return response()->json($response, $response['status']);
    }  

}