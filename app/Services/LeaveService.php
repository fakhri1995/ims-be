<?php

namespace App\Services;

use App\Contract;
use App\Employee;
use App\EmployeeContract;
use App\File;
use App\Leave;
use App\LeaveType;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveService
{
  public function __construct()
  {
      $this->globalService = new GlobalService;
      $this->table = 'App\Leave';
      $this->folder_detail = 'Leave';
  }

  public function getLeaveStatuses(Request $request, $route_name){
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;

    $statistics = DB::table('leaves')->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();
    return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $statistics, "status" => 200];
  }

  public function getLeaves(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $keyword = $request->keyword;
      $rows = $request->rows ?? NULL;
      try{
          $leaves = Leave::with(['document', 'type', 'employee', 'delegate']);
          if($keyword) $leaves = $leaves->where("name","LIKE", "%$keyword%");
          if($rows){
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaves->paginate($rows), "status" => 200];  
          }
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaves->get(), "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function getLeave(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $id = $request->id;
      try{
          $leaves = Leave::with(['document', 'type', 'employee', 'delegate'])->find($id);
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaves, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function getLeavesUser(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $user = User::with('employee')->find(auth()->user()->id);
      if(!$user->employee) return ["success" => false, "message" => "User tidak terdaftar sebagai Employee", "status" => 400];
      $rows = $request->rows ?? NULL;
      
      try{
          $leaves = Leave::with(['document', 'type', 'employee', 'delegate'])->where("employee_id", $user->employee->id);
          if($rows){
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaves->paginate($rows), "status" => 200];  
          }
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaves->get(), "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function addLeave(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "type" => "numeric|required|exists:App\LeaveType,id",
          "employee_id" => "numeric|required|exists:App\Employee,id",
          "delegate_id" => "numeric|exists:App\Employee,id",
          "start_date" => "required",
          "end_date" => "required",
          "document" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      try{
          $leave = new Leave();
          $leave->notes = $request->notes;
          
          $employee = Employee::find($request->employee_id);
          if($employee === null) return ["success" => false, "message" => "Id Karyawan Tidak Ditemukan", "status" => 400];
          $leave->employee_id = $employee->id;

          if($request->delegate_id){
            $delegate = Employee::find($request->delegate_id);
            if($delegate === null) return ["success" => false, "message" => "Id Karyawan Tidak Ditemukan", "status" => 400];
            $leave->delegate_id = $delegate->id;
          }
          
          $type = LeaveType::find($request->type);
          if($type === null) return ["success" => false, "message" => "Id Tipe Cuti Tidak Ditemukan", "status" => 400];
          $leave->type = $type->id;


          $duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));
          
          $leave->duration=$duration;
          $leave->start_date = $request->start_date;
          $leave->end_date = $request->end_date;
          $leave->issued_date = Date('Y-m-d H:i:s');
          $leave->status = 1;
          
          if(!$request->document && $type->is_document_required)  return ["success" => false, "message" => "Dokumen pendukung harus diisi.", "status" => 400];
          if(!$leave->save()) return ["success" => false, "message" => "Gagal Menambah Leave", "status" => 400];

          if($request->document) $this->addDocument($leave->id, $request->document, "document");

          return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $leave->id, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function addLeaveUser(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "type" => "numeric|required|exists:App\LeaveType,id",
          "delegate_id" => "numeric|required|exists:App\Employee,id",
          "start_date" => "required",
          "end_date" => "required",
          "document" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      try{
          $leave = new Leave();
          $leave->notes = $request->notes;
          
          $user = auth()->user()->id;
          $employee = Employee::where('user_id', $user)->first();
          if($employee === null) return ["success" => false, "message" => "Id Karyawan Tidak Ditemukan", "status" => 400];
          $leave->employee_id = $employee->id;

          $delegate = Employee::find($request->delegate_id);
          if($delegate === null) return ["success" => false, "message" => "Id Karyawan Tidak Ditemukan", "status" => 400];
          $leave->delegate_id = $delegate->id;
          
          $type = LeaveType::find($request->type);
          if($type === null) return ["success" => false, "message" => "Id Tipe Cuti Tidak Ditemukan", "status" => 400];
          $leave->type = $type->id;


          $duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));
          
          $leave->duration=$duration;
          $leave->start_date = $request->start_date;
          $leave->end_date = $request->end_date;
          $leave->issued_date = Date('Y-m-d H:i:s');
          $leave->status = 1;
          
          if(!$request->document && $type->is_document_required)  return ["success" => false, "message" => "Dokumen pendukung harus diisi.", "status" => 400];
          if(!$leave->save()) return ["success" => false, "message" => "Gagal Menambah Leave", "status" => 400];

          if($request->document) $this->addDocument($leave->id, $request->document, "document");

          return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $leave->id, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function deleteLeave(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "id" => "required|exists:App\Leave,id"
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      $id = $request->id;
      $leave = Leave::find($id);
      if(!$leave) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

      try{
          $leave->delete();
          return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $leave, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function updateLeave(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;
    
    $id = $request->id ?? NULL;
    $validator = Validator::make($request->all(), [
        "id" => "required|exists:App\Leave,id",
        "type" => "numeric|exists:App\LeaveType,id",
        "employee_id" => "numeric|exists:App\Employee,id",
        "delegate_id" => "numeric|exists:App\Employee,id",
        "document" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
    ]);
    

    if($validator->fails()){
        $errors = $validator->errors()->all();
        return ["success" => false, "message" => $errors, "status" => 400];
    }
    
    $leave = Leave::find($id);
    if(!$leave) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

    if($request->notes)$leave->notes = $request->notes;
    if($request->type)$leave->type = $request->type;
    if($request->employee_id)$leave->employee_id = $request->employee_id;
    if($request->delegate_id)$leave->delegate_id = $request->delegate_id;
    
    $file = $request->file('document');
          if($file){
              $oldFile = File::where(['fileable_id' => $leave->id, 'fileable_type' => $this->table])->first(); //using first() because resume just single file
              $addResume = $this->addDocument($leave->id, $file, "document");
              $fileService = new FileService;
              if($oldFile){
                  $deleteDocument = $fileService->deleteForceFile($oldFile->id);
              }
          }
    if(!$leave->save()) return ["success" => false, "message" => "Gagal Mengubah Cuti", "status" => 400];
    return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
  }

   
  public function approveLeave(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;
    
    $leave_id = $request->id;
    $approve = $request ->approve;

    $leave = Leave::with(['employee', 'type'])->find($leave_id);
    $type = LeaveType::find($leave->type);
    $employee = Employee::with('contract')->find($leave->employee->id);
    $contract = EmployeeContract::find($employee->contract->id);

    if($approve){
      $leave->status = 2;
      if($type->is_tahunan) $contract->annual_leave = $contract->annual_leave - 1;
      
      $contract->save();
    }
    else $leave->status = 3;
    $leave->save();
    return ["success" => true, "message" => $contract, "status" => 200];
  }

  //LEAVE TYPE

  public function getLeaveTypes(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      try{
          $leaveTypes = LeaveType::get();
          
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaveTypes, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function getLeaveType(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $id = $request->id;
      try{
          $leaveType = LeaveType::find($id);
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $leaveType, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function addLeaveType(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "name" => "required",
          "is_tahunan" => "boolean|required",
          "is_document_required" => "boolean|required",
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      try{
          $leaveType = new LeaveType();
          $leaveType->name = $request->name;
          $leaveType->description = $request->description;
          $leaveType->is_tahunan = $request->is_tahunan;
          $leaveType->is_document_required = $request->is_document_required;

          if(!$leaveType->save()) return ["success" => false, "message" => "Gagal Menambah Leave Type", "status" => 400];

          return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $leaveType->id, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function updateLeaveType(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;
    
    $id = $request->id ?? NULL;
    $validator = Validator::make($request->all(), [
        "id" => "required|exists:App\LeaveType,id",
        "is_document_required" => "numeric|in:0,1",
        "is_document_required" => "numeric|in:0,1",
        "is_tahunan" => "numeric|in:0,1",
    ]);
    

    if($validator->fails()){
        $errors = $validator->errors()->all();
        return ["success" => false, "message" => $errors, "status" => 400];
    }
    
    $leaveType = LeaveType::find($id);
    if(!$leaveType) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

    if($request->name)$leaveType->name = $request->name;
    if($request->description)$leaveType->description = $request->description;
    if($request->is_document_required != NULL)$leaveType->is_document_required = $request->is_document_required;
    if($request->is_tahunan != NULL)$leaveType->is_tahunan = $request->is_tahunan;

    if(!$leaveType->save()) return ["success" => false, "message" => "Gagal Mengubah Tipe Cuti", "status" => 400];
    return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
  }

  public function deleteLeaveType(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "id" => "required|exists:App\LeaveType,id"
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      $id = $request->id;
      $leaveType = LeaveType::find($id);
      if(!$leaveType) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

      try{
          $leaveType->delete();
          return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $leaveType, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  //DOCUMENT FILE
  private function addDocument($id, $file, $description)
  {
      $fileService = new FileService;
      $add_file_response = $fileService->addFile($id, $file, $this->table, $description, $this->folder_detail, false);
      return $add_file_response;
  }

  public function getLeavesCount(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;

    $user = User::with('employee','employee.contract')->find(auth()->user()->id);
    return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $user->employee->contract->annual_leave, "status" => 200];
  }

  public function addLeaveDocument(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;

    $id = $request->id;
    $file = $request->file('file');
    $fileService = new FileService;
    $add_file_response = $fileService->addFile($id, $file, $this->table, "document", $this->folder_detail, false);
    return ["success" => true, "message" => "Dokumen Berhasil Diunggah", "status" => 200];
  }
  
}