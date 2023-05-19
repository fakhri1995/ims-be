<?php 

namespace App\Services;
use App\User;
use App\Group;
use Exception;
use App\Services\GlobalService;

class GroupService{
    public function __construct()
    {
        $this->agent = true;
        $this->requester = false;
        $this->globalService = new GlobalService;
    }

    public function getAssignToList($request)
    {
        $assignable_type = $request->get('assignable_type', 1);
        $name = $request->get('name', null);
        if($assignable_type){
            $users = User::select('id','name','position')->with('profileImage')->whereHas('groups', function($q){
                $q->where('groups.id', 1);
            });
            if($name) $users = $users->where('users.name', 'like', "%".$name."%");
            $data = $users->limit(50)->get();
        } else {
            $groups = Group::select('id', 'name');
            if($name) $groups = $groups->where('groups.name', 'like', "%".$name."%");
            $data = $groups->limit(50)->get();
        }
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }

    public function getFilterGroups($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $name = $request->get('name', null);
            $type = $request->get('type', null);
            $groups = Group::select('id', 'name');
            if($type == 1) $groups = $groups->where('is_agent', true);
            else if($type == 2) $groups = $groups->where('is_agent', false);
            if($name) $groups = $groups->where('groups.name', 'like', "%".$name."%");
            $data = $groups->limit(50)->get();
            
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getFilterGroupsWithUsers($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $name = $request->get('name', null);
            $type = $request->get('type', null);
            $groups = Group::with("users","users.profileImage")->select('id', 'name');
            if($type == 1) $groups = $groups->where('is_agent', true);
            else if($type == 2) $groups = $groups->where('is_agent', false);
            if($name) $groups = $groups->where('groups.name', 'like', "%".$name."%");
            $data = $groups->limit(50)->get();
            
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Client Groups
    public function getGroups($is_agent)
    {
        try{
            $groups = Group::where('is_agent', $is_agent)->get();
            if(!count($groups)) return ["success" => true, "message" => "Data Masih Kosong", "data" => $groups, "status" => 200];
            
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $groups, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAgentGroups($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->getGroups($this->agent);
    }

    public function getRequesterGroups($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->getGroups($this->requester);
    }

    public function getGroup($id, $is_agent)
    {
        try{
            $group = Group::with('users')->find($id);
            if($group === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if($group->is_agent != $is_agent) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
            $group_user = $group->users->pluck('id');
            $group->makeHidden('users');
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["group_detail" => $group, "group_user" => $group_user], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAgentGroup($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->getGroup($id, $this->agent);
    }

    public function getRequesterGroup($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->getGroup($id, $this->requester);
    }

    private function addGroup($data, $is_agent)
    {
        $group = new Group;
        $group->name = $data['name'];
        $group->description = $data['description'];
        $group->is_agent = $is_agent;
        $group->group_head = $data['group_head'];
        $user_ids = $data['user_ids'];
        try{
            $group->save();
            $group->users()->attach($user_ids);
            
            return ["success" => true, "message" => "Group berhasil dibuat", "id" => $group->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAgentGroup($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->addGroup($data, $this->agent);
    }

    public function addRequesterGroup($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->addGroup($data, $this->requester);
    }

    public function updateGroup($data, $is_agent)
    {
        $id = $data['id'];
        $group = Group::find($id);
        if($group === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if($group->is_agent != $is_agent) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
        $group->name = $data['name'];
        $group->description = $data['description'];
        $group->group_head = $data['group_head'];
        $user_ids = $data['user_ids'];
        try{
            $group->save();
            $group->users()->sync($user_ids);

            return ["success" => true, "message" => "Group berhasil diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAgentGroup($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->updateGroup($data, $this->agent);
    }

    public function updateRequesterGroup($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->updateGroup($data, $this->requester);
    }

    public function deleteGroup($id, $is_agent)
    {
        if($id == 1) return ["success" => false, "message" => "Tidak Dapat Menghapus Group Engineer", "status" => 403];
        $group = Group::find($id);
        if($group === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        if($group->is_agent != $is_agent) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
        try{
            $group->users()->detach();
            $group->delete();
            return ["success" => true, "message" => "Group berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAgentGroup($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->deleteGroup($id, $this->agent);
    }

    public function deleteRequesterGroup($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->deleteGroup($id, $this->requester);
    }
}