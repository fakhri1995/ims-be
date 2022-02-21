<?php 

namespace App\Services;
use Exception;
use App\PurchaseQC;
use App\PurchaseOrder;
use App\PurchaseQCDetail;
use App\Services\LogService;
use App\Services\GlobalService;
use Illuminate\Support\Facades\DB;

class WarehouseService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    // Purchase Order
    public function getPurchaseOrders($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', null);
            $asset = $request->get('asset', null);
            $vendor = $request->get('vendor', null);
            $status = $request->get('status', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            $purchase_orders = PurchaseOrder::select('purchase_orders.id', 'purchase_order_number', 'purchase_order_date', 'arrived_date', 'vendors.name as vendor_name', 'status')->with(['modelInventories:id,asset_id', 'modelInventories.asset:id,name'])
            ->join('vendors', 'purchase_orders.vendor_id', '=', 'vendors.id');

            $params = "?rows=$rows";
            if($keyword) $params = "$params&keyword=$keyword";
            if($sort_by) $params = "$params&sort_by=$sort_by";
            if($sort_type) $params = "$params&sort_type=$sort_type";
            if($asset) $params = "$params&asset=$asset";
            if($vendor) $params = "$params&vendor=$vendor";
            if($status) $params = "$params&status=$status";
            
            if($asset){
                $model_ids = DB::table('model_inventories')->where('asset_id', $asset)->pluck('id');
                $purchase_order_ids = DB::table('model_inventory_purchase_order')->whereIn('model_inventory_id', $model_ids)->pluck('purchase_order_id');
                $purchase_orders = $purchase_orders->whereIn('purchase_orders.id', $purchase_order_ids);
            } 
            if($vendor) $purchase_orders = $purchase_orders->where('vendor_id', $vendor);
            if($keyword){
                if(is_numeric($keyword)){
                    $purchase_orders = $purchase_orders->where(function ($query) use ($keyword){
                        $query->where('purchase_order_number', 'like', "%".$keyword."%")->orWhere('purchase_orders.id', $keyword);
                    });
                } else $purchase_orders = $purchase_orders->where('purchase_order_number', 'like', "%".$keyword."%");
            } 
            if($status) $purchase_orders = $purchase_orders->where('status', $status);
            
            if($sort_by){
                if($sort_type === null) $sort_type = 'desc';
                if($sort_by === 'po_number') $purchase_orders = $purchase_orders->orderBy('purchase_order_number', $sort_type);
                else if($sort_by === 'po_date') $purchase_orders = $purchase_orders->orderBy('purchase_order_date', $sort_type);
                else if($sort_by === 'vendor') $purchase_orders = $purchase_orders->orderBy('vendor_name', $sort_type);
                else if($sort_by === 'status') $purchase_orders = $purchase_orders->orderBy('status', $sort_type);
                else if($sort_by === 'arrived_date') $purchase_orders = $purchase_orders->orderBy('arrived_date', $sort_type);
            }
            
            $purchase_orders = $purchase_orders->paginate($rows);
            $purchase_orders->withPath(env('APP_URL').'/getPurchaseOrders'.$params);
            if($purchase_orders->isEmpty()) return ["success" => true, "message" => "Purchase Orders Masih Kosong", "data" => $purchase_orders, "status" => 200];
            $statuses = $this->globalService->statusPurchaseOrder();
            foreach($purchase_orders as $purchase_order){
                $purchase_order->purchase_order_date_template = date("d F Y", strtotime($purchase_order->purchase_order_date));
                if($purchase_order->arrived_date !== null) $purchase_order->arrived_date_template = date("d F Y", strtotime($purchase_order->arrived_date));
                else $purchase_order->arrived_date_template = "-";
                $purchase_order->status_name = $statuses[$purchase_order->status];
                if(count($purchase_order->modelInventories)){
                    foreach($purchase_order->modelInventories as $model){
                        $model->quantity += $model->pivot->quantity;
                    }
                }
            }
            return ["success" => true, "message" => "Purchase Orders Berhasil Diambil", "data" => $purchase_orders, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $purchase_order = PurchaseOrder::with(['activityLogPurchaseOrders', 'modelInventories:id,asset_id', 'modelInventories.asset:id,name', 'vendor:id,name'])->find($id);
            if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $purchase_order->purchase_order_date_template = date("d F Y", strtotime($purchase_order->purchase_order_date));
            $price = 0;
            $quantity = 0;
            if(count($purchase_order->modelInventories)){
                foreach($purchase_order->modelInventories as $model){
                    $temp_price = $model->pivot->quantity *  $model->pivot->price;
                    $price += $temp_price;
                    $quantity += $model->pivot->quantity;
                }
            }
            if(count($purchase_order->activityLogPurchaseOrders)){
                foreach($purchase_order->activityLogPurchaseOrders as $log){
                    if(isset($log->connectable->name)) $log->description = $log->description . $log->connectable->name;
                    else $log->description = $log->description . "-";
                    $log->created_at = date("l, d F Y H:i", strtotime($log->created_at))." WIB";
                    $log->makeHidden('purchase_order_id', 'causer_id', 'connectable');
                }
            }
            $purchase_order->total_price = "Rp ".number_format($price,2,',','.');
            $purchase_order->total_quantity = $quantity;
            return ["success" => true, "message" => "Purchase Order Berhasil Diambil", "data" => $purchase_order, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $purchase_order = new PurchaseOrder;
        $purchase_order->purchase_order_date = $request->get('purchase_order_date');
        $purchase_order->vendor_id = $request->get('vendor_id');
        $purchase_order->description = $request->get('description');
        $purchase_order->status = 1;
        $purchase_order->purchase_order_number = "PO-";
        $purchase_order->created_by = auth()->user()->id;
        try{
            $purchase_order->save();
            $logService = new LogService;
            $logService->createPurchaseOrder($purchase_order->id);
            return ["success" => true, "message" => "Purchase Order Berhasil Ditambahkan", "id" => $purchase_order->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updatePurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $purchase_order = PurchaseOrder::find($id);
        if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($purchase_order->status !== 1) return ["success" => false, "message" => "Purchase Order Tidak Dapat Diubah, Status Purchase Order Bukan Pada Status Draft!", "status" => 400];
        $purchase_order->purchase_order_date = $request->get('purchase_order_date');
        $purchase_order->vendor_id = $request->get('vendor_id');
        $purchase_order->description = $request->get('description');
        $purchase_order->purchase_order_number = $request->get('purchase_order_number');
        try{
            $purchase_order->save();
            return ["success" => true, "message" => "Purchase Order Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deletePurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $purchase_order = PurchaseOrder::find($request->get('id'));
        if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($purchase_order->status !== 1) return ["success" => false, "message" => "Purchase Order Tidak Bisa Dihapus, Status Purchase Order Sudah Bukan Draft!", "status" => 400];
        
        try{
            $purchase_order->delete();
            return ["success" => true, "message" => "PurchaseOrder berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function acceptPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $purchase_order = PurchaseOrder::with('vendor:id,name')->find($request->get('id'));
        if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($purchase_order->status === 2) return ["success" => false, "message" => "Purchase Order Telah Disetujui", "status" => 400];
        if($purchase_order->status !== 1) return ["success" => false, "message" => "Purchase Order Tidak Dapat Disetujui, Status Purchase Order Bukan Pada Status Draft!", "status" => 400];
        
        try{
            $year_and_month = date('Y m', strtotime($purchase_order->purchase_order_date));
            $split = explode(' ', $year_and_month);
            $year = (int) $split[0];
            $month = (int) $split[1];
            $roman_numeral = $this->globalService->romanNumeral();
            $last_purchase_order = PurchaseOrder::where('year', $year)->orderBy('sub_id', 'desc')->first();
            if($last_purchase_order === null) $purchase_order->sub_id = 1;
            else $purchase_order->sub_id = $last_purchase_order->sub_id + 1;
            if(!$purchase_order->vendor) return ["success" => false, "message" => "Vendor Purchase Order Tidak Ditemukan", "status" => 400];
            $purchase_order->purchase_order_number = sprintf('%03d', $purchase_order->sub_id).'/'.$purchase_order->purchase_order_number.'/'.$purchase_order->vendor->name.'/'.$roman_numeral[$month].'/'.$year;
            $purchase_order->year = $year;
            $purchase_order->status = 2;
            $purchase_order->save();
            $logService = new LogService;
            $logService->acceptPurchaseOrder($purchase_order->id);
            return ["success" => true, "message" => "Purchase Order Berhasil Disetujui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function rejectPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $purchase_order = PurchaseOrder::find($request->get('id'));
        if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($purchase_order->status === 3) return ["success" => false, "message" => "Purchase Order Telah Ditolak", "status" => 400];
        if($purchase_order->status !== 1) return ["success" => false, "message" => "Purchase Order Tidak Dapat Ditolak, Status Purchase Order Bukan Pada Status Draft!", "status" => 400];
        
        try{
            $purchase_order->status = 3;
            $purchase_order->save();
            $logService = new LogService;
            $logService->rejectPurchaseOrder($purchase_order->id);
            return ["success" => true, "message" => "Purchase Order Berhasil Ditolak", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function sendPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $purchase_order = PurchaseOrder::find($request->get('id'));
        if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($purchase_order->status === 4) return ["success" => false, "message" => "Purchase Order Telah Dikirim", "status" => 400];
        if($purchase_order->status !== 2) return ["success" => false, "message" => "Purchase Order Tidak Dapat Dikirim, Status Purchase Order Bukan Pada Status Disetujui!", "status" => 400];
        
        try{
            $purchase_order->status = 4;
            $purchase_order->save();
            $logService = new LogService;
            $logService->sendPurchaseOrder($purchase_order->id, $purchase_order->vendor_id);
            return ["success" => true, "message" => "Purchase Order Berhasil Dikirim", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function receivePurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $purchase_order = PurchaseOrder::with('modelOrders')->find($request->get('id'));
        if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($purchase_order->status === 5) return ["success" => false, "message" => "Purchase Order Telah Diterima", "status" => 400];
        if($purchase_order->status !== 4) return ["success" => false, "message" => "Purchase Order Tidak Dapat Diterima, Status Purchase Order Bukan Pada Status Dikirim!", "status" => 400];
        
        try{
            $purchase_order->arrived_date = date('Y-m-d H:i:s');
            $purchase_order->status = 5;
            $purchase_order->save();
            $logService = new LogService;
            $logService->receivePurchaseOrder($purchase_order->id);
            $inventory_parts = [];
            $purchase_quality_control = new PurchaseQC;
            $purchase_quality_control->purchase_order_id = $purchase_order->id;
            $purchase_quality_control->status = 1;
            $purchase_quality_control->save();
            if(count($purchase_order->modelOrders)){
                foreach($purchase_order->modelOrders as $model){
                    for($i = 0; $i < $model->pivot->quantity; $i++){
                        $this->addPurchaseQCDetail($model, $purchase_quality_control->id, null);
                    }
                }
            }
            return ["success" => true, "message" => "Purchase Order Berhasil Diterima", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function addPurchaseInventoryParts($model, $parent_id)
    {
        $this->addPurchaseQCDetail($model, null, $parent_id);
    }

    private function addPurchaseQCDetail($model, $purchase_q_c_id, $parent_id)
    {
        $qc_detail = new PurchaseQCDetail;
        $qc_detail->purchase_q_c_id = $purchase_q_c_id;
        $qc_detail->model_id = $model->id;
        $qc_detail->parent_id = $parent_id;
        $qc_detail->status = 1;
        $qc_detail->save();
        if(count($model->modelColumns)){
            foreach($model->modelColumns as $model_column) $qc_detail->purchaseQCDetailAttributes()->attach($model_column->id, ['is_checked' => false]);
        }
        if(count($model->modelParts)){
            foreach($model->modelParts as $model_part) $this->addPurchaseInventoryParts($model_part, $qc_detail->id);
        }
    }
    
    // Detail Purchase Order
    public function getDetailPurchaseOrders($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $sort_by = $request->get('sort_by');
            $sort_type = $request->get('sort_type', 'desc');
            
            $purchase_order = PurchaseOrder::find($id);
            if($purchase_order === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            
            $purchase_orders = DB::table('model_inventory_purchase_order')->select(DB::raw('model_inventories.id, assets.name as asset_name, model_inventories.name as model_name, model_inventory_purchase_order.quantity, model_inventory_purchase_order.price, model_inventory_purchase_order.warranty_period, model_inventory_purchase_order.warranty_descripition, model_inventory_purchase_order.quantity * model_inventory_purchase_order.price as total_price'))->where('purchase_order_id', $id)
            ->join('model_inventories', 'model_inventory_purchase_order.model_inventory_id', '=', 'model_inventories.id')
            ->join('assets', 'model_inventories.asset_id', '=', 'assets.id');

            if($sort_by){
                if($sort_by === 'asset') $purchase_orders = $purchase_orders->orderBy('asset_name', $sort_type);
                else if($sort_by === 'model') $purchase_orders = $purchase_orders->orderBy('model_name', $sort_type);
                else if($sort_by === 'quantity') $purchase_orders = $purchase_orders->orderBy('quantity', $sort_type);
                else if($sort_by === 'price') $purchase_orders = $purchase_orders->orderBy('price', $sort_type);
                else if($sort_by === 'total_price') $purchase_orders = $purchase_orders->orderBy('total_price', $sort_type);
                else if($sort_by === 'warranty_period') $purchase_orders = $purchase_orders->orderBy('warranty_period', $sort_type);
            }
            $purchase_orders = $purchase_orders->get();

            if($purchase_orders->isEmpty()) return ["success" => true, "message" => "Detail Purchase Orders Masih Kosong", "data" => $purchase_orders, "status" => 200];
            return ["success" => true, "message" => "Detail Purchase Orders Berhasil Diambil", "data" => $purchase_orders, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function addDetailPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model_id = $request->get('model_id');
        if($model_id === null) return ["success" => false, "message" => "Model Id Belum Diisi!", "status" => 400];
        $purchase_order_id = $request->get('purchase_order_id');
        $purchase_order = PurchaseOrder::with('modelInventories')->find($purchase_order_id);
        if($purchase_order === null) return ["success" => false, "message" => "Id Purchase Order Tidak Ditemukan", "status" => 400];
        if($purchase_order->status < 3) return ["success" => false, "message" => "Purchase Order Tidak Bisa Dihapus, Status Purchase Tidak Tepat!", "status" => 400];
        $search = $purchase_order->modelInventories->search(function ($item) use ($model_id) {
            return $item->id === $model_id;
        });

        if($search !== false) return ["success" => false, "message" => "Model Sudah Dimasukkan Pada Detail Purchase Order", "status" => 400];
        
        $price = $request->get('price');
        if($price === null) return ["success" => false, "message" => "Harga Belum Diisi!", "status" => 400];
        $quantity = $request->get('quantity');
        if($quantity === null) return ["success" => false, "message" => "Jumlah Belum Diisi!", "status" => 400];
        $warranty_period = $request->get('warranty_period');
        if($warranty_period === null) return ["success" => false, "message" => "Garansi Belum Diisi!", "status" => 400];
        $warranty_descripition = $request->get('warranty_descripition');
        try{
            $purchase_order->modelInventories()->attach($model_id, ['price' => $price, 'quantity' => $quantity, 'warranty_period' => $warranty_period, 'warranty_descripition' => $warranty_descripition]);
            return ["success" => true, "message" => "Detail Purchase Order Berhasil Ditambahkan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateDetailPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model_id = $request->get('model_id');
        if($model_id === null) return ["success" => false, "message" => "Model Id Belum Diisi!", "status" => 400];
        $purchase_order_id = $request->get('purchase_order_id');
        $purchase_order = PurchaseOrder::with('modelInventories')->find($purchase_order_id);
        if($purchase_order === null) return ["success" => false, "message" => "Id Purchase Order Tidak Ditemukan", "status" => 400];
        if($purchase_order->status < 3) return ["success" => false, "message" => "Purchase Order Tidak Bisa Dihapus, Status Purchase Tidak Tepat!", "status" => 400];
        $search = $purchase_order->modelInventories->search(function ($item) use ($model_id) {
            return $item->id === $model_id;
        });

        if($search === false) return ["success" => false, "message" => "Model Tidak Termasuk Pada Detail Purchase Order", "status" => 400];
        
        $price = $request->get('price');
        if($price === null) return ["success" => false, "message" => "Harga Belum Diisi!", "status" => 400];
        $quantity = $request->get('quantity');
        if($quantity === null) return ["success" => false, "message" => "Jumlah Belum Diisi!", "status" => 400];
        $warranty_period = $request->get('warranty_period');
        if($warranty_period === null) return ["success" => false, "message" => "Garansi Belum Diisi!", "status" => 400];
        $warranty_descripition = $request->get('warranty_descripition');
        try{
            $purchase_order->modelInventories()->syncWithoutDetaching([$model_id => ['price' => $price, 'quantity' => $quantity, 'warranty_period' => $warranty_period, 'warranty_descripition' => $warranty_descripition]]);
            return ["success" => true, "message" => "Detail Purchase Order Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteDetailPurchaseOrder($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model_id = $request->get('model_id');
        if($model_id === null) return ["success" => false, "message" => "Model Id Belum Diisi!", "status" => 400];
        $purchase_order_id = $request->get('purchase_order_id');
        $purchase_order = PurchaseOrder::with('modelInventories')->find($purchase_order_id);
        if($purchase_order === null) return ["success" => false, "message" => "Id Purchase Order Tidak Ditemukan", "status" => 400];
        if($purchase_order->status < 3) return ["success" => false, "message" => "Purchase Order Tidak Bisa Dihapus, Status Purchase Tidak Tepat!", "status" => 400];
        
        try{
            $purchase_order->modelInventories()->detach($model_id);
            return ["success" => true, "message" => "Detail Purchase Order Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    // Quality Control Purchase
    public function getQualityControlPurchases($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', null);
            $vendor = $request->get('vendor', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            $purchase_quality_controls = PurchaseQC::select('*')->join('purchase_orders', 'purchase_q_c_s.id', '=', 'purchase_orders.id');
            
            $params = "?rows=$rows";
            // if($keyword) $params = "$params&keyword=$keyword";
            // if($sort_by) $params = "$params&sort_by=$sort_by";
            // if($sort_type) $params = "$params&sort_type=$sort_type";
            // if($asset) $params = "$params&asset=$asset";
            // if($vendor) $params = "$params&vendor=$vendor";
            // if($status) $params = "$params&status=$status";
            
            // if($asset){
            //     $model_ids = DB::table('model_inventories')->where('asset_id', $asset)->pluck('id');
            //     $purchase_order_ids = DB::table('model_inventory_purchase_order')->whereIn('model_inventory_id', $model_ids)->pluck('purchase_order_id');
            //     $purchase_quality_controls = $purchase_quality_controls->whereIn('purchase_quality_controls.id', $purchase_order_ids);
            // } 
            // if($vendor) $purchase_quality_controls = $purchase_quality_controls->where('vendor_id', $vendor);
            // if($keyword){
            //     if(is_numeric($keyword)){
            //         $purchase_quality_controls = $purchase_quality_controls->where(function ($query) use ($keyword){
            //             $query->where('purchase_order_number', 'like', "%".$keyword."%")->orWhere('purchase_quality_controls.id', $keyword);
            //         });
            //     } else $purchase_quality_controls = $purchase_quality_controls->where('purchase_order_number', 'like', "%".$keyword."%");
            // } 
            // if($status) $purchase_quality_controls = $purchase_quality_controls->where('status', $status);
            
            // if($sort_by){
            //     if($sort_type === null) $sort_type = 'desc';
            //     if($sort_by === 'po_number') $purchase_quality_controls = $purchase_quality_controls->orderBy('purchase_order_number', $sort_type);
            //     else if($sort_by === 'po_date') $purchase_quality_controls = $purchase_quality_controls->orderBy('purchase_order_date', $sort_type);
            //     else if($sort_by === 'vendor') $purchase_quality_controls = $purchase_quality_controls->orderBy('vendor_name', $sort_type);
            //     else if($sort_by === 'status') $purchase_quality_controls = $purchase_quality_controls->orderBy('status', $sort_type);
            //     else if($sort_by === 'arrived_date') $purchase_quality_controls = $purchase_quality_controls->orderBy('arrived_date', $sort_type);
            // }
            
            $purchase_quality_controls = $purchase_quality_controls->paginate($rows);
            $purchase_quality_controls->withPath(env('APP_URL').'/getQualityControlPurchases'.$params);
            if($purchase_quality_controls->isEmpty()) return ["success" => true, "message" => "Purchase Orders Masih Kosong", "data" => $purchase_quality_controls, "status" => 200];
            // $statuses = $this->globalService->statusQualityControlPurchase();
            // foreach($purchase_quality_controls as $purchase_order){
            //     $purchase_order->purchase_order_date_template = date("d F Y", strtotime($purchase_order->purchase_order_date));
            //     if($purchase_order->arrived_date !== null) $purchase_order->arrived_date_template = date("d F Y", strtotime($purchase_order->arrived_date));
            //     else $purchase_order->arrived_date_template = "-";
            //     $purchase_order->status_name = $statuses[$purchase_order->status];
            //     if(count($purchase_order->modelInventories)){
            //         foreach($purchase_order->modelInventories as $model){
            //             $model->quantity += $model->pivot->quantity;
            //         }
            //     }
            // }
            return ["success" => true, "message" => "Quality Control Purchases Berhasil Diambil", "data" => $purchase_quality_controls, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getQualityControlPurchase($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $purchase_quality_control = PurchaseQC::with(['purchaseOrder:id,purchase_order_number,purchase_order_date,arrived_date,status,vendor_id', 'purchaseOrder.vendor:id,name', 'purchaseOrder.modelInventories:id,name', 'PurchaseQCDetail'])->find($id);
            if($purchase_quality_control === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $quantity = 0;
            if(count($purchase_quality_control->purchaseOrder->modelInventories)){
                foreach($purchase_quality_control->purchaseOrder->modelInventories as $model){
                    $model->quantity = $model->pivot->quantity;
                    $quantity += $model->quantity;
                }
            }
            $purchase_quality_control->purchaseOrder->quantity = $quantity;
            return ["success" => true, "message" => "Quality Control Purchase Berhasil Diambil", "data" => $purchase_quality_control, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function saveQC($request, $route_name)
    {
        $id = $request->get('id');
        $purchase_q_c_id = $request->get('purchase_q_c_id');
        $attributes = $request->get('attributes', []);
        $purchase_quality_control_detail = PurchaseQCDetail::with('purchaseQCDetailAttributes')->find($id);
        if($purchase_quality_control_detail === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $top_parent_id_qcpd = $purchase_quality_control_detail->getTopParent();
        if($purchase_q_c_id != $top_parent_id_qcpd->purchase_q_c_id) return ["success" => false, "message" => "Id Detail Bukan Milik Purchase Quality Control", "status" => 400];
        $purchase_quality_control_detail->makeHidden('parent');
        foreach($attributes as $attribute){
            $purchase_quality_control_detail->purchaseQCDetailAttributes()->syncWithoutDetaching([$attribute['id'] => ['is_checked' => $attribute['is_checked']]]);
        }
        return ["success" => true, "message" => "Purchase Detail Quality Control Attributes Berhasil Disimpan", "status" => 200];   
    }
}