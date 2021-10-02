<?php

namespace App\Services;
use App\Services\GeneralService;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\UserRolePivot;
use Exception;

class UserService
{
    public function __construct()
    {
        $this->agent_role_id = 1;
        $this->requester_role_id = 2;
    }

    public function getUserDetail($account_id, $role_id){
        try{
            $user = User::find($account_id);
            if($user === null) return ["success" => false, "message" => "Id Akun Tidak Ditemukan", "status" => 400];
            if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 401];
            else {
                $user->company;
                $user->company->makeHidden('phone_number','address','role','is_enabled','parent_id','singkatan','tanggal_pkp','penanggung_jawab','npwp','fax','email','website','deleted_at');
                $user->makeHidden('deleted_at');
                $user->feature_roles = UserRolePivot::where('user_id', $user->user_id)->pluck('role_id')->toArray();
                return [
                    "success" => true,
                    "data" => $user,
                    "status" => 200
                ];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAgentDetail($account_id){
        return $this->getUserDetail($account_id, $this->agent_role_id);
    }

    public function getRequesterDetail($account_id){
        return $this->getUserDetail($account_id, $this->requester_role_id);
    }

    public function getUserList($role_id, $company_id, $full_list = false){
        // select('user_id AS id','fullname AS name')
        if($full_list){
            $users = User::select('user_id','fullname', 'users.company_id','companies.company_name')->leftJoin('companies', function($join) {
            $join->on('users.company_id', '=', 'companies.company_id');
            })->where('users.role', $role_id)->get();
            if(!count($users)) return ["success" => true, "message" => "User masih kosong", "data" => $users, "status" => 200];
            else return ["success" => true, "message" => "Users Berhasil Diambil", "data" => $users, "status" => 200 ];
        }
        $company_service = new CompanyService;
        $company_list = $company_service->checkCompanyList($company_id);
        $users = User::select('user_id','fullname', 'email','role','company_id','profile_image','phone_number','created_time','is_enabled')->where('role', $role_id)->whereIn('company_id', $company_list)->get();
        if(!count($users)) return ["success" => true, "message" => "User masih kosong", "data" => $users, "status" => 200];
        else return ["success" => true, "message" => "Users Berhasil Diambil", "data" => $users, "status" => 200 ];
    }

    public function getAgentList(){
        return $this->getUserList($this->agent_role_id, auth()->user()->company_id);
    }

    public function getRequesterList(){
        return $this->getUserList($this->requester_role_id, auth()->user()->company_id);
    }

    public function addUserMember($data, $role_id){
        $check_email_user = User::where('email', $data['email'])->first();
        if($check_email_user) return ["success" => false, "message" => "Email Telah Digunakan", "status" => 400];
        $generalService = new GeneralService;
        try{
            $user = new User;
            $user->fullname = $data['fullname'];
            $user->company_id = $data['company_id'];
            $user->email = $data['email'];
            $user->password = Hash::make("123456789");
            $user->role = $role_id;
            $user->phone_number = $data['phone_number'];
            $user->profile_image = $data['profile_image'];
            $user->is_enabled = false;
            $user->created_time = $generalService->getTimeNow();
            $user->save();
            if($role_id === 1) return ["success" => true, "message" => "Akun Agent berhasil ditambah", "status" => 200];
            else return ["success" => true, "message" => "Akun Requester berhasil ditambah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAgentMember($data){
        return $this->addUserMember($data, $this->agent_role_id);
    }

    public function addRequesterMember($data){
        return $this->addUserMember($data, $this->requester_role_id);
    }

    public function updateUserDetail($data, $role_id){
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 401];
        try{
            $user->fullname = $data['fullname'];
            $user->phone_number = $data['phone_number'];
            $user->save();
            if($role_id === 1) return ["success" => true, "message" => "Akun Agent berhasil diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Akun Requester berhasil diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAgentDetail($data){
        return $this->updateUserDetail($data, $this->agent_role_id);
    }

    public function updateRequesterDetail($data){
        return $this->updateUserDetail($data, $this->requester_role_id);
    }

    public function changeUserPassword($data, $role_id){
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 401];
        try{
            $user->password = Hash::make($data['password']);
            $user->save();
            if($role_id === 1) return ["success" => true, "message" => "Password Akun Agent berhasil diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Password Akun Requester berhasil diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeAgentPassword($data){
        return $this->changeUserPassword($data, $this->agent_role_id);
    }

    public function changeRequesterPassword($data){
        return $this->changeUserPassword($data, $this->requester_role_id);
    }

    public function userActivation($data, $role_id){
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 401];
        try{
            $user->is_enabled = $data['is_enabled'];
            $user->save();
            if($role_id === 1) return ["success" => true, "message" => "Status Aktivasi Agent Telah diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Status Aktivasi Requester Telah diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function agentActivation($data){
        return $this->userActivation($data, $this->agent_role_id);
    }

    public function requesterActivation($data){
        return $this->userActivation($data, $this->requester_role_id);
    }

    public function updateRoleUser($data, $role_id){
        $id = $data['id'];
        $role_ids = $data['role_ids'];
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 401];
        try{
            $user_role_ids = UserRolePivot::where('user_id', $id)->pluck('role_id')->toArray();
            if(!count($user_role_ids)) {
                foreach($role_ids as $role_id){
                    $pivot = new UserRolePivot;
                    $pivot->user_id = $id;
                    $pivot->role_id = $role_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($role_ids, $user_role_ids);
                $difference_array_delete = array_diff($user_role_ids, $role_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $role_id){
                    $pivot = new UserRolePivot;
                    $pivot->user_id = $id;
                    $pivot->role_id = $role_id;
                    $pivot->save();
                }
                $user = UserRolePivot::where('user_id', $id)->get();
                foreach($difference_array_delete as $role_id){
                    $role_user = $user->where('role_id', $role_id)->first();
                    $role_user->delete();
                }
            }
            return ["success" => true, "message" => "Berhasil Merubah Fitur Akun", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateFeatureAgent($data){
        return $this->updateRoleUser($data, $this->agent_role_id);
    }

    public function updateFeatureRequester($data){
        return $this->updateRoleUser($data, $this->requester_role_id);
    }
        
}