<?php 

namespace App\Services;
use Exception;
use App\AttendanceForm;
use Illuminate\Support\Str;

class AttendanceService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    // Attendance Form
    public function getAttendanceForms($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $rows = $request->get('rows', 10);
            $keyword = $request->get('keyword', null);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', null);

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            $attendance_forms = AttendanceForm::select('id', 'name', 'description', 'updated_at')->withCount('users');

            $params = "?rows=$rows";
            if($keyword) $params = "$params&keyword=$keyword";
            if($sort_by) $params = "$params&sort_by=$sort_by";
            if($sort_type) $params = "$params&sort_type=$sort_type";
            
            if($keyword) $attendance_forms = $attendance_forms->where('name', 'like', "%".$keyword."%");
            if($sort_by){
                if($sort_type === null) $sort_type = 'desc';
                if($sort_by === 'name') $attendance_forms = $attendance_forms->orderBy('name', $sort_type);
                else if($sort_by === 'description') $attendance_forms = $attendance_forms->orderBy('description', $sort_type);
                else if($sort_by === 'updated_at') $attendance_forms = $attendance_forms->orderBy('updated_at', $sort_type);
                else if($sort_by === 'count') $attendance_forms = $attendance_forms->orderBy('users_count', $sort_type);
            }
            
            $attendance_forms = $attendance_forms->paginate($rows);
            $attendance_forms->withPath(env('APP_URL').'/getAttendanceForms'.$params);
            if($attendance_forms->isEmpty()) return ["success" => true, "message" => "Attendance Forms Masih Kosong", "data" => $attendance_forms, "status" => 200];
            return ["success" => true, "message" => "Attendance Forms Berhasil Diambil", "data" => $attendance_forms, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $attendance_form = AttendanceForm::with(['users:id,name,profile_image,position', 'creator:id,name,profile_image,position'])->find($id);
            if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Attendance Form Berhasil Diambil", "data" => $attendance_form, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $attendance_form = new AttendanceForm;
        $attendance_form->name = $request->get('name');
        $attendance_form->description = $request->get('description');
        $attendance_form->updated_at = date('Y-m-d H:i:s');
        $attendance_form->created_by = auth()->user()->id;
        $details = $request->get('details', []);
        try{
            if(count($details)){
                $new_details = [];
                foreach($details as $detail){
                    $detail['key'] = Str::uuid()->toString();
                    $new_details[] = $detail;
                }
            }
            $attendance_form->details = $new_details;
            $attendance_form->save();
            return ["success" => true, "message" => "Attendance Form Berhasil Ditambahkan", "id" => $attendance_form->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_form = AttendanceForm::find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        $attendance_form->name = $request->get('name');
        $attendance_form->description = $request->get('description');
        $attendance_form->updated_at = date('Y-m-d H:i:s');
        try{
            $attendance_form->save();
            return ["success" => true, "message" => "Attendance Form Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $attendance_form = AttendanceForm::find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        try{
            $attendance_form->delete();
            return ["success" => true, "message" => "Attendance Form berhasil dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addUserAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $user_ids = $request->get('user_ids', []);
        $attendance_form = AttendanceForm::with('users')->find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        try{
            $attendance_form->users()->syncWithoutDetaching($user_ids);
            return ["success" => true, "message" => "User Attendance Form Berhasil Ditambah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function removeUserAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('id');
        $user_ids = $request->get('user_ids', []);
        $attendance_form = AttendanceForm::with('users')->find($id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        try{
            $attendance_form->users()->detach($user_ids);
            return ["success" => true, "message" => "User Attendance Form Berhasil Dikeluarkan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function sendAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $attendance_form = AttendanceForm::find($request->get('id'));
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($attendance_form->status === 4) return ["success" => false, "message" => "Attendance Form Telah Dikirim", "status" => 400];
        if($attendance_form->status !== 2) return ["success" => false, "message" => "Attendance Form Tidak Dapat Dikirim, Status Attendance Form Bukan Pada Status Disetujui!", "status" => 400];
        
        try{
            $attendance_form->status = 4;
            $attendance_form->save();
            $logService = new LogService;
            $logService->sendAttendanceForm($attendance_form->id, $attendance_form->vendor_id);
            return ["success" => true, "message" => "Attendance Form Berhasil Dikirim", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function receiveAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $attendance_form = AttendanceForm::with('modelOrders')->find($request->get('id'));
        if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($attendance_form->status === 5) return ["success" => false, "message" => "Attendance Form Telah Diterima", "status" => 400];
        if($attendance_form->status !== 4) return ["success" => false, "message" => "Attendance Form Tidak Dapat Diterima, Status Attendance Form Bukan Pada Status Dikirim!", "status" => 400];
        
        try{
            $attendance_form->arrived_date = date('Y-m-d H:i:s');
            $attendance_form->status = 5;
            $attendance_form->save();
            $logService = new LogService;
            $logService->receiveAttendanceForm($attendance_form->id);
            $inventory_parts = [];
            $purchase_quality_control = new PurchaseQC;
            $purchase_quality_control->attendance_form_id = $attendance_form->id;
            $purchase_quality_control->status = 1;
            $purchase_quality_control->save();
            if(count($attendance_form->modelOrders)){
                foreach($attendance_form->modelOrders as $model){
                    for($i = 0; $i < $model->pivot->quantity; $i++){
                        $this->addPurchaseQCDetail($model, $purchase_quality_control->id, null);
                    }
                }
            }
            return ["success" => true, "message" => "Attendance Form Berhasil Diterima", "status" => 200];
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
    
    // Detail Attendance Form
    public function getDetailAttendanceForms($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        try{
            $id = $request->get('id');
            $sort_by = $request->get('sort_by');
            $sort_type = $request->get('sort_type', 'desc');
            
            $attendance_form = AttendanceForm::find($id);
            if($attendance_form === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            
            $attendance_forms = DB::table('model_inventory_attendance_form')->select(DB::raw('model_inventories.id, assets.name as asset_name, model_inventories.name as model_name, model_inventory_attendance_form.quantity, model_inventory_attendance_form.price, model_inventory_attendance_form.warranty_period, model_inventory_attendance_form.warranty_descripition, model_inventory_attendance_form.quantity * model_inventory_attendance_form.price as total_price'))->where('attendance_form_id', $id)
            ->join('model_inventories', 'model_inventory_attendance_form.model_inventory_id', '=', 'model_inventories.id')
            ->join('assets', 'model_inventories.asset_id', '=', 'assets.id');

            if($sort_by){
                if($sort_by === 'asset') $attendance_forms = $attendance_forms->orderBy('asset_name', $sort_type);
                else if($sort_by === 'model') $attendance_forms = $attendance_forms->orderBy('model_name', $sort_type);
                else if($sort_by === 'quantity') $attendance_forms = $attendance_forms->orderBy('quantity', $sort_type);
                else if($sort_by === 'price') $attendance_forms = $attendance_forms->orderBy('price', $sort_type);
                else if($sort_by === 'total_price') $attendance_forms = $attendance_forms->orderBy('total_price', $sort_type);
                else if($sort_by === 'warranty_period') $attendance_forms = $attendance_forms->orderBy('warranty_period', $sort_type);
            }
            $attendance_forms = $attendance_forms->get();

            if($attendance_forms->isEmpty()) return ["success" => true, "message" => "Detail Attendance Forms Masih Kosong", "data" => $attendance_forms, "status" => 200];
            return ["success" => true, "message" => "Detail Attendance Forms Berhasil Diambil", "data" => $attendance_forms, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function addDetailAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model_id = $request->get('model_id');
        if($model_id === null) return ["success" => false, "message" => "Model Id Belum Diisi!", "status" => 400];
        $attendance_form_id = $request->get('attendance_form_id');
        $attendance_form = AttendanceForm::with('modelInventories')->find($attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Attendance Form Tidak Ditemukan", "status" => 400];
        if($attendance_form->status < 3) return ["success" => false, "message" => "Attendance Form Tidak Bisa Dihapus, Status Purchase Tidak Tepat!", "status" => 400];
        $search = $attendance_form->modelInventories->search(function ($item) use ($model_id) {
            return $item->id === $model_id;
        });

        if($search !== false) return ["success" => false, "message" => "Model Sudah Dimasukkan Pada Detail Attendance Form", "status" => 400];
        
        $price = $request->get('price');
        if($price === null) return ["success" => false, "message" => "Harga Belum Diisi!", "status" => 400];
        $quantity = $request->get('quantity');
        if($quantity === null) return ["success" => false, "message" => "Jumlah Belum Diisi!", "status" => 400];
        $warranty_period = $request->get('warranty_period');
        if($warranty_period === null) return ["success" => false, "message" => "Garansi Belum Diisi!", "status" => 400];
        $warranty_descripition = $request->get('warranty_descripition');
        try{
            $attendance_form->modelInventories()->attach($model_id, ['price' => $price, 'quantity' => $quantity, 'warranty_period' => $warranty_period, 'warranty_descripition' => $warranty_descripition]);
            return ["success" => true, "message" => "Detail Attendance Form Berhasil Ditambahkan", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateDetailAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model_id = $request->get('model_id');
        if($model_id === null) return ["success" => false, "message" => "Model Id Belum Diisi!", "status" => 400];
        $attendance_form_id = $request->get('attendance_form_id');
        $attendance_form = AttendanceForm::with('modelInventories')->find($attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Attendance Form Tidak Ditemukan", "status" => 400];
        if($attendance_form->status < 3) return ["success" => false, "message" => "Attendance Form Tidak Bisa Dihapus, Status Purchase Tidak Tepat!", "status" => 400];
        $search = $attendance_form->modelInventories->search(function ($item) use ($model_id) {
            return $item->id === $model_id;
        });

        if($search === false) return ["success" => false, "message" => "Model Tidak Termasuk Pada Detail Attendance Form", "status" => 400];
        
        $price = $request->get('price');
        if($price === null) return ["success" => false, "message" => "Harga Belum Diisi!", "status" => 400];
        $quantity = $request->get('quantity');
        if($quantity === null) return ["success" => false, "message" => "Jumlah Belum Diisi!", "status" => 400];
        $warranty_period = $request->get('warranty_period');
        if($warranty_period === null) return ["success" => false, "message" => "Garansi Belum Diisi!", "status" => 400];
        $warranty_descripition = $request->get('warranty_descripition');
        try{
            $attendance_form->modelInventories()->syncWithoutDetaching([$model_id => ['price' => $price, 'quantity' => $quantity, 'warranty_period' => $warranty_period, 'warranty_descripition' => $warranty_descripition]]);
            return ["success" => true, "message" => "Detail Attendance Form Berhasil Diubah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteDetailAttendanceForm($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $model_id = $request->get('model_id');
        if($model_id === null) return ["success" => false, "message" => "Model Id Belum Diisi!", "status" => 400];
        $attendance_form_id = $request->get('attendance_form_id');
        $attendance_form = AttendanceForm::with('modelInventories')->find($attendance_form_id);
        if($attendance_form === null) return ["success" => false, "message" => "Id Attendance Form Tidak Ditemukan", "status" => 400];
        if($attendance_form->status < 3) return ["success" => false, "message" => "Attendance Form Tidak Bisa Dihapus, Status Purchase Tidak Tepat!", "status" => 400];
        
        try{
            $attendance_form->modelInventories()->detach($model_id);
            return ["success" => true, "message" => "Detail Attendance Form Berhasil Dihapus", "status" => 200];
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
            $purchase_quality_controls = PurchaseQC::select('*')->join('attendance_forms', 'purchase_q_c_s.id', '=', 'attendance_forms.id');
            
            $params = "?rows=$rows";
            // if($keyword) $params = "$params&keyword=$keyword";
            // if($sort_by) $params = "$params&sort_by=$sort_by";
            // if($sort_type) $params = "$params&sort_type=$sort_type";
            // if($asset) $params = "$params&asset=$asset";
            // if($vendor) $params = "$params&vendor=$vendor";
            // if($status) $params = "$params&status=$status";
            
            // if($asset){
            //     $model_ids = DB::table('model_inventories')->where('asset_id', $asset)->pluck('id');
            //     $attendance_form_ids = DB::table('model_inventory_attendance_form')->whereIn('model_inventory_id', $model_ids)->pluck('attendance_form_id');
            //     $purchase_quality_controls = $purchase_quality_controls->whereIn('purchase_quality_controls.id', $attendance_form_ids);
            // } 
            // if($vendor) $purchase_quality_controls = $purchase_quality_controls->where('vendor_id', $vendor);
            // if($keyword){
            //     if(is_numeric($keyword)){
            //         $purchase_quality_controls = $purchase_quality_controls->where(function ($query) use ($keyword){
            //             $query->where('attendance_form_number', 'like', "%".$keyword."%")->orWhere('purchase_quality_controls.id', $keyword);
            //         });
            //     } else $purchase_quality_controls = $purchase_quality_controls->where('attendance_form_number', 'like', "%".$keyword."%");
            // } 
            // if($status) $purchase_quality_controls = $purchase_quality_controls->where('status', $status);
            
            // if($sort_by){
            //     if($sort_type === null) $sort_type = 'desc';
            //     if($sort_by === 'po_number') $purchase_quality_controls = $purchase_quality_controls->orderBy('attendance_form_number', $sort_type);
            //     else if($sort_by === 'po_date') $purchase_quality_controls = $purchase_quality_controls->orderBy('attendance_form_date', $sort_type);
            //     else if($sort_by === 'vendor') $purchase_quality_controls = $purchase_quality_controls->orderBy('vendor_name', $sort_type);
            //     else if($sort_by === 'status') $purchase_quality_controls = $purchase_quality_controls->orderBy('status', $sort_type);
            //     else if($sort_by === 'arrived_date') $purchase_quality_controls = $purchase_quality_controls->orderBy('arrived_date', $sort_type);
            // }
            
            $purchase_quality_controls = $purchase_quality_controls->paginate($rows);
            $purchase_quality_controls->withPath(env('APP_URL').'/getQualityControlPurchases'.$params);
            if($purchase_quality_controls->isEmpty()) return ["success" => true, "message" => "Attendance Forms Masih Kosong", "data" => $purchase_quality_controls, "status" => 200];
            // $statuses = $this->globalService->statusQualityControlPurchase();
            // foreach($purchase_quality_controls as $attendance_form){
            //     $attendance_form->attendance_form_date_template = date("d F Y", strtotime($attendance_form->attendance_form_date));
            //     if($attendance_form->arrived_date !== null) $attendance_form->arrived_date_template = date("d F Y", strtotime($attendance_form->arrived_date));
            //     else $attendance_form->arrived_date_template = "-";
            //     $attendance_form->status_name = $statuses[$attendance_form->status];
            //     if(count($attendance_form->modelInventories)){
            //         foreach($attendance_form->modelInventories as $model){
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
            $purchase_quality_control = PurchaseQC::with(['purchaseOrder:id,attendance_form_number,attendance_form_date,arrived_date,status,vendor_id', 'purchaseOrder.vendor:id,name', 'purchaseOrder.modelInventories:id,name', 'PurchaseQCDetail'])->find($id);
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