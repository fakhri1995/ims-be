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
        $route_name = "FEATURES_GET";
        $response = $this->accessService->getFeatures($route_name);
        return response()->json($response, $response['status']);
    }

    // public function addFeature(Request $request)
    // {
    //     $route_name = "FEATURE_ADD";
    //     $data_request = [
    //         'name' => $request->get('name'),
    //         'description' => $request->get('description')
    //     ];
        
    //     $response = $this->accessService->addFeature($data_request, $route_name);
    //     return response()->json($response, $response['status']);
    // }

    // public function updateFeature(Request $request)
    // {
    //     $route_name = "FEATURE_UPDATE";
    //     $data_request = [
    //         'id' => $request->get('id'),
    //         'name' => $request->get('name'),
    //         'description' => $request->get('description')
    //     ];
        
    //     $response = $this->accessService->updateFeature($data_request, $route_name);
    //     return response()->json($response, $response['status']);
    // }

    // public function deleteFeature(Request $request)
    // {
    //     $route_name = "FEATURE_DELETE";
    //     $id = $request->get('id', null);
        
    //     $response = $this->accessService->deleteFeature($id, $route_name);
    //     return response()->json($response, $response['status']);
    // }

    // Module
    public function getModules(Request $request)
    {
        $route_name = "MODULES_GET";
        $response = $this->accessService->getModules($route_name, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addModule(Request $request)
    {
        $route_name = "MODULE_ADD";
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ];
        
        $response = $this->accessService->addModule($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addModuleFeature(Request $request)
    {
        $route_name = "MODULE_FEATURES_ADD";
        $data_request = [
            'id' => $request->get('id', null),
            'feature_ids' => $request->get('feature_ids', [])
        ];
        $response = $this->accessService->addModuleFeature($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateModule(Request $request)
    {
        $route_name = "MODULE_UPDATE";
        
        $data_request = [
            'id' => $request->get('id', null),
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ];
        
        $response = $this->accessService->updateModule($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteModuleFeature(Request $request)
    {
        $route_name = "MODULE_FEATURES_DELETE";
        $data_request = [
            'id' => $request->get('id', null),
            'feature_ids' => $request->get('feature_ids', [])
        ];
        $response = $this->accessService->deleteModuleFeature($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteModule(Request $request)
    {
        $route_name = "MODULE_DELETE";
        $id = $request->get('id', null);
        
        $response = $this->accessService->deleteModule($id, $route_name);
        return response()->json($response, $response['status']);
    }

    // Role
    public function getRoleUserFeatures(Request $request)
    {
        $route_name = "ROLE_USER_FEATURES_GET";
        $role_id = $request->get('id');
        $response = $this->accessService->getRoleUserFeatures($role_id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRoles(Request $request)
    {
        $route_name = "ROLES_GET";
        $response = $this->accessService->getRoles($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRole(Request $request)
    {
        $route_name = "ROLE_GET";
        $id = $request->get('id', null);
        $response = $this->accessService->getRole($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRole(Request $request)
    {
        $route_name = "ROLE_ADD";
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'feature_ids' => $request->get('feature_ids', [])
        ];

        $response = $this->accessService->addRole($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRole(Request $request)
    {
        $route_name = "ROLE_UPDATE";
        $data_request = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'feature_ids' => $request->get('feature_ids', [])
        ];

        $response = $this->accessService->updateRole($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRole(Request $request)
    {
        $route_name = "ROLE_DELETE";
        $id = $request->get('id', null);
        $response = $this->accessService->deleteRole($id, $route_name);
        return response()->json($response, $response['status']);
    }  

}