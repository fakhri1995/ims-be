<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\ServiceItemKontrak;
use App\ServiceItem;
use App\DimTermsOfPayment;
use App\DimTipeKontrak;
use App\Kontrak;
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

    // Normal Route

    public function getContractTypes(Request $request)
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
            $contracts = DimTipeKontrak::get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addContractType(Request $request)
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
        $contract = new DimTipeKontrak;
        $contract->nama = $request->get('nama');
        $contract->keterangan = $request->get('keterangan');
        try{
            $contract->save();
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateContractType(Request $request)
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
    }

    public function deleteContractType(Request $request)
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
    }

    public function getContracts(Request $request)
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
            $contracts = Kontrak::get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function getContract(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by'),
            'is_enabled' => $request->get('is_enabled', null),
            'role' => $request->get('role')
        ];
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-company?page=1'
                .'&rows=50', [
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
            $object_search = array_search($contract->id_client_company, array_column($companies, 'id'));
            $contract->company_name = $companies[$object_search]['company_name'];
            $service_item_kontraks = ServiceItemKontrak::where('id_kontrak', $id)->get();
            $service_items = ServiceItem::select('id','nama_service_item')->get();
            $term_of_payments = DimTermsOfPayment::select('id','nama')->get();
            foreach($service_item_kontraks as $service_item_kontrak){
                $service_item = $service_items->where('id', $service_item_kontrak->id_service_item)->first();
                $metode_pembayaran = $term_of_payments->where('id', $service_item_kontrak->id_terms_of_payment)->first();
                if($service_item !== null) $service_item_kontrak->nama_service_item = $service_item->nama_service_item;
                else $service_item_kontrak->nama_service_item = "Service Item Tidak Ditemukan";
                if($metode_pembayaran !== null) $service_item_kontrak->nama_metode_pembayaran = $metode_pembayaran->nama;
                else $service_item_kontrak->nama_metode_pembayaran = "Metode Pembayaran Tidak Ditemukan";
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["contract" => $contract, "service_item_kontraks" => $service_item_kontraks]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function addContract(Request $request)
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
                $model->harga = $service_item['price'];
                $model->is_active = false;
                $model->save();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function updateContract(Request $request)
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
                $item_service->harga = $new_service_item['price'];
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
                $item_service->harga = $new_service_item['price'];
                $item_service->id_terms_of_payment = $new_service_item['id_terms_of_payment'];
                $item_service->is_active = false;
                $item_service->save();
            }
            return response()->json(["success" => true, "message" => "Data Berhasil Disimpan"]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }

    public function deleteContract(Request $request)
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
    }

    public function getContractInputData(Request $request)
    {
        $params = [
            'page' => $request->get('page'),
            'rows' => $request->get('rows'),
            'order_by' => $request->get('order_by'),
            'is_enabled' => $request->get('is_enabled', null),
            'role' => $request->get('role')
        ];
        $headers = ['Authorization' => $request->header("Authorization")];
        try{
            $response = $this->client->request('GET', '/admin/v1/get-list-company?page=1'
                .'&rows=50', [
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
            $service_items = ServiceItem::select('id','nama_service_item')->get();
            $term_of_payments = DimTermsOfPayment::select('id','nama')->get();
            $contract_types = DimTipeKontrak::select('id', 'nama')->get();
            return response()->json(["success" => true, "message" => "Data Berhasil Diambil", "data" => (object)["companies" => $companies, "service_items" => $service_items, "term_of_payments" => $term_of_payments, "contract_types" => $contract_types]]);
        } catch(Exception $err){
            return response()->json(["success" => false, "message" => $err], 400);
        }
    }
}