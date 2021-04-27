<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\ServiceCategory;
use App\ServiceItem;
use App\PivotServiceItem;
use App\DimTermsOfPayment;
use App\AccessFeature;
use Exception;

class ServiceController extends Controller
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

    public function checkRoute($name, $auth)
    {
        $protocol = $name;
        $access_feature = AccessFeature::where('name',$protocol)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin",
                    "server_code" => 400,
                    "status_detail" => "Fitur Masih Belum Terdaftar, Silahkan Hubungi Admin"
                ]
            ]];
        }
        $body = [
            'path_url' => $access_feature->feature_key
        ];
        $headers = [
            'Authorization' => $auth,
            'content-type' => 'application/json'
        ];
        try{
            $response = $this->client->request('POST', '/auth/v1/validate-feature', [
                    'headers'  => $headers,
                    'json' => $body
            ]);
            return ["success" => true];
        }catch(ClientException $err){
            $error_response = $err->getResponse();
            $detail = json_decode($error_response->getBody());
            return ["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]];
        }
    }

    public function getServiceCategories(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_CATEGORIES_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $services = ServiceCategory::orderBy('nama_kategori','asc')->get();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $services]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "SERVICE_CATEGORIES_GET";
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
            $services = ServiceCategory::orderBy('nama_kategori','asc')->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $services]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addServiceCategory(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_CATEGORY_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $service = new ServiceCategory;
        // $service->nama_kategori = $request->get('nama_kategori');
        // $service->deskripsi = $request->get('deskripsi');
        // try{
        //     $service->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "SERVICE_CATEGORY_ADD";
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
        $service = new ServiceCategory;
        $service->nama_kategori = $request->get('nama_kategori');
        $service->deskripsi = $request->get('deskripsi');
        try{
            $service->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateServiceCategory(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_CATEGORY_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);  
        // $id = $request->get('id', null);
        // $service = ServiceCategory::find($id);
        // if($service === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Id Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Id Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // $service->nama_kategori = $request->get('nama_kategori');
        // $service->deskripsi = $request->get('deskripsi');
        // try{
        //     $service->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        
        // $protocol = "SERVICE_CATEGORY_UPDATE";
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
        $service = ServiceCategory::find($id);
        if($service === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Id Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Id Tidak Ditemukan"
                ]
            ]], 400);
        $service->nama_kategori = $request->get('nama_kategori');
        $service->deskripsi = $request->get('deskripsi');
        try{
            $service->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteServiceCategory(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_CATEGORY_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $service = ServiceCategory::find($id);
        // if($service === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Data Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Data Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // try{
        //     $service->delete();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "SERVICE_CATEGORY_DELETE";
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
        $service = ServiceCategory::find($id);
        if($service === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Data Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Data Tidak Ditemukan"
                ]
            ]], 400);
        try{
            $service->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getServiceItems(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_ITEMS_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $services = ServiceItem::orderBy('nama_service_item','asc')->get();
        //     $category_services = ServiceCategory::select('id','nama_kategori')->orderBy('nama_kategori','asc')->get();
        //     foreach($services as $service){
        //         $category_service = $category_services->where('id', $service->id_service_kategori)->first();
        //         if($category_service === null) {
        //             $service->nama_kategori = "Id Service Kategori Tidak ditemukan";
        //         } else {
        //             $service->nama_kategori = $category_service->nama_kategori;
        //         }
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $services]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }

        // $protocol = "SERVICE_ITEMS_GET";
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
            $services = ServiceItem::orderBy('nama_service_item','asc')->get();
            $category_services = ServiceCategory::select('id','nama_kategori')->orderBy('nama_kategori','asc')->get();
            foreach($services as $service){
                $category_service = $category_services->where('id', $service->id_service_kategori)->first();
                if($category_service === null) {
                    $service->nama_kategori = "Id Service Kategori Tidak ditemukan";
                } else {
                    $service->nama_kategori = $category_service->nama_kategori;
                }
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $services]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getServiceItem(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_ITEM_GET", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $id = $request->get('id', null);
        //     $service = ServiceItem::find($id);
        //     if($service === null) return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => "Id Tidak Ditemukan",
        //                 "server_code" => 400,
        //                 "status_detail" => "Id Tidak Ditemukan"
        //             ]
        //         ]], 400);
        //     $child_ids = PivotServiceItem::where('parent_id', $id)->pluck('child_id');
            
        //     if($child_ids->isEmpty()) {
        //         return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => 
        //         (object)[
        //             "service" => $service, 
        //             "childs" =>[]
        //             ]
        //         ]);
        //     } else {
        //         $service_items = ServiceItem::get();
        //         $childs = [];
        //         foreach($child_ids as $child_id){
        //             $temp = (object)[
        //                 "id" => $child_id,
        //                 "nama_service_item" => $service_items->where('id', $child_id)->first()->nama_service_item
        //             ];
        //             $childs[] = $temp;
        //         }
        //         return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["service" => $service, "childs" => $childs]]);
        //     } 
            
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // $protocol = "SERVICE_ITEM_GET";
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
            $service = ServiceItem::find($id);
            if($service === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $child_ids = PivotServiceItem::where('parent_id', $id)->pluck('child_id');
            
            if($child_ids->isEmpty()) {
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => 
                (object)[
                    "service" => $service, 
                    "childs" =>[]
                    ]
                ]);
            } else {
                $service_items = ServiceItem::get();
                $childs = [];
                foreach($child_ids as $child_id){
                    $temp = (object)[
                        "id" => $child_id,
                        "nama_service_item" => $service_items->where('id', $child_id)->first()->nama_service_item
                    ];
                    $childs[] = $temp;
                }
                return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["service" => $service, "childs" => $childs]]);
            } 
            
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addServiceItem(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_ITEM_ADD", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $service = new ServiceItem;
        // $service->id_service_kategori = $request->get('id_service_kategori');
        // $service->nama_service_item = $request->get('nama_service_item');
        // $service->deskripsi_singkat = $request->get('deskripsi_singkat');
        // $service->deskripsi_lengkap = $request->get('deskripsi_lengkap');
        // $service->is_publish = false;
        // try{
        //     $service->save();
        //     $child_ids = $request->get('child_ids');
        //     foreach($child_ids as $child_id){
        //         $model = new PivotServiceItem;
        //         $model->parent_id = $service->id;
        //         $model->child_id = $child_id;
        //         $model->save();
        //     }
            
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        
        // $protocol = "SERVICE_ITEM_ADD";
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
        $service = new ServiceItem;
        $service->id_service_kategori = $request->get('id_service_kategori');
        $service->nama_service_item = $request->get('nama_service_item');
        $service->deskripsi_singkat = $request->get('deskripsi_singkat');
        $service->deskripsi_lengkap = $request->get('deskripsi_lengkap');
        $service->is_publish = false;
        try{
            $service->save();
            $child_ids = $request->get('child_ids');
            foreach($child_ids as $child_id){
                $model = new PivotServiceItem;
                $model->parent_id = $service->id;
                $model->child_id = $child_id;
                $model->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateServiceItem(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_ITEM_UPDATE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $service = ServiceItem::find($id);
        // if($service === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Id Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Id Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // $service->id_service_kategori = $request->get('id_service_kategori');
        // $service->nama_service_item = $request->get('nama_service_item');
        // $service->deskripsi_singkat = $request->get('deskripsi_singkat');
        // $service->deskripsi_lengkap = $request->get('deskripsi_lengkap');
        // $new_child_ids = $request->get('new_child_ids',[]);
        // try{
        //     $service->save();
        //     $child_ids = PivotServiceItem::where('parent_id', $id)->pluck('child_id')->toArray();
        //     $difference_array_new = array_diff($new_child_ids, $child_ids);
        //     $difference_array_delete = array_diff($child_ids, $new_child_ids);
        //     $difference_array_new = array_unique($difference_array_new);
        //     $difference_array_delete = array_unique($difference_array_delete);
        //     foreach($difference_array_new as $new_child_id){
        //         $pivot = new PivotServiceItem;
        //         $pivot->parent_id = $id;
        //         $pivot->child_id = $new_child_id;
        //         $pivot->save();
        //     }
        //     $group = PivotServiceItem::where('parent_id', $id)->get();
        //     foreach($difference_array_delete as $new_child_id){
        //         $temp_child = $group->where('child_id', $new_child_id)->first();
        //         $temp_child->delete();
        //     }
            
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // $protocol = "SERVICE_ITEM_UPDATE";
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
        $service = ServiceItem::find($id);
        if($service === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Id Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Id Tidak Ditemukan"
                ]
            ]], 400);
        $service->id_service_kategori = $request->get('id_service_kategori');
        $service->nama_service_item = $request->get('nama_service_item');
        $service->deskripsi_singkat = $request->get('deskripsi_singkat');
        $service->deskripsi_lengkap = $request->get('deskripsi_lengkap');
        $new_child_ids = $request->get('new_child_ids',[]);
        try{
            $service->save();
            $child_ids = PivotServiceItem::where('parent_id', $id)->pluck('child_id')->toArray();
            $difference_array_new = array_diff($new_child_ids, $child_ids);
            $difference_array_delete = array_diff($child_ids, $new_child_ids);
            $difference_array_new = array_unique($difference_array_new);
            $difference_array_delete = array_unique($difference_array_delete);
            foreach($difference_array_new as $new_child_id){
                $pivot = new PivotServiceItem;
                $pivot->parent_id = $id;
                $pivot->child_id = $new_child_id;
                $pivot->save();
            }
            $group = PivotServiceItem::where('parent_id', $id)->get();
            foreach($difference_array_delete as $new_child_id){
                $temp_child = $group->where('child_id', $new_child_id)->first();
                $temp_child->delete();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteServiceItem(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_ITEM_DELETE", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // $id = $request->get('id', null);
        // $service = ServiceItem::find($id);
        // if($service === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Data Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Data Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // try{
        //     $service->delete();
        //     $service_pivots = PivotServiceItem::where('parent_id', $id)->get();
        //     foreach($service_pivots as $service_pivot){
        //         $service_pivot->delete();
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // $protocol = "SERVICE_ITEM_DELETE";
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
        $service = ServiceItem::find($id);
        if($service === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Data Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Data Tidak Ditemukan"
                ]
            ]], 400);
        try{
            $service->delete();
            $service_pivots = PivotServiceItem::where('parent_id', $id)->get();
            foreach($service_pivots as $service_pivot){
                $service_pivot->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function publishingServiceItem(Request $request)
    {
        // $check = $this->checkRoute("SERVICE_ITEM_PUBLISH", $request->header("Authorization"));
        // if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        // try{
        //     $id = $request->get('id', null);
        //     $service = ServiceItem::find($id);
        //     if($service === null) return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => "Id Tidak Ditemukan",
        //                 "server_code" => 400,
        //                 "status_detail" => "Id Tidak Ditemukan"
        //             ]
        //         ]], 400);
        //     $service->is_publish = true;
        //     $service->save();
        //     return response()->json(["success" => true, "message" => "Service Item Telah Dipublikasikan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
        // $protocol = "SERVICE_ITEM_PUBLISH";
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
            $service = ServiceItem::find($id);
            if($service === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $service->is_publish = true;
            $service->save();
            return response()->json(["success" => true, "message" => "Service Item Telah Dipublikasikan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function depublishingServiceItem(Request $request)
    {
        $check = $this->checkRoute("SERVICE_ITEM_DEPUBLISH", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $service = ServiceItem::find($id);
            if($service === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $service->is_publish = false;
            $service->save();
            return response()->json(["success" => true, "message" => "Service Item Telah Dinonpublikasikan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "SERVICE_ITEM_DEPUBLISH";
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
            $service = ServiceItem::find($id);
            if($service === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $service->is_publish = false;
            $service->save();
            return response()->json(["success" => true, "message" => "Service Item Telah Dinonpublikasikan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addDefaultPayments(Request $request)
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
        $datas = [
            [
                "nama" => "Per Bulan",
                "keterangan" => "Pembayaran dilakukan setiap bulan"
            ],
            [
                "nama" => "Per 3 Bulan",
                "keterangan" => "Pembayaran dilakukan setiap 3 bulan"
            ],
            [
                "nama" => "Langsung",
                "keterangan" => "Pembayaran dilakukan tunai"
            ]
        ];
        try{
            // return $datas;
            foreach($datas as $data){
                // return $data['nama_kategori'];
                $payments = new DimTermsOfPayment;
                $payments->nama = $data['nama'];
                $payments->keterangan = $data['keterangan'];
                $payments->save();
            }
            
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}