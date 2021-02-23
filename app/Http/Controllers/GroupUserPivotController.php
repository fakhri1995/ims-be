<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\GroupUserPivot;
use Exception;

class GroupUserPivotController extends Controller
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

    public function attachPivotGU(Request $request)
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
            ]]);
        }
        $validator = Validator::make($request->all(), [
            "group_id" => "required",
            "user_ids" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => $validator->errors()
            ]], 400);
        }
        try{
            $group_id = $request->get('group_id');
            $user_ids = $request->get('user_ids');
            $group_user_ids = GroupUserPivot::where('group_id', $group_id)->pluck('user_id')->toArray();
            if(!count($group_user_ids)) {
                $user_ids = array_unique($user_ids);
                foreach($user_ids as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $group_id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
            } else {
                $difference_array_new = array_diff($user_ids, $group_user_ids);
                $difference_array_delete = array_diff($group_user_ids, $user_ids);
                $difference_array_new = array_unique($difference_array_new);
                $difference_array_delete = array_unique($difference_array_delete);
                foreach($difference_array_new as $user_id){
                    $pivot = new GroupUserPivot;
                    $pivot->group_id = $group_id;
                    $pivot->user_id = $user_id;
                    $pivot->save();
                }
                $group = GroupUserPivot::where('group_id', $group_id)->get();
                foreach($difference_array_delete as $user_id){
                    $user_group = $group->where('user_id', $user_id)->first();
                    $user_group->delete();
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err]);
        }
    }

    // public function detachPivotGU(Request $request)
    // {
    //     $headers = ['Authorization' => $request->header("Authorization")];
    //     try{
    //         $response = $this->client->request('GET', '/auth/v1/get-profile', [
    //                 'headers'  => $headers
    //             ]);
    //     }catch(ClientException $err){
    //         $error_response = $err->getResponse();
    //         $detail = json_decode($error_response->getBody());
    //         return response()->json(["success" => false, "message" => (object)[
    //             "errorInfo" => [
    //                 "status" => $error_response->getStatusCode(),
    //                 "reason" => $error_response->getReasonPhrase(),
    //                 "server_code" => json_decode($error_response->getBody())->error->code,
    //                 "status_detail" => json_decode($error_response->getBody())->error->detail
    //             ]
    //         ]]);
    //     }
    //     try{
    //         $group_id = $request->get('group_id');
    //         $user_id = $request->get('user_id');
    //         $group = GroupUserPivot::where('group_id', $group_id)->get();
    //         if($group->isEmpty()){
    //             return response()->json(["success" => false, "message" => "Group Tidak Ditemukan"]);
    //         } else {
    //             if(!$user_group = $group->where('user_id', $user_id)->first()){
    //                 return response()->json(["success" => false, "message" => "User pada Group Tidak Ditemukan"]);
    //             }
    //             $user_group->delete();
    //             return response()->json(["success" => false, "message" => "Pivot Berhasil Dihapus"]);
    //         }
    //     } catch(Exception $err){
    //         return response()->json(["success" => false, "message" => $err]);
    //     }
    // }

    
}