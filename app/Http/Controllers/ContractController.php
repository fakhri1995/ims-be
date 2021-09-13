<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\ServiceItemKontrak;
use App\ServiceItem;
use App\ServiceCategory;
use App\DimTermsOfPayment;
use App\DimTipeKontrak;
use App\Kontrak;
use App\AccessFeature;
use Exception;

class ContractController extends Controller
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
            return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => $error_response->getStatusCode(),
                    "reason" => $error_response->getReasonPhrase(),
                    "server_code" => json_decode($error_response->getBody())->error->code,
                    "status_detail" => json_decode($error_response->getBody())->error->detail
                ]
            ]]);
        }
    }

    public function getContractTypes(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_TYPES_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $contracts = DimTipeKontrak::get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_TYPES_GET";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $contracts = DimTipeKontrak::get();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function addContractType(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_TYPE_ADD", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $contract = new DimTipeKontrak;
        $contract->nama = $request->get('nama');
        $contract->keterangan = $request->get('keterangan');
        try{
            $contract->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_TYPE_ADD";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // $contract = new DimTipeKontrak;
        // $contract->nama = $request->get('nama');
        // $contract->keterangan = $request->get('keterangan');
        // try{
        //     $contract->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function updateContractType(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_TYPE_UPDATE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $contract = DimTipeKontrak::find($id);
        if($contract === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Id Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Id Tidak Ditemukan"
                ]
            ]], 400);
        $contract->nama = $request->get('nama');
        $contract->keterangan = $request->get('keterangan');
        try{
            $contract->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        
        // $protocol = "CONTRACT_TYPE_UPDATE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // $id = $request->get('id', null);
        // $contract = DimTipeKontrak::find($id);
        // if($contract === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Id Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Id Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // $contract->nama = $request->get('nama');
        // $contract->keterangan = $request->get('keterangan');
        // try{
        //     $contract->save();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function deleteContractType(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_TYPE_DELETE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $contract = DimTipeKontrak::find($id);
        if($contract === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Data Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Data Tidak Ditemukan"
                ]
            ]], 400);
        try{
            $contract->delete();
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_TYPE_DELETE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // $id = $request->get('id', null);
        // $contract = DimTipeKontrak::find($id);
        // if($contract === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Data Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Data Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // try{
        //     $contract->delete();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function getContracts(Request $request)
    {
        $check = $this->checkRoute("CONTRACTS_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $contracts = Kontrak::get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }

        // $protocol = "CONTRACTS_GET";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $contracts = Kontrak::get();
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function getContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_GET", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
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
            }
            else {
                $companies = [];
                foreach($response['data']['companies'] as $data){
                    $temp = [
                        "id" => $data['company_id'],
                        "company_name" => $data['company_name']
                    ];
                    $companies[] = $temp;
                }
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
        try{
            $id = $request->get('id', null);
            $contract = Kontrak::find($id);
            if($contract === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
            $object_search = array_search($contract->id_client_company, array_column($companies, 'id'));
            $contract->company_name = $companies[$object_search]['company_name'];
            $service_item_kontraks = ServiceItemKontrak::where('id_kontrak', $id)->get();
            $service_items = ServiceItem::select('id','nama_service_item')->get();
            $term_of_payments = DimTermsOfPayment::select('id','nama')->get();
            foreach($service_item_kontraks as $service_item_kontrak){
                $service_item = $service_items->where('id', $service_item_kontrak->id_service_item)->first();
                $metode_pembayaran = $term_of_payments->where('id', $service_item_kontrak->id_terms_of_payment)->first();
                if($service_item !== null) $service_item_kontrak->nama = $service_item->nama_service_item;
                else $service_item_kontrak->nama = "Service Item Tidak Ditemukan";
                if($metode_pembayaran !== null) $service_item_kontrak->nama_metode_pembayaran = $metode_pembayaran->nama;
                else $service_item_kontrak->nama_metode_pembayaran = "Metode Pembayaran Tidak Ditemukan";
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["contract" => $contract, "service_item_kontraks" => $service_item_kontraks]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        
        // $protocol = "CONTRACT_GET";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
        //             'headers'  => $headers
        //         ]);
        //     $response = json_decode((string) $response->getBody(), true);
        //     if(array_key_exists('error', $response)) {
        //         return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => $response['error']['detail'],
        //                 "server_code" => $response['error']['code'],
        //                 "status_detail" => $response['error']['detail']
        //             ]
        //         ]], 400);
        //     }
        //     else {
        //         $companies = [];
        //         foreach($response['data']['companies'] as $data){
        //             $temp = [
        //                 "id" => $data['company_id'],
        //                 "company_name" => $data['company_name']
        //             ];
        //             $companies[] = $temp;
        //         }
        //     } 
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $id = $request->get('id', null);
        //     $contract = Kontrak::find($id);
        //     if($contract === null) return response()->json(["success" => false, "message" => "Data Tidak Ditemukan"], 400);
        //     $object_search = array_search($contract->id_client_company, array_column($companies, 'id'));
        //     $contract->company_name = $companies[$object_search]['company_name'];
        //     $service_item_kontraks = ServiceItemKontrak::where('id_kontrak', $id)->get();
        //     $service_items = ServiceItem::select('id','nama_service_item')->get();
        //     $term_of_payments = DimTermsOfPayment::select('id','nama')->get();
        //     foreach($service_item_kontraks as $service_item_kontrak){
        //         $service_item = $service_items->where('id', $service_item_kontrak->id_service_item)->first();
        //         $metode_pembayaran = $term_of_payments->where('id', $service_item_kontrak->id_terms_of_payment)->first();
        //         if($service_item !== null) $service_item_kontrak->nama = $service_item->nama_service_item;
        //         else $service_item_kontrak->nama = "Service Item Tidak Ditemukan";
        //         if($metode_pembayaran !== null) $service_item_kontrak->nama_metode_pembayaran = $metode_pembayaran->nama;
        //         else $service_item_kontrak->nama_metode_pembayaran = "Metode Pembayaran Tidak Ditemukan";
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["contract" => $contract, "service_item_kontraks" => $service_item_kontraks]]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function addContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_ADD", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $contract = new Kontrak;
        $contract->id_client_company = $request->get('id_client_company');
        $contract->id_tipe_kontrak = $request->get('id_tipe_kontrak');
        $contract->nomor_kontrak = $request->get('nomor_kontrak');
        $contract->deskripsi = $request->get('deskripsi', null);
        $contract->tanggal_mulai = $request->get('tanggal_mulai');
        $contract->tanggal_selesai = $request->get('tanggal_selesai');
        $contract->is_active = false;
        try{
            $contract->save();
            $service_items = $request->get('service_items');
            foreach($service_items as $service_item){
                $model = new ServiceItemKontrak;
                $model->id_kontrak = $contract->id;
                $model->id_service_item = $service_item['id_service_item'];
                $model->id_terms_of_payment = $service_item['id_terms_of_payment'];
                $model->harga = $service_item['harga'];
                $model->is_active = false;
                $model->save();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_ADD";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // $contract = new Kontrak;
        // $contract->id_client_company = $request->get('id_client_company');
        // $contract->id_tipe_kontrak = $request->get('id_tipe_kontrak');
        // $contract->nomor_kontrak = $request->get('nomor_kontrak');
        // $contract->deskripsi = $request->get('deskripsi', null);
        // $contract->tanggal_mulai = $request->get('tanggal_mulai');
        // $contract->tanggal_selesai = $request->get('tanggal_selesai');
        // $contract->is_active = false;
        // try{
        //     $contract->save();
        //     $service_items = $request->get('service_items');
        //     foreach($service_items as $service_item){
        //         $model = new ServiceItemKontrak;
        //         $model->id_kontrak = $contract->id;
        //         $model->id_service_item = $service_item['id_service_item'];
        //         $model->id_terms_of_payment = $service_item['id_terms_of_payment'];
        //         $model->harga = $service_item['harga'];
        //         $model->is_active = false;
        //         $model->save();
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function updateContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_UPDATE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $contract = Kontrak::find($id);
        if($contract === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Id Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Id Tidak Ditemukan"
                ]
            ]], 400);
        $contract->id_client_company = $request->get('id_client_company');
        $contract->id_tipe_kontrak = $request->get('id_tipe_kontrak');
        $contract->nomor_kontrak = $request->get('nomor_kontrak');
        $contract->deskripsi = $request->get('deskripsi');
        $contract->tanggal_mulai = $request->get('tanggal_mulai');
        $contract->tanggal_selesai = $request->get('tanggal_selesai');
        try{
            $contract->save();
            $service_items = $request->get('service_items');
            $new_service_items_ids = [];
            foreach($service_items as $service_item){
                array_push($new_service_items_ids, $service_item['id_service_item']);
            }
            $old_service_item_ids = ServiceItemKontrak::where('id_kontrak', $id)->pluck('id_service_item')->toArray();
            
            $same_array = array_intersect($new_service_items_ids, $old_service_item_ids);
            $difference_array_new = array_diff($new_service_items_ids, $old_service_item_ids);
            $difference_array_delete = array_diff($old_service_item_ids, $new_service_items_ids);
            
            $all_service_items = ServiceItemKontrak::where('id_kontrak', $id)->get();
            // Update
            foreach($same_array as $pivot_asset_id){
                $object_search = array_search($pivot_asset_id, array_column($service_items, 'id_service_item'));
                $new_service_item = $service_items[$object_search];
                // return $new_service_item;
                $item_service = $all_service_items->where('id_service_item', $pivot_asset_id)->first();
                $item_service->id_service_item = $new_service_item['id_service_item'];
                $item_service->harga = $new_service_item['harga'];
                $item_service->id_terms_of_payment = $new_service_item['id_terms_of_payment'];
                $item_service->save();
            }
            // Delete
            foreach($difference_array_delete as $pivot_asset_id){
                $item_service = $all_service_items->where('id_service_item', $pivot_asset_id)->first();
                $item_service->delete();
            }
            // Create
            foreach($difference_array_new as $pivot_asset_id){
                $object_search = array_search($pivot_asset_id, array_column($service_items, 'id_service_item'));
                $new_service_item = $service_items[$object_search];
                $item_service = new ServiceItemKontrak;
                $item_service->id_kontrak = $id;
                $item_service->id_service_item = $new_service_item['id_service_item'];
                $item_service->harga = $new_service_item['harga'];
                $item_service->id_terms_of_payment = $new_service_item['id_terms_of_payment'];
                $item_service->is_active = false;
                $item_service->save();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_UPDATE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // $id = $request->get('id', null);
        // $contract = Kontrak::find($id);
        // if($contract === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Id Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Id Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // $contract->id_client_company = $request->get('id_client_company');
        // $contract->id_tipe_kontrak = $request->get('id_tipe_kontrak');
        // $contract->nomor_kontrak = $request->get('nomor_kontrak');
        // $contract->deskripsi = $request->get('deskripsi');
        // $contract->tanggal_mulai = $request->get('tanggal_mulai');
        // $contract->tanggal_selesai = $request->get('tanggal_selesai');
        // try{
        //     $contract->save();
        //     $service_items = $request->get('service_items');
        //     $new_service_items_ids = [];
        //     foreach($service_items as $service_item){
        //         array_push($new_service_items_ids, $service_item['id_service_item']);
        //     }
        //     $old_service_item_ids = ServiceItemKontrak::where('id_kontrak', $id)->pluck('id_service_item')->toArray();
            
        //     $same_array = array_intersect($new_service_items_ids, $old_service_item_ids);
        //     $difference_array_new = array_diff($new_service_items_ids, $old_service_item_ids);
        //     $difference_array_delete = array_diff($old_service_item_ids, $new_service_items_ids);
            
        //     $all_service_items = ServiceItemKontrak::where('id_kontrak', $id)->get();
        //     // Update
        //     foreach($same_array as $pivot_asset_id){
        //         $object_search = array_search($pivot_asset_id, array_column($service_items, 'id_service_item'));
        //         $new_service_item = $service_items[$object_search];
        //         // return $new_service_item;
        //         $item_service = $all_service_items->where('id_service_item', $pivot_asset_id)->first();
        //         $item_service->id_service_item = $new_service_item['id_service_item'];
        //         $item_service->harga = $new_service_item['harga'];
        //         $item_service->id_terms_of_payment = $new_service_item['id_terms_of_payment'];
        //         $item_service->save();
        //     }
        //     // Delete
        //     foreach($difference_array_delete as $pivot_asset_id){
        //         $item_service = $all_service_items->where('id_service_item', $pivot_asset_id)->first();
        //         $item_service->delete();
        //     }
        //     // Create
        //     foreach($difference_array_new as $pivot_asset_id){
        //         $object_search = array_search($pivot_asset_id, array_column($service_items, 'id_service_item'));
        //         $new_service_item = $service_items[$object_search];
        //         $item_service = new ServiceItemKontrak;
        //         $item_service->id_kontrak = $id;
        //         $item_service->id_service_item = $new_service_item['id_service_item'];
        //         $item_service->harga = $new_service_item['harga'];
        //         $item_service->id_terms_of_payment = $new_service_item['id_terms_of_payment'];
        //         $item_service->is_active = false;
        //         $item_service->save();
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function deleteContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_DELETE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $id = $request->get('id', null);
        $contract = Kontrak::find($id);
        if($contract === null) return response()->json(["success" => false, "message" => (object)[
                "errorInfo" => [
                    "status" => 400,
                    "reason" => "Data Tidak Ditemukan",
                    "server_code" => 400,
                    "status_detail" => "Data Tidak Ditemukan"
                ]
            ]], 400);
        try{
            $contract->delete();
            $service_item_kontraks = ServiceItemKontrak::where('id_kontrak', $id)->get();
            foreach($service_item_kontraks as $service_item_kontrak){
                $service_item_kontrak->delete();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_DELETE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // $id = $request->get('id', null);
        // $contract = Kontrak::find($id);
        // if($contract === null) return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => 400,
        //             "reason" => "Data Tidak Ditemukan",
        //             "server_code" => 400,
        //             "status_detail" => "Data Tidak Ditemukan"
        //         ]
        //     ]], 400);
        // try{
        //     $contract->delete();
        //     $service_item_kontraks = ServiceItemKontrak::where('id_kontrak', $id)->get();
        //     foreach($service_item_kontraks as $service_item_kontrak){
        //         $service_item_kontrak->delete();
        //     }
        //     return response()->json(["success" => true, "message" => "Data Berhasil Dihapus"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function activatingContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_ACTIVE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $contract = Kontrak::find($id);
            if($contract === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $contract->is_active = true;
            $contract->save();
            return response()->json(["success" => true, "message" => "Kontrak Telah Diaktifkan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_ACTIVE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $id = $request->get('id', null);
        //     $contract = Kontrak::find($id);
        //     if($contract === null) return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => "Id Tidak Ditemukan",
        //                 "server_code" => 400,
        //                 "status_detail" => "Id Tidak Ditemukan"
        //             ]
        //         ]], 400);
        //     $contract->is_active = true;
        //     $contract->save();
        //     return response()->json(["success" => true, "message" => "Kontrak Telah Diaktifkan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function deactivatingContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_DEACTIVE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $contract = Kontrak::find($id);
            if($contract === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $contract->is_active = false;
            $contract->save();
            return response()->json(["success" => true, "message" => "Kontrak Telah Dinonaktifkan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_DEACTIVE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $id = $request->get('id', null);
        //     $contract = Kontrak::find($id);
        //     if($contract === null) return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => "Id Tidak Ditemukan",
        //                 "server_code" => 400,
        //                 "status_detail" => "Id Tidak Ditemukan"
        //             ]
        //         ]], 400);
        //     $contract->is_active = false;
        //     $contract->save();
        //     return response()->json(["success" => true, "message" => "Kontrak Telah Dinonaktifkan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function activatingServiceItemContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_SERVICE_ITEM_ACTIVE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $service_item_kontrak = ServiceItemKontrak::find($id);
            if($service_item_kontrak === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $service_item_kontrak->is_active = true;
            $service_item_kontrak->save();
            return response()->json(["success" => true, "message" => "Service Item Kontrak Telah Diaktifkan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_SERVICE_ITEM_ACTIVE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $id = $request->get('id', null);
        //     $service_item_kontrak = ServiceItemKontrak::find($id);
        //     if($service_item_kontrak === null) return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => "Id Tidak Ditemukan",
        //                 "server_code" => 400,
        //                 "status_detail" => "Id Tidak Ditemukan"
        //             ]
        //         ]], 400);
        //     $service_item_kontrak->is_active = true;
        //     $service_item_kontrak->save();
        //     return response()->json(["success" => true, "message" => "Service Item Kontrak Telah Diaktifkan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function deactivatingServiceItemContract(Request $request)
    {
        $check = $this->checkRoute("CONTRACT_SERVICE_ITEM_DEACTIVE", $request->header("Authorization"));
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        try{
            $id = $request->get('id', null);
            $service_item_kontrak = ServiceItemKontrak::find($id);
            if($service_item_kontrak === null) return response()->json(["success" => false, "message" => (object)[
                    "errorInfo" => [
                        "status" => 400,
                        "reason" => "Id Tidak Ditemukan",
                        "server_code" => 400,
                        "status_detail" => "Id Tidak Ditemukan"
                    ]
                ]], 400);
            $service_item_kontrak->is_active = false;
            $service_item_kontrak->save();
            return response()->json(["success" => true, "message" => "Service Item Kontrak Telah Dinonaktifkan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
        // $protocol = "CONTRACT_SERVICE_ITEM_DEACTIVE";
        // $headers = ['Authorization' => $request->header("Authorization")];
        // try{
        //     $response = $this->client->request('GET', '/auth/v1/get-profile', [
        //             'headers'  => $headers
        //         ]);
        // }catch(ClientException $err){
        //     $error_response = $err->getResponse();
        //     $detail = json_decode($error_response->getBody());
        //     return response()->json(["success" => false, "message" => (object)[
        //         "errorInfo" => [
        //             "status" => $error_response->getStatusCode(),
        //             "reason" => $error_response->getReasonPhrase(),
        //             "server_code" => json_decode($error_response->getBody())->error->code,
        //             "status_detail" => json_decode($error_response->getBody())->error->detail
        //         ]
        //     ]], $error_response->getStatusCode());
        // }
        // try{
        //     $id = $request->get('id', null);
        //     $service_item_kontrak = ServiceItemKontrak::find($id);
        //     if($service_item_kontrak === null) return response()->json(["success" => false, "message" => (object)[
        //             "errorInfo" => [
        //                 "status" => 400,
        //                 "reason" => "Id Tidak Ditemukan",
        //                 "server_code" => 400,
        //                 "status_detail" => "Id Tidak Ditemukan"
        //             ]
        //         ]], 400);
        //     $service_item_kontrak->is_active = false;
        //     $service_item_kontrak->save();
        //     return response()->json(["success" => true, "message" => "Service Item Kontrak Telah Dinonaktifkan"]);
        // } catch(Exception $err){
        //     return response()->json(["success" => false, "message" => $err], 400);
        // }
    }

    public function getContractInputData(Request $request)
    {
        $header = $request->header("Authorization");
        $check = $this->checkRoute("CONTRACT_ADD", $header);
        if($check['success'] === false) return response()->json($check, $check['message']->errorInfo['status']);
        $headers = ['Authorization' => $header];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-company?get_all_data=true', [
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
            }
            else {
                $companies = [];
                foreach($response['data']['companies'] as $data){
                    $temp = (object)[
                        "id" => $data['company_id'],
                        "company_name" => $data['company_name']
                    ];
                    $companies[] = $temp;
                }
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
        try{
            $service_categories = ServiceCategory::get();
            $service_items = ServiceItem::select('id','id_service_kategori','nama_service_item','deskripsi_singkat')->get();
            $term_of_payments = DimTermsOfPayment::select('id','nama')->get();
            $contract_types = DimTipeKontrak::select('id', 'nama', 'keterangan')->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["companies" => $companies, "service_categories" => $service_categories, "service_items" => $service_items, "term_of_payments" => $term_of_payments, "contract_types" => $contract_types]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}