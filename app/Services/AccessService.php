<?php

namespace App\Services;
use App\Services\GlobalService;
use App\AccessFeature;
use App\Module;
use App\Role;
use App\User;
use Exception;

class AccessService
{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    // Features
    public function getFeatures($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $features = AccessFeature::get();
        if(!count($features)) return ["success" => true, "message" => "Fitur masih kosong", "data" => $features, "status" => 200];
        else return ["success" => true, "message" => "Users Berhasil Diambil", "data" => $features, "status" => 200 ];
    }

    // public function addFeature($data, $route_name)
    // {
    //     $access = $this->globalService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     try{
    //         $access_feature = new AccessFeature;
    //         $access_feature->name = $data['name'];
    //         $access_feature->description = $data['description'];
    //         $access_feature->save();
    //         return ["success" => true, "message" => "Feature Berhasil Dibuat", "id" => $access_feature->id, "status" => 200];
    //     } catch(Exception $err){
    //         return ["success" => false, "message" => $err, "status" => 400];
    //     }
    // }

    // public function updateFeature($data, $route_name)
    // {
    //     $access = $this->globalService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     $id = $data['id'];
    //     try{
    //         $access_feature = AccessFeature::find($id);
    //         if($access_feature === null) ["success" => false, "message" => "Id Feature Tidak Ditemukan", "status" => 400];
    //         $access_feature->name = $data['name'];
    //         $access_feature->description = $data['description'];
    //         $access_feature->save();
    //         return ["success" => true, "message" => "Feature Berhasil Diubah", "status" => 200];
    //     } catch(Exception $err){
    //         return ["success" => false, "message" => $err, "status" => 400];
    //     }
    // }

    // public function deleteFeature($id, $route_name)
    // {
    //     $access = $this->globalService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     $access_feature = AccessFeature::find($id);
    //     if($access_feature === null) return ["success" => false, "message" => "Id Feature Tidak Ditemukan", "status" => 400];
    //     try{
    //         $access_feature->delete();
    //         $access_feature->roles()->detach();
    //         return ["success" => true, "message" => "Feature Berhasil Dihapus", "status" => 200];
    //     } catch(Exception $err){
    //         return ["success" => false, "message" => $err, "status" => 400];
    //     }
    // }

    //Modules
    public function getModules($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $modules = Module::get();
        if(!count($modules)) return ["success" => true, "message" => "Fitur masih kosong", "data" => $modules, "status" => 200];
        $access_feature = AccessFeature::select('id', 'name')->get();
        foreach($modules as $module){
            if(count($module->features)){
                $features = $access_feature->whereIn('id', $module->features);
                $new_features = [];
                foreach($features as $feature) $new_features[] = $feature;
                $module->features = $new_features;
            }
        }
        return ["success" => true, "message" => "Modules Berhasil Diambil", "data" => $modules, "status" => 200 ];
    }

    public function addModule($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $module = new Module;
            $module->name = $data['name'];
            $module->description = $data['description'];
            $module->features = [];
            $module->save();
            return ["success" => true, "message" => "Module Berhasil Dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateModule($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $data['id'];
        try{
            $module = Module::find($id);
            if($module === null) return ["success" => false, "message" => "Id Module Tidak Ditemukan", "status" => 400];
            $module->name = $data['name'];
            $module->description = $data['description'];
            $module->save();
            return ["success" => true, "message" => "Module Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addModuleFeature($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $module = Module::find($data['id']);
        if($module === null) return ["success" => false, "message" => "Id Module Tidak Ditemukan", "status" => 400];
        foreach($module->features as $feature){
            $data['feature_ids'][] = $feature;
        }
        $data['feature_ids'] = array_unique($data['feature_ids']);
        $new_features = [];
        foreach($data['feature_ids'] as $feature){
            $new_features[] = $feature;
        }
        try{
            $module->features = $new_features;
            $module->save();
            return ["success" => true, "message" => "Feature Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteModuleFeature($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $module = Module::find($data['id']);
        if($module === null) return ["success" => false, "message" => "Id Module Tidak Ditemukan", "status" => 400];
        $temp_features = array_diff($module->features, $data['feature_ids']);
        $new_features = [];
        foreach($temp_features as $feature){
            $new_features[] = $feature;
        }
        try{
            $module->features = $new_features;
            $module->save();
            return ["success" => true, "message" => "Feature Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteModule($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $module = Module::find($id);
        if($module === null) return ["success" => false, "message" => "Id Module Tidak Ditemukan", "status" => 400];
        try{
            $module->delete();
            return ["success" => true, "message" => "Module Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //Roles
    public function getRoleUserFeatures($role_id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $role = Role::find($role_id);
            if(!$role) return ["success" => false, "message" => "Id Role Tidak Ditemukan", "status" => 400];
            $role_user_ids = $role->users->pluck('id')->toArray();
            $role_feature = $role->features;
            $data_module = Module::get();
            $list_feature = [];
            foreach($role_feature as $feature){
                $list_module = [];
                foreach($data_module as $module){
                    if(in_array($feature->id, $module->features)) $list_module[] = $module->name;
                }
                $feature->list_module = $list_module;
            }
            $users = User::select('name', 'is_enabled AS status')->whereIn('id', $role_user_ids)->get();
            $data = ["users" => $users, "features" => $role_feature];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];  
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRoles($route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $roles = Role::withCount('users')->get();
            if(!count($roles)) return ["success" => true, "message" => "Roles masih kosong", "data" => $roles, "status" => 200];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $roles, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRole($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $role = Role::with('features')->find($id);
            if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $role, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRole($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $role = new Role;
            $role->name = $data['name'];
            $role->description = $data['description'];
            $feature_ids = $data['feature_ids'];
            $role->save();

            $role_id = $role->id;
            $role->features()->sync($data['feature_ids']);
            return ["success" => true, "message" => "Role Berhasil Disimpan", "id" => $role->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRole($data, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $data['id'];
        $role = Role::with('features')->find($id);
        if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400]);
        $role->name = $data['name'];
        $role->description = $data['description'];
        $feature_ids = $data['feature_ids'];
        try{
            $role->save();
            $role->features()->sync($feature_ids);

            return ["success" => true, "message" => "Role Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRole($id, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        if($id == 1) return ["success" => false, "message" => "Tidak Dapat Menghapus Role Super Admin", "status" => 403];
        $role = Role::with('features')->find($id);
        if($role === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $role->delete();
            $role->features()->detach();
            return ["success" => true, "message" => "Role Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}