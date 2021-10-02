<?php

namespace App\Services;
use App\AccessFeature;
use App\Module;
use App\Role;
use App\RoleFeaturePivot;
use App\UserRolePivot;
use App\User;
use Exception;

class AccessService
{
    // Features
    public function getFeatures()
    {
        $features = AccessFeature::select('id','feature_id', 'feature_key','name','description')->get();
        if(!count($features)) return ["success" => true, "message" => "Fitur masih kosong", "data" => $features, "status" => 200];
        else return ["success" => true, "message" => "Users Berhasil Diambil", "data" => $features, "status" => 200 ];
    }

    public function addFeature($data)
    {
        try{
            $access_feature = new AccessFeature;
            $access_feature->name = $data['name'];
            $access_feature->description = $data['description'];
            $access_feature->feature_id = 1;
            $access_feature->feature_key = "-";
            $access_feature->save();
            return ["success" => true, "message" => "Feature Berhasil Dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteFeature($id)
    {
        $access_feature = AccessFeature::find($id);
        if($access_feature === null) return ["success" => false, "message" => "Id Feature Tidak Ditemukan", "status" => 400];
        try{
            $access_feature->delete();
            return ["success" => true, "message" => "Feature Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //Modules
    public function getModules()
    {
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

    public function addModule($data)
    {
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

    public function addModuleFeature($data)
    {
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

    public function deleteModuleFeature($data)
    {
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

    public function deleteModule($id)
    {
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
    public function getRoleUserFeatures($role_id)
    {
        try{
            $role_user_ids = UserRolePivot::where('role_id', $role_id)->pluck('user_id')->toArray();
            $role_feature_ids = RoleFeaturePivot::where('role_id', $role_id)->pluck('feature_id')->toArray();
            $features = AccessFeature::select('id','name', 'description')->get();
            $data_module = Module::get();
            $list_feature = [];
            foreach($role_feature_ids as $feature_id){
                foreach($features as $feature){
                    if($feature->id === $feature_id){
                        $list_module = [];
                        foreach($data_module as $module){
                            if(in_array($feature_id, $module->features)) $list_module[] = $module->name;
                        }
                        $feature['list_module'] = $list_module;
                        $list_feature[] = $feature;
                        break;
                    }
                }
            }
            $list_user = [];
            $users = User::select('fullname AS name', 'is_enabled AS status')->whereIn('user_id', $role_user_ids)->get();
            $data = ["users" => $users, "features" => $list_feature];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];  
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRoles()
    {
        try{
            $roles = Role::all();
            if(!count($roles)) return ["success" => true, "message" => "Roles masih kosong", "data" => $roles, "status" => 200];
            foreach($roles as $role){
                $role->member = UserRolePivot::where('role_id', $role->id)->count();
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $roles, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getRole($id)
    {
        try{
            $role = Role::find($id);
            if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            // return $role;
            $role_feature_ids = RoleFeaturePivot::where('role_id', $id)->pluck('feature_id');
            $features = AccessFeature::get();
            foreach($role_feature_ids as $role_feature_id){
                $role_feature = $features->where('id', $role_feature_id)->first();
                if($role_feature === null) {
                    $role_feature['id'] = $role_feature_id;
                    $role_feature['feature_id'] = "Data Tidak Ditemukan";
                    $role_feature['name'] = "Data Tidak Ditemukan";
                    $role_feature['description'] = "Data Tidak Ditemukan";
                    $role_feature['feature_key'] = "Data Tidak Ditemukan";
                } 
                $role_features[] = $role_feature;
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => ["role_detail" => $role, "role_features" => $role_features], "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addRole($data)
    {
        try{
            $role = new Role;
            $role->name = $data['name'];
            $role->description = $data['description'];
            $feature_ids = $data['feature_ids'];
            $role->save();

            $role_id = $role->id;
            $feature_ids = array_unique($feature_ids);
            foreach($feature_ids as $feature_id){
                $pivot = new RoleFeaturePivot;
                $pivot->role_id = $role_id;
                $pivot->feature_id = $feature_id;
                $pivot->save();
            }
            return ["success" => true, "message" => "Role Berhasil Disimpan", "id" => $role->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateRole($data)
    {
        $id = $data['id'];
        $role = Role::find($id);
        if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400]);
        $role->name = $data['name'];
        $role->description = $data['description'];
        $feature_ids = $data['feature_ids'];
        try{
            $role->save();

            $role_feature_ids = RoleFeaturePivot::where('role_id', $id)->pluck('feature_id')->toArray();
            if(!count($role_feature_ids)) {
                $feature_ids = array_unique($feature_ids);
                foreach($feature_ids as $feature_id){
                    $pivot = new RoleFeaturePivot;
                    $pivot->role_id = $id;
                    $pivot->feature_id = $feature_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($feature_ids, $role_feature_ids);
                $difference_array_delete = array_diff($role_feature_ids, $feature_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $feature_id){
                    $pivot = new RoleFeaturePivot;
                    $pivot->role_id = $id;
                    $pivot->feature_id = $feature_id;
                    $pivot->save();
                }
                $role = RoleFeaturePivot::where('role_id', $id)->get();
                foreach($difference_array_delete as $feature_id){
                    $feature_role = $role->where('feature_id', $feature_id)->first();
                    $feature_role->delete();
                }
            }
            return ["success" => true, "message" => "Role Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteRole($id)
    {
        $role = Role::find($id);
        if($role === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $role->delete();
            $role_feature = RoleFeaturePivot::where('role_id', $id)->get();
            foreach($role_feature as $feature){
                $feature->delete();
            }
            return ["success" => true, "message" => "Role Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}