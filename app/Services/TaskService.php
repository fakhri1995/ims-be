<?php

namespace App\Services;

use App\Services\CheckRouteService;
use App\TaskType;
use App\TaskTypeWork;
use Exception;

class TaskService{

    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }
    // Task Type

    public function getTaskTypes($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $name = $request->get('name', null);
            $rows = $request->get('rows', 10);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            
            $tasks = TaskType::select('*');

            if($name) $tasks = $tasks->where('name', 'ilike', "%".$name."%");
            if($sort_by){
                if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
                // else if($sort_by === 'count') $tasks = $tasks->orderBy('inventories_count', $sort_type);
            }
            
            $tasks = $tasks->paginate($rows);
            if($tasks->isEmpty()) return ["success" => false, "message" => "Tipe Task Belum Terdaftar", "status" => 200];
            return ["success" => true, "message" => "Tipe Task Berhasil Diambil", "data" => $tasks, "status" => 200];
            
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->get('id', null);
            $task = TaskType::with('works')->find($id);
            if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Tipe Task Berhasil Diambil", "data" => $task, "status" => 200];
            
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
        // Single Textbox
        // Paragraf
        // Checkbox
        // Matrix checkbox
        // Number
        // Dropdown

    public function checkTypes($work, $index, $order_type = 'Add'){
        if($order_type === 'Update_Add') $pekerjaan = "Tambahan Pekerjaan";
        else if($order_type === 'Update_Update') $pekerjaan = "Update Pekerjaan Dengan Id";
        else $pekerjaan = "Pekerjaan";

        if(!isset($work['name'])) return ["success" => false, "message" => "Nama $pekerjaan $index Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe $pekerjaan $index Masih Kosong", "status" => 400];
        $type = (int) $work['type'];
        $details = (object)[];

        if($type < 1 || $type > 6){
            return ["success" => false, "message" => "Tipe $pekerjaan $index Tidak Tepat", "status" => 400];
        } else if($type === 3){
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan $index Belum Memiliki List Checkbox", "status" => 400];
            $details = (object)["lists" => $lists];
        } else if($type === 4){
            if(isset($work['columns'])) $columns = $work['columns'];
            else $columns = [];
            if(!count($columns)) return ["success" => false, "message" => "$pekerjaan $index Belum Memiliki Kolom", "status" => 400];
            $is_general = filter_var($work['is_general'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if($is_general){
                if(isset($work['rows'])) $rows = $work['rows'];
                else $rows = [];
                if(!count($rows)) return ["success" => false, "message" => "$pekerjaan $index Belum Memiliki Baris", "status" => 400];
            }
            $details = (object)[
                "is_general" => $is_general,
                "rows" => $rows ?? [],
                "columns" => $columns
            ];
        } else if($type === 5){
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan $index Belum Memiliki List Numeral", "status" => 400];
            $index_number = 1;
            foreach($lists as $list){
                if(!isset($list['type'])) return ["success" => false, "message" => "$pekerjaan $index Dengan List $index_number Belum Memiliki Tipe", "status" => 400];
                if(!isset($list['description'])) return ["success" => false, "message" => "$pekerjaan $index Dengan List $index_number Belum Memiliki Keterangan", "status" => 400];
            }
            $details = (object)["lists" => $lists];
        } else if($type === 6){
            if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "$pekerjaan $index Dengan List Belum Memiliki Nama Dropdown", "status" => 400];
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan $index Belum Memiliki List Dropdown", "status" => 400];
            $details = (object)["lists" => $lists];
        } 
        return ['success' => true, 'type' => $type, 'details' => $details];
    }

    public function addTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $task = new TaskType;
        $task->name = $request->get('name');
        $task->description = $request->get('description');
        $works = $request->get('works', []);
        if(!count($works)) return ["success" => false, "message" => "Pekerjaan Belum Diisi", "status" => 400];
        
        $index = 1;
        $new_works = [];
        foreach($works as $work){
            $result = $this->checkTypes($work, $index);
            if(!$result['success']) return $result;
            $new_works[] = new TaskTypeWork([
                "name" => $work['name'],
                "description" => $work['description'] ?? null,
                "type" => $result['type'],
                "details" => $result['details'],
            ]);
            $index++;
        }

        try{
            $task->save();
            $task->works()->saveMany($new_works);
            return ["success" => true, "message" => "Tipe Task Berhasil Dibuat", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $task = TaskType::find($id);
        if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];$task->name = $request->get('name');
        $task->name = $request->get('name');
        $task->description = $request->get('description');
        $add_works = $request->get('add_works', []);
        $update_works = $request->get('update_works', []);
        $delete_works = $request->get('delete_works', []);
        
        $index = 1;
        $new_works = [];
        if(count($add_works)){       
            foreach($add_works as $work){
                $result = $this->checkTypes($work, $index, 'Update_Add');
                if(!$result['success']) return $result;
                $new_works[] = new TaskTypeWork([
                    "name" => $work['name'],
                    "description" => $work['description'] ?? null,
                    "type" => $result['type'],
                    "details" => $result['details']
                ]);
                $index++;
            }
        }

        $new_update_works = [];
        if(count($update_works)){       
            foreach($update_works as $work){
                $temp_id = $work['id'];
                $temp_work = TaskTypeWork::find($temp_id);
                if($temp_work === null) return ["success" => false, "message" => "Update Dengan Id $temp_id Tidak Ditemukan", "status" => 400];
                if($temp_work->task_type_id !== $id) return ["success" => false, "message" => "Update Dengan Id $temp_id Bukan Milik Tipe Task", "status" => 400];
                $result = $this->checkTypes($work, $temp_id, 'Update_Update');
                if(!$result['success']) return $result;
                $temp_work->name = $work['name'];
                $temp_work->description = $work['description'];
                $temp_work->type = $result['type'];
                $temp_work->details = $result['details'];
                
                $new_update_works[] = $temp_work;
            }
        }

        $new_delete_works = [];
        if(count($delete_works)){       
            foreach($delete_works as $work){
                $temp_work = TaskTypeWork::find($work);
                if($temp_work === null) return ["success" => false, "message" => "Delete Dengan Id $work Tidak Ditemukan", "status" => 400];
                if($temp_work->task_type_id !== $id) return ["success" => false, "message" => "Delete Dengan Id $work Bukan Milik Tipe Task", "status" => 400];
                
                $new_delete_works[] = $temp_work;
            }
        }

        try{
            $task->save();
            $task->works()->saveMany($new_works);

            foreach($new_update_works as $work) $work->save();
            foreach($new_delete_works as $work) $work->delete();

            return ["success" => true, "message" => "Tipe Task Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTaskType($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $task = TaskType::find($id);
        if($task === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $task->delete();
            return ["success" => true, "message" => "Tipe Task Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}