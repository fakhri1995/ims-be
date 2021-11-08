<?php

namespace App\Services;
use App\AccessFeature;
use App\Role;

class CheckRouteService
{
    public function checkRoute($route_name){
        //Super Admin Special Bypass
        $super_admin_role = Role::where('name', 'Super Admin')->first();
        $user_super_admin = auth()->user()->roles->where('id', $super_admin_role->id)->first();
        if($user_super_admin) return ["success" => true];
        //------


        $access_feature = AccessFeature::with('roles')->where('name', $route_name)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => "RUTE AKSES FITUR BELUM TERDAFTAR, SILAHKAN HUBUNGI CS MIG", "status" => 400];
        } else {
            $user_roles = auth()->user()->roles->pluck('id')->toArray();
            $feature_in_roles = $access_feature->roles->pluck('id')->toArray();
            $result = array_intersect($user_roles, $feature_in_roles);
            if(count($result)) return ["success" => true];
            else return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Fitur Ini", "status" => 401];
        }
        return $response;
    }    
}