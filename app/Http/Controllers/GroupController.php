<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GroupService;

class GroupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    public function __construct()
    {
        $this->groupService = new GroupService;
    }

    public function getAssignToList(Request $request)
    {
        // $route_name = "AGENT_GROUPS_GET";
        $response = $this->groupService->getAssignToList($request);
        return response()->json($response, $response['status']);
    }

    public function getFilterGroups(Request $request)
    {
        $route_name = "FILTER_GROUPS_GET";
        
        $response = $this->groupService->getFilterGroups($request, $route_name);
        return response()->json($response, $response['status']);
    }
    
    public function getAgentGroups(Request $request)
    {
        $route_name = "AGENT_GROUPS_GET";
        
        $response = $this->groupService->getAgentGroups($route_name);
        return response()->json($response, $response['status']);
    }

    public function getAgentGroup(Request $request)
    {
        $route_name = "AGENT_GROUP_GET";
        
        $id = $request->get('id', null);
        
        $response = $this->groupService->getAgentGroup($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAgentGroup(Request $request)
    {
        $route_name = "AGENT_GROUP_ADD";
        
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'group_head' => $request->get('group_head'),
            'user_ids' => $request->get('user_ids', [])
        ];
        
        $response = $this->groupService->addAgentGroup($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAgentGroup(Request $request)
    {
        $route_name = "AGENT_GROUP_UPDATE";
        
        $data_request = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'group_head' => $request->get('group_head'),
            'user_ids' => $request->get('user_ids', [])
        ];
        
        $response = $this->groupService->updateAgentGroup($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAgentGroup(Request $request)
    {
        $route_name = "AGENT_GROUP_GET";
        
        $id = $request->get('id', null);
        
        $response = $this->groupService->deleteAgentGroup($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getRequesterGroups(Request $request)
    {
        $route_name = "REQUESTER_GROUPS_GET";
        
        $response = $this->groupService->getRequesterGroups($route_name);
        return response()->json($response, $response['status']);
    }

    public function getRequesterGroup(Request $request)
    {
        $route_name = "REQUESTER_GROUP_GET";
        
        $id = $request->get('id', null);
        
        $response = $this->groupService->getRequesterGroup($id, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addRequesterGroup(Request $request)
    {
        $route_name = "REQUESTER_GROUP_ADD";
        
        $data_request = [
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'group_head' => $request->get('group_head'),
            'user_ids' => $request->get('user_ids', [])
        ];
        
        $response = $this->groupService->addRequesterGroup($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateRequesterGroup(Request $request)
    {
        $route_name = "REQUESTER_GROUP_UPDATE";
        
        $data_request = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'group_head' => $request->get('group_head'),
            'user_ids' => $request->get('user_ids', [])
        ];
        
        $response = $this->groupService->updateRequesterGroup($data_request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteRequesterGroup(Request $request)
    {
        $route_name = "REQUESTER_GROUP_GET";
        
        $id = $request->get('id', null);
        
        $response = $this->groupService->deleteRequesterGroup($id, $route_name);
        return response()->json($response, $response['status']);
    }

    
}