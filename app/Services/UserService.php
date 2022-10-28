<?php

namespace App\Services;
use App\User;
use Exception;
use App\Services\FileService;
use App\Services\LoginService;
use App\Mail\ChangePasswordMail;
use App\Services\CompanyService;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->agent_role_id = 1;
        $this->requester_role_id = 2;
        $this->guest_role_id = 9;
    }

    public function getUserListRoles($role, $name = null){
        $users = User::with(['roles:id,name', 'company:id,name,top_parent_id', 'company.topParent'])->select('id', 'name', 'company_id', 'role', 'position')->where('role', $role);
        if($name) $users = $users->where('name', 'like', "%".$name."%");
        $users = $users->limit(50)->get();
        foreach($users as $user){
            if($user->company->id !== 0){
                $user->company->full_name = $user->company->topParent ? $user->company->topParent->name.' - '.$user->company->name : $user->company->name;
                $user->company->makeHidden('topParent');
            }
        }
        return $users;
    }

    public function getFilterUsers($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $name = $request->get('name', null);
        $type = $request->get('type', null);
        $company_id = $request->get('company_id', null);
        $users = User::with(['company:id,name,top_parent_id', 'profileImage', 'company.topParent', 'attendanceForms:id,name'])->select('id', 'name', 'company_id', 'position');
        if($type) $users = $users->where('role', $type);
        if($name) $users = $users->where('name', 'like', "%".$name."%");
        if($company_id) $users = $users->where('company_id', $company_id);
        $users = $users->limit(50)->get();
        foreach($users as $user){
            if($user->company->id !== 0){
                $user->company->full_name = $user->company->topParent ? $user->company->topParent->name.' / '.$user->company->name : $user->company->name;
                $user->company->makeHidden('topParent');
            }
        }
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $users, "status" => 200];
    }

    public function getUserDetail($account_id, $role_id){
        try{
            $user = User::with(['roles:id,name', 'company', 'attendanceForms:id,name', 'profileImage'])->find($account_id);
            if($user === null) return ["success" => false, "message" => "Id Akun Tidak Ditemukan", "status" => 400];
            if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 403];
            else {
                $user->company->makeHidden('phone_number','address','role','is_enabled','parent_id','singkatan','tanggal_pkp','penanggung_jawab','npwp','fax','email','website','deleted_at');
                $user->makeHidden('deleted_at');
                $list_feature = [];
                foreach($user->roles as $role)
                {
                    foreach($role->features as $feature) $list_feature[] = $feature->id;
                    $user->roles->makeHidden('features');
                }
                $user->features = array_values(array_unique($list_feature, SORT_NUMERIC));
                
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

    public function getAgentDetail($account_id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getUserDetail($account_id, $this->agent_role_id);
    }

    public function getRequesterDetail($account_id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getUserDetail($account_id, $this->requester_role_id);
    }

    public function getGuestDetail($account_id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getUserDetail($account_id, $this->guest_role_id);
    }

    public function getFullUserList($role_id = 0, $name = null)
    {
        $users = User::select('id','name', 'company_id', 'role', 'position')->with(['company:id,parent_id,name,top_parent_id', 'company.topParent']);
        if($role_id !== 0) $users = $users->where('users.role', $role_id);
        if($name) $users = $users->where('name', 'like', "%".$name."%");
        $users = $users->limit(50)->get();
        foreach($users as $user){
            $user->company_name = $user->company->topParent ? $user->company->topParent->name. ' - ' . $user->company->name : $user->company->name;
            $user->makeHidden(['role', 'company', 'company_id']);
        }
        if(!count($users)) return ["success" => true, "message" => "User masih kosong", "data" => $users, "status" => 200];
        else return ["success" => true, "message" => "Users Berhasil Diambil", "data" => $users, "status" => 200 ];
    }

    public function getUserList($request, $role_id){
        $company_id = auth()->user()->company_id;
        $users = User::with(['company:id,parent_id,name,top_parent_id', 'company.topParent', 'profileImage'])
        ->select('id','name', 'nip', 'email','role','company_id', 'position','phone_number','created_time','is_enabled')
        ->where('users.role', $role_id);
        if($company_id !== 1){
            $company_service = new CompanyService;
            $company_list = $company_service->checkNoSubCompanyList($company_id);
            $users = $users->whereIn('users.company_id', $company_list);
        }
        
        $name = $request->get('name', null);
        $company_id = $request->get('company_id', null);
        $is_enabled = $request->get('is_enabled', null);
        $rows = $request->get('rows', 10);

        if($company_id) $users = $users->where('company_id', $company_id);
        if($is_enabled !== null) $users = $users->where('is_enabled', $is_enabled);
        if($name) $users = $users->where('name', 'like', "%".$name."%");

        if($rows > 100) $rows = 100;
        if($rows < 1) $rows = 10;

        $users = $users->paginate($rows);
        if(!count($users)) return ["success" => true, "message" => "User masih kosong", "data" => $users, "status" => 200];
        foreach($users as $user){
            $user->company_name = $user->company->topParent ? $user->company->topParent->name. ' - ' . $user->company->name : $user->company->name;
            $user->makeHidden(['company']);
        }
        return ["success" => true, "message" => "Users Berhasil Diambil", "data" => $users, "status" => 200 ];
    }

    public function getAgentList($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getUserList($request, $this->agent_role_id);
    }

    public function getRequesterList($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getUserList($request, $this->requester_role_id);
    }

    public function getGuestList($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getUserList($request, $this->guest_role_id);
    }

    public function addUserMember($request, $role_id){
        $email = $request->get('email');
        $password = $request->get('password');
        $check_email_user = User::where('email', $email)->first();
        if($check_email_user) return ["success" => false, "message" => "Email Telah Digunakan", "status" => 400];
        if($password !== $request->get('confirm_password')) return ["success" => false, "message" => "Password Tidak Sama", "status" => 400];
        try{
            $user = new User;
            $user->name = $request->get('fullname');
            $user->nip = $request->get('nip');
            $user->company_id = $request->get('company_id');
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->role = $role_id;
            $user->phone_number = $request->get('phone_number');
            $user->position = $request->get('position');
            $user->is_enabled = true;
            $user->created_time = date("Y-m-d H:i:s");
            $user->save();
            $data_request = [
                "id" => $user->id,
                "role_ids" => $request->get('role_ids', [])
            ];

            $set_role = $this->updateRoleUser($data_request, $role_id);
            $attendance_form_ids = $request->get('attendance_form_ids', []);
            $user->attendanceForms()->attach($attendance_form_ids);
            if($request->hasFile('profile_image')) {
                $fileService = new FileService;
                $file = $request->file('profile_image');
                $table = 'App\User';
                $description = 'profile_image';
                $folder_detail = 'Users';
                $add_file_response = $fileService->addFile($user->id, $file, $table, $description, $folder_detail);
            }

            $loginService = new LoginService;
            $token = $loginService->generate_token($email, $password);
            if(!isset($token['error'])){
                $email_data = [
                    'url' => env('APP_URL_WEB'),
                    'username' => $user->name,
                    'token' => $token['access_token'],
                    'subject' => 'Ubah Password Akun MIG'
                ];
                Mail::to($email)->send(new ChangePasswordMail($email_data));
            } 
            
            if($role_id === 1) return ["success" => true, "message" => "Akun Agent berhasil ditambah", "id" => $user->id, "status" => 200];
            else if($role_id === 2) return ["success" => true, "message" => "Akun Requester berhasil ditambah", "id" => $user->id, "status" => 200];
            else return ["success" => true, "message" => "Akun Guest berhasil ditambah", "id" => $user->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAgentMember($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->addUserMember($data, $this->agent_role_id);
    }

    public function addRequesterMember($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->addUserMember($data, $this->requester_role_id);
    }

    public function addGuestMember($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->addUserMember($data, $this->guest_role_id);
    }

    public function updateUserDetail($request, $role_id){
        $id = $request->get('id');
        $user = User::with('profileImage')->find($id);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 403];
        try{
            $user->name = $request->get('fullname');
            $user->nip = $request->get('nip');
            $user->phone_number = $request->get('phone_number');
            $user->position = $request->get('position');
            $user->save();

            if($request->hasFile('profile_image')) {
                $fileService = new FileService;
                $file = $request->file('profile_image');
                $table = 'App\User';
                $description = 'profile_image';
                $folder_detail = 'Users';
                if($user->profileImage->id){
                    $delete_file_response = $fileService->deleteForceFile($user->profileImage->id);
                }
                $add_file_response = $fileService->addFile($user->id, $file, $table, $description, $folder_detail);
            }
            
            $data_request = [
                "id" => $id,
                "role_ids" => $request->get('role_ids', [])
            ];
            $save_role = $this->updateRoleUser($data_request, $role_id);
            $attendance_form_ids = $request->get('attendance_form_ids', []);
            $user->attendanceForms()->sync($attendance_form_ids);
            if(!$save_role["success"]) return $save_role;
            if($role_id === 1) return ["success" => true, "message" => "Akun Agent berhasil diperbarui", "status" => 200];
            else if($role_id === 2)  return ["success" => true, "message" => "Akun Requester berhasil diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Akun Guest berhasil diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAgentDetail($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->updateUserDetail($data, $this->agent_role_id);
    }

    public function updateRequesterDetail($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->updateUserDetail($data, $this->requester_role_id);
    }

    public function updateGuestDetail($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->updateUserDetail($data, $this->guest_role_id);
    }

    public function changeUserPassword($data, $role_id){
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 403];
        try{
            $user->password = Hash::make($data['password']);
            $user->save();
            if($role_id === 1) return ["success" => true, "message" => "Password Akun Agent berhasil diperbarui", "status" => 200];
            else if($role_id === 2) return ["success" => true, "message" => "Password Akun Requester berhasil diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Password Akun Guest berhasil diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function changeAgentPassword($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->changeUserPassword($data, $this->agent_role_id);
    }

    public function changeRequesterPassword($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->changeUserPassword($data, $this->requester_role_id);
    }

    public function changeGuestPassword($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->changeUserPassword($data, $this->guest_role_id);
    }

    public function userActivation($data, $role_id){
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 403];
        try{
            $user->is_enabled = $data['is_enabled'];
            $user->save();
            if($role_id === 1) return ["success" => true, "message" => "Status Aktivasi Agent Telah diperbarui", "status" => 200];
            else if($role_id === 2) return ["success" => true, "message" => "Status Aktivasi Requester Telah diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Status Aktivasi Guest Telah diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function agentActivation($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->userActivation($data, $this->agent_role_id);
    }

    public function requesterActivation($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->userActivation($data, $this->requester_role_id);
    }

    public function guestActivation($data, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->userActivation($data, $this->guest_role_id);
    }

    public function updateRoleUser($data, $role_id){
        $id = $data['id'];
        $role_ids = $data['role_ids'];
        $user = User::find($data['id']);
        if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
        if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 403];
        try{
            $user->roles()->sync($role_ids);
            return ["success" => true, "message" => "Berhasil Merubah Fitur Akun", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // public function updateFeatureAgent($data, $route_name){
    //     $access = $this->globalService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     return $this->updateRoleUser($data, $this->agent_role_id);
    // }

    // public function updateFeatureRequester($data, $route_name){
    //     $access = $this->globalService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     return $this->updateRoleUser($data, $this->requester_role_id);
    // }

    public function deleteUser($id, $role_id){
        try{
            if($id == auth()->user()->id) return ["success" => false, "message" => "Tidak Dapat Menghapus Akun Anda", "status" => 403];
            if($id == 1) return ["success" => false, "message" => "Tidak Dapat Menghapus Akun Admin", "status" => 403];
            $user = User::find($id);
            if($user === null) return ["success" => false, "message" => "Id Pengguna Tidak Ditemukan", "status" => 400];
            if($user->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Akun Ini", "status" => 403];
            $user->delete();
            $user->roles()->detach();
            return ["success" => true, "message" => "User Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAgent($id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->deleteUser($id, $this->agent_role_id);
    }

    public function deleteRequester($id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->deleteUser($id, $this->requester_role_id);
    }

    public function deleteGuest($id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->deleteUser($id, $this->guest_role_id);
    }
        
}