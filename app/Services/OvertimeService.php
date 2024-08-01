<?php

namespace App\Services;

use App\Contract;
use App\Employee;
use App\EmployeeContract;
use App\File;
use App\Overtime;
use App\OvertimeStatus;
use App\OvertimeType;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OvertimeService
{
  public function __construct()
  {
      $this->globalService = new GlobalService;
      $this->table = 'App\Overtime';
      $this->folder_detail = 'Overtime';
  }

  public function getOvertimeStatistics(Request $request, $route_name){
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;

    $has_overtime = count(Employee::has('overtime')->get());
    $no_overtime = count(Employee::whereDoesntHave('overtime')->get());
    $statistics = (object)[
        "has_overtime" => $has_overtime,
        "no_overtime" => $no_overtime
    ];
    return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $statistics, "status" => 200];
  }

  public function getOvertimeStatus(Request $request, $route_name){
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;

    $statistics = DB::table('overtimes')->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();
    return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $statistics, "status" => 200];
  }

  public function getOvertimes(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $keyword = $request->keyword;
      $rows = $request->rows ?? NULL;
      try{
          $overtimes = Overtime::with(['document', 'status', 'project', 'employee', 'manager']);
          if($keyword) $overtimes = $overtimes->where("name","LIKE", "%$keyword%");
          if($rows){
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $overtimes->paginate($rows), "status" => 200];  
          }
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $overtimes->get(), "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function getOvertime(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $id = $request->id;
      try{
          $overtimes = Overtime::with(['document', 'status', 'project', 'employee', 'manager'])->find($id);
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $overtimes, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function getOvertimesUser(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      $user = User::with('employee')->find(auth()->user()->id);
      if(!$user->employee) return ["success" => false, "message" => "User tidak terdaftar sebagai Employee", "status" => 400];
      $rows = $request->rows ?? NULL;
      
      try{
          $overtimes = Overtime::with(['document', 'status', 'project', 'employee', 'manager'])->where("employee_id", $user->employee->id);
          if($rows){
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $overtimes->paginate($rows), "status" => 200];  
          }
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $overtimes->get(), "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  public function addOvertime(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "employee_id" => "numeric|required|exists:App\Employee,id",
          "manager_id" => "numeric|exists:App\Employee,id",
          "project_id" => "numeric|exists:App\Project,id",
          "start_at" => "required",
          "end_at" => "required",
          "document" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      try{
          $overtime = new Overtime();
          $overtime->notes = $request->notes;
          
          $employee = Employee::find($request->employee_id);
          if($employee === null) return ["success" => false, "message" => "Id Karyawan Tidak Ditemukan", "status" => 400];
          $overtime->employee_id = $employee->id;

          if($request->manager_id){
            $manager = Employee::find($request->manager_id);
            if($manager === null) return ["success" => false, "message" => "Id Manajer Tidak Ditemukan", "status" => 400];
            $overtime->manager_id = $manager->id;
          }

          if($request->project_id){
            $project = Project::find($request->project_id);
            if($project === null) return ["success" => false, "message" => "Id Project Tidak Ditemukan", "status" => 400];
            $overtime->project_id = $project->id;
          }

          $duration = Carbon::parse($request->start_at)->diffInHours(Carbon::parse($request->end_at));
          
          $overtime->duration=$duration;
          $overtime->start_at = $request->start_at;
          $overtime->end_at = $request->end_at;
          $overtime->issued_date = Date('Y-m-d H:i:s');
          $overtime->status_id = 1;
          
          if(!$overtime->save()) return ["success" => false, "message" => "Gagal Menambah Overtime", "status" => 400];

          if($request->document) $this->addDocument($overtime->id, $request->document, "document");

          return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $overtime->id, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function addOvertimeUser(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      $validator = Validator::make($request->all(), [
          "manager_id" => "numeric|exists:App\Employee,id",
          "project_id" => "numeric|exists:App\Project,id",
          "start_at" => "required",
          "end_at" => "required",
          "document" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      try{
          $overtime = new Overtime();
          $overtime->notes = $request->notes;
          
          $user = auth()->user()->id;
          $employee = Employee::where('user_id', $user)->first();
          if($employee === null) return ["success" => false, "message" => "Id Karyawan Tidak Ditemukan", "status" => 400];
          $overtime->employee_id = $employee->id;

          if($request->manager_id){
            $manager = Employee::find($request->manager_id);
            if($manager === null) return ["success" => false, "message" => "Id Manajer Tidak Ditemukan", "status" => 400];
            $overtime->manager_id = $manager->id;
          }

          if($request->project_id){
            $project = Project::find($request->project_id);
            if($project === null) return ["success" => false, "message" => "Id Project Tidak Ditemukan", "status" => 400];
            $overtime->project_id = $project->id;
          }

          $duration = Carbon::parse($request->start_at)->diffInHours(Carbon::parse($request->end_at));
          
          $overtime->duration=$duration;
          $overtime->start_at = $request->start_at;
          $overtime->end_at = $request->end_at;
          $overtime->issued_date = Date('Y-m-d H:i:s');
          $overtime->status_id = 1;
          
          if(!$overtime->save()) return ["success" => false, "message" => "Gagal Menambah Overtime", "status" => 400];

          if($request->document) $this->addDocument($overtime->id, $request->document, "document");

          return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $overtime->id, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function deleteOvertimeFunc(Request $request, $is_user = false)
  {
      $validator = Validator::make($request->all(), [
          "id" => "required|exists:App\Overtime,id"
      ]);

      if($validator->fails()){
          $errors = $validator->errors()->all();
          return ["success" => false, "message" => $errors, "status" => 400];
      }
      
      $id = $request->id;
      $overtime = Overtime::find($id);
      if(!$overtime) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

      try{
          if($is_user){
            $user = auth()->user()->id;
            $employee = Employee::where('user_id', $user)->first()->id;
            $user_overtime = $overtime->get()->employee_id;
            if($employee != $user_overtime){
                return ["success" => false, "message" => "Pengajuan overtime bukan milik user", "status" => 400];
            }
          }
          $overtime->delete();
          return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $overtime, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err, "status" => 400];
      }
  }

  public function deleteOvertime(Request $request, $route_name){
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;

    return $this->deleteOvertimeFunc($request, false);
  }

  public function deleteOvertimeUser(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      
      return $this->deleteOvertimeFunc($request, true);
  }

  public function updateOvertime(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;
    
    $id = $request->id ?? NULL;
    $validator = Validator::make($request->all(), [
        "id" => "required|exists:App\Overtime,id",
        "employee_id" => "numeric|exists:App\Employee,id",
        "manager_id" => "numeric|exists:App\Employee,id",
        "project_id" => "numeric|exists:App\Project,id",
        "document" => "mimes:pdf|mimetypes:application/pdf|file|max:5120",
    ]);
    

    if($validator->fails()){
        $errors = $validator->errors()->all();
        return ["success" => false, "message" => $errors, "status" => 400];
    }
    
    $overtime = Overtime::find($id);
    if(!$overtime) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

    if($request->notes)$overtime->notes = $request->notes;
    if($request->status_id)$overtime->status_id = $request->status_id;
    if($request->employee_id)$overtime->employee_id = $request->employee_id;
    if($request->manager_id)$overtime->manager_id = $request->manager_id;
    if($request->project_id)$overtime->project_id = $request->project_id;
    
    $file = $request->file('document');
          if($file){
              $oldFile = File::where(['fileable_id' => $overtime->id, 'fileable_type' => $this->table])->first(); //using first() because resume just single file
              $addResume = $this->addDocument($overtime->id, $file, "document");
              $fileService = new FileService;
              if($oldFile){
                  $deleteDocument = $fileService->deleteForceFile($oldFile->id);
              }
          }
    if(!$overtime->save()) return ["success" => false, "message" => "Gagal Mengubah Overtime", "status" => 400];
    return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
  }

   
  public function approveOvertime(Request $request, $route_name)
  {
    $access = $this->globalService->checkRoute($route_name);
    if($access["success"] === false) return $access;
    
    $overtime_id = $request->id;
    $approve = $request->approve;

    $overtime = Overtime::with(['employee', 'type'])->find($overtime_id);

    if($approve){
      $overtime->status = 2;
    }
    else $overtime->status = 3;
    $overtime->save();
    return ["success" => true, "message" => $overtime, "status" => 200];
  }

  //OVERTIME STATUS

  public function getOvertimeStatuses(Request $request, $route_name)
  {
      $access = $this->globalService->checkRoute($route_name);
      if($access["success"] === false) return $access;
      try{
          $overtimeTypes = OvertimeStatus::get();
          
          return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $overtimeTypes, "status" => 200];
      }catch(Exception $err){
          return ["success" => false, "message" => $err->getMessage(), "status" => 400];
      }
  }

  //DOCUMENT FILE
  private function addDocument($id, $file, $description)
  {
      $fileService = new FileService;
      $add_file_response = $fileService->addFile($id, $file, $this->table, $description, $this->folder_detail, false);
      return $add_file_response;
  }

  public function addOvertimeDocument(Request $request, $route_name)
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