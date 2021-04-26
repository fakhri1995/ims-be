<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\AccessFeature;
use App\Role;
use App\RoleFeaturePivot;
use App\UserRolePivot;
use Exception;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://go.cgx.co.id/']);
    }

    public function getRoleUsers(Request $request){
        $role_id = $request->get('id');
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-account?get_all_data=true&order_by=asc', [
                    'headers'  => $headers
                ]);
            $response = json_decode((string) $response->getBody(), true);
            if(array_key_exists('error', $response)) {
                return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => $response['error']['detail'],
                        "server_code" => $response['error']['code'],
                        "status_detail" => $response['error']['detail']
                    ]
                ]], 400);
            } else {
                $role_user_ids = UserRolePivot::where('role_id', $role_id)->pluck('user_id')->toArray();
                $list_user = [];
                foreach($role_user_ids as $user_id){
                    foreach($response['data']['accounts'] as $user){
                        if($user['user_id'] === $user_id){
                            $list_user[] = $user['fullname'];
                        }
                    }
                }
              return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $list_user]);  
            } 

        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
    }

    public function getRoles(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        try{
            $roles = Role::all();
            foreach($roles as $role){
                $role->member = UserRolePivot::where('role_id', $role->id)->count();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $roles]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getRole(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        try{
            $id = $request->get('id', null);
            $role = Role::find($id);
            if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
            $role_feature_ids = RoleFeaturePivot::where('role_id', $id)->pluck('feature_id');
            $features = AccessFeature::get();
            $role_features = [];
            foreach($role_feature_ids as $role_feature_id){
                $role_feature = $features->where('feature_id', $role_feature_id)->first();
                if($role_feature === null) {
                    $role_feature['id'] = "Data Tidak Ditemukan";
                    $role_feature['feature_id'] = $role_feature_id;
                    $role_feature['name'] = "Data Tidak Ditemukan";
                    $role_feature['description'] = "Data Tidak Ditemukan";
                    $role_feature['feature_key'] = "Data Tidak Ditemukan";
                } 
                $role_features[] = $role_feature;
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => ["role_detail" => $role, "role_features" => $role_features]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addRole(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        $role = new Role;
        $role->name = $request->get('name');
        $role->description = $request->get('description');
        $feature_ids = $request->get('feature_ids',[]);
        try{
            $role->save();

            $role_id = $role->id;
            $feature_ids = array_unique($feature_ids);
            foreach($feature_ids as $feature_id){
                $pivot = new RoleFeaturePivot;
                $pivot->role_id = $role_id;
                $pivot->feature_id = $feature_id;
                $pivot->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateRole(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }

        $id = $request->get('id', null);
        $role = Role::find($id);
        if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        $role->name = $request->get('name');
        $role->description = $request->get('description');
        $feature_ids = $request->get('feature_ids',[]);
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
            //Get list account that have updated role
            $account_ids = UserRolePivot::where('role_id', $id)->pluck('user_id')->toArray();
            // return $account_ids;
            
            //Reupdating accounts feature according to new feature's role
            //Feature ids for accessing cgx's company and account feature
            $default_feature = [54, 55, 56, 57, 58, 59, 60, 61 ,62, 74, 75];
            foreach($account_ids as $account_id){
                $role_ids = UserRolePivot::where('user_id', $account_id)->pluck('role_id')->toArray();
                $feature_ids = [];
                foreach($role_ids as $role_id){
                    $role = Role::find($role_id);
                    if($role !== null){
                        $role_feature_ids = RoleFeaturePivot::where('role_id', $role->id)->pluck('feature_id');
                        foreach($role_feature_ids as $feature_id){
                            $feature_ids[] = $feature_id; 
                        }
                    }
                }
                $unique_ids = array_unique($feature_ids);
                $account_feature_ids = array_merge($default_feature, $unique_ids);
                $body = [
                    'account_id' => $account_id,
                    'feature_ids' => $account_feature_ids
                ];
                $headers = [
                    'Authorization' => $request->header("Authorization"),
                    'content-type' => 'application/json'
                ];
                try{
                    $response = $this->client->request('POST', '/admin/v1/update-feature', [
                            'headers'  => $headers,
                            'json' => $body
                        ]);
                    $response = json_decode((string) $response->getBody(), true);
                    if(array_key_exists('error', $response)) {
                        return response()->json(["success" => false, "message" => (object)[
                            "errorInfo" => [
                                "status" => 400,
                                "reason" => $response['error']['detail'],
                                "server_code" => $response['error']['code'],
                                "status_detail" => $response['error']['detail']
                            ]
                        ]], 400);
                    }   
                }catch(ClientException $err){
                    $error_response = $err->getResponse();
                    $detail = json_decode($error_response->getBody());
                    return response()->json(["success" => false, "message" => (object)[
                        "errorInfo" => [
                            "status" => $error_response->getStatusCode(),
                            "reason" => $error_response->getReasonPhrase(),
                            "server_code" => json_decode($error_response->getBody())->error->code,
                            "status_detail" => json_decode($error_response->getBody())->error->detail
                        ]
                    ]], $error_response->getStatusCode());
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteRole(Request $request)
    {
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/auth/v1/get-profile', [
                    'headers'  => $headers
                ]);
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]], $error_response->getStatusCode());
        }
        $id = $request->get('id', null);
        $role = Role::find($id);
        if($role === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"]);
        try{
            $role->delete();
            $role_feature = RoleFeaturePivot::where('role_id', $id)->get();
            foreach($role_feature as $feature){
                $feature->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }   
}