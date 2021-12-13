<?php

namespace App\Services;

use App\Task;
use App\Group;
use Exception;
use App\TaskType;
use App\Inventory;
use App\TaskDetail;
use App\TaskTypeWork;
use App\Services\CheckRouteService;

class TaskService{

    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
    }
    
        // Single Textbox
        // Paragraf
        // Checkbox
        // Matrix checkbox
        // Number
        // Dropdown

    // Task

    private function clusteringNewTaskWorks($works, $inventory_ids)
    {
        $new_works = [];
        foreach($works as $work){
            if($work->type > 0 || $work->type < 7){
                if($work->type > 2){
                    if($work->type === 3){
                        $values = [];
                        foreach($work->details->lists as $list){
                            $values[] = false;
                        }
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "lists" => $work->details->lists, "values" => $values];
                    } else if($work->type === 4){
                        $is_general = $work->details->is_general;
                        $columns = $work->details->columns;
                        if($is_general) $rows = $work->details->rows;
                        else {
                            $inventories = Inventory::with('modelInventory:id,name')->select('id','model_id')->find($inventory_ids);
                            $rows = [];
                            foreach($inventories as $inventory) $rows[] = $inventory->modelInventory->name;
                        }
                        $values = [];
                        foreach($columns as $column){
                            $value_column = [];
                            foreach($rows as $row){
                                $value_column[] = false;
                            }
                            $values[] = $value_column;
                        }
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "rows" => $rows, "columns" => $columns, "is_general" => $is_general, "values" => $values];
                    } else if($work->type === 5){
                        $lists = $work->details->lists;
                        foreach($lists as $list){
                            $list->values = "-";
                        }
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "lists" => $lists];
                    } else if($work->type === 6){
                        $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, "dropdown_name" => $work->details->dropdown_name, "lists" => $work->details->lists, "values" => '-'];
                    } 
                } else {
                    $component = (object)["name" => $work->name, "description" => $work->description, "type" => $work->type, 'values' => '-'];
                }
                $new_works[] = new TaskDetail([
                    "component" => $component
                ]);
            } 
        }
        return $new_works;
    }

    public function getTasks($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            // $name = $request->get('name', null);
            $rows = $request->get('rows', 10);
            $sort_by = $request->get('sort_by', null);
            $sort_type = $request->get('sort_type', 'desc');

            if($rows > 100) $rows = 100;
            if($rows < 1) $rows = 10;
            
            $tasks = Task::select('*');

            // if($name) $tasks = $tasks->where('name', 'ilike', "%".$name."%");
            // if($sort_by){
            //     if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
            //     // else if($sort_by === 'count') $tasks = $tasks->orderBy('inventories_count', $sort_type);
            // }
            
            $tasks = $tasks->paginate($rows);
            if($tasks->isEmpty()) return ["success" => true, "message" => "Task Masih Kosong", "data" => $tasks, "status" => 200];
            return ["success" => true, "message" => "Task Berhasil Diambil", "data" => $tasks, "status" => 200];

        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getTask($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->get('id', null);
            $task = Task::with(['reference.type', 'location:id,name,parent_id,top_parent_id,role','users', 'group:id,name','inventories.modelInventory.asset', 'taskDetails'])->find($id);
            if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $task->location->full_location = $task->location->fullSubNameWParent();
            $task->location->makeHidden(['parent', 'parent_id', 'role']);
            if(count($task->inventories)){
                foreach($task->inventories as $inventory){
                    $inventory->model_name = $inventory->modelInventory->name;
                    $inventory->asset_name = $inventory->modelInventory->asset->fullName();
                    $inventory->makeHidden('modelInventory');
                    $inventory->is_from_task = $inventory->pivot->is_from_task;
                    $inventory->is_in = $inventory->pivot->is_in;
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $task, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addTask($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $is_group = $request->get('is_group', true);
        $assign_ids = $request->get('assign_ids', []);
        $inventory_ids = $request->get('inventory_ids', []);
        $task_type_id = $request->get('task_type_id');
        try{
            $task_type = TaskType::with('works')->find($task_type_id);
            if($task_type === null) return ["success" => false, "message" => "Id Tipe Task Tidak Ditemukan", "status" => 400];
            $task = new Task;
            if($is_group){
                if(count($assign_ids)){
                    $task->group_id = $assign_ids[0];
                    $group = Group::with('users')->find($assign_ids[0]);
                    if($group === null) return ["success" => false, "message" => "Id Group Tidak Ditemukan", "status" => 400];
                } 
                else $task->group_id = null;
            }

            $task->name = $request->get('name');
            $task->description = $request->get('description');
            $task->task_type_id = $task_type_id;
            $task->location_id = $request->get('location_id');
            $task->reference_id = $request->get('reference_id');
            $task->created_by = auth()->user()->id;
            $task->deadline = $request->get('deadline');
            $task->created_at = $request->get('created_at');
            $task->status = 2;
            
            
            $task->save();
            
            if(count($task_type->works)){
                $new_works = $this->clusteringNewTaskWorks($task_type->works, $inventory_ids);
                $task->taskDetails()->saveMany($new_works);
            }
            
            if(count($assign_ids)){
                if($is_group) $task->users()->attach($group->users->pluck('id'));
                else $task->users()->attach($assign_ids);
            }
            if(count($inventory_ids)){
                $attach_inventories = [];
                foreach($inventory_ids as $inventory_id){
                    $attach_inventories[$inventory_id] = ['is_from_task' => true];
                }
                $task->inventories()->attach($attach_inventories);
            }
            return ["success" => true, "message" => "Task Berhasil Dibuat","id" => $task->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTask($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $task = Task::find($id);
        if($task === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        
        $is_group = $request->get('is_group', null);
        if($is_group === null) return ["success" => false, "message" => "Kolom Is Group Belum Diisi", "status" => 400];
        
        $assign_ids = $request->get('assign_ids', []);
        $inventory_ids = $request->get('inventory_ids', []);
        try{
            if($is_group){
                if(count($assign_ids)){
                    $old_group = $task->group_id;
                    $task->group_id = $assign_ids[0];
                    $group = Group::with('users')->find($assign_ids[0]);
                    if($group === null) return ["success" => false, "message" => "Id Group Tidak Ditemukan", "status" => 400];
                } 
            }

            $task->name = $request->get('name');
            $task->description = $request->get('description');
            $task->location_id = $request->get('location_id');
            $task->reference_id = $request->get('reference_id');
            $task->deadline = $request->get('deadline');
            $task->created_at = $request->get('created_at');
            $task->save();
            
            if(count($assign_ids)){
                if($is_group){
                    if($old_group !== $task->group_id) $task->users()->sync($group->users->pluck('id'));
                } else $task->users()->sync($assign_ids);
            }

            if(count($inventory_ids)){
                $attach_inventories = [];
                foreach($inventory_ids as $inventory_id){
                    $attach_inventories[$inventory_id] = ['is_from_task' => true];
                }
                $task->inventories()->sync($attach_inventories);
            }
            return ["success" => true, "message" => "Task Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTask($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $task = Task::find($id);
        if($task === null) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
        try{
            $task->delete();
            $task->taskDetails()->delete();
            $task->users()->detach();
            $task->inventories()->detach();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Task Detail

    private function checkAddTaskDetail($work, $task_id){
        if(!isset($work['name'])) return ["success" => false, "message" => "Nama Pekerjaan Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe Pekerjaan Masih Kosong", "status" => 400];
        $type = (int) $work['type'];

        if($type < 1 || $type > 6){
            return ["success" => false, "message" => "Tipe Pekerjaan Tidak Tepat", "status" => 400];
        } else {
            if($type > 2){
                if($type === 3){
                    if(isset($work['rows'])) $lists = $work['rows'];
                    else $lists = [];
                    if(!count($lists)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki List Checkbox", "status" => 400];
                    $details = (object)["lists" => $lists];
                    $values = [];
                    foreach($lists as $list) $values[] = false;
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "lists" => $lists, "values" => $values];
                } else if($type === 4){
                    if(isset($work['columns'])) $columns = $work['columns'];
                    else $columns = [];
                    if(!count($columns)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki Kolom", "status" => 400];
                    $is_general = filter_var($work['is_general'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    if($is_general){
                        if(isset($work['rows'])) $rows = $work['rows'];
                        else $rows = [];
                        if(!count($rows)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki Baris", "status" => 400];
                    } else {
                        $task = Task::with('inventories')->find($task_id);
                        if(!$task) return ["success" => false, "message" => "Task Pada Pekerjaan Sudah Dihapus", "status" => 400];
                        $inventory_ids = $task->inventories->pluck('id') ?? [];
                        $inventories = Inventory::with('modelInventory:id,name')->select('id','model_id')->find($inventory_ids);
                        $rows = [];
                        if(count($inventories)){
                            foreach($inventories as $inventory) $rows[] = $inventory->modelInventory->name;
                        } else return ["success" => false, "message" => "Task Tidak Memiliki Aset Sehingga Baris Suku Cadang Masih Kosong", "status" => 400];
                    }
                    $values = [];
                    if(count($rows)){
                        foreach($columns as $column){
                            $value_column = [];
                            foreach($rows as $row){
                                $value_column[] = false;
                            }
                            $values[] = $value_column;
                        }
                    }
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "rows" => $rows, "columns" => $columns, "is_general" => $is_general, "values" => $values];
                } else if($type === 5){
                    if(isset($work['rows'])) $lists = $work['rows'];
                    else $lists = [];
                    if(!count($lists)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki List Numeral", "status" => 400];
                    $index_number = 1;
                    foreach($lists as $list){
                        if(!isset($list['type'])) return ["success" => false, "message" => "Pekerjaan Dengan List $index_number Belum Memiliki Tipe", "status" => 400];
                        if(!isset($list['description'])) return ["success" => false, "message" => "Pekerjaan Dengan List $index_number Belum Memiliki Keterangan", "status" => 400];
                    }
                    foreach($lists as $list){
                        $list['values'] = "-";
                    }
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "lists" => $lists];
                } else {
                    if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "Pekerjaan Dengan List Belum Memiliki Nama Dropdown", "status" => 400];
                    if(isset($work['rows'])) $lists = $work['rows'];
                    else $lists = [];
                    if(!count($lists)) return ["success" => false, "message" => "Pekerjaan Belum Memiliki List Dropdown", "status" => 400];
                    $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], "dropdown_name" => $work['dropdown_name'], "lists" => $lists, "values" => '-'];
                }
            } else {
                $component = (object)["name" => $work['name'], "description" => $work['description'] ?? null, "type" => $work['type'], 'values' => '-'];
            }
        }  
        return ['success' => true, 'component' => $component];
    }
    
    public function addTaskDetail($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $task_id = $request->get('task_id');
            $work = $request->get('work', []);
            $task = Task::find($task_id);
            if(!$task) return ["success" => false, "message" => "Task Id Tidak Ditemukan", "status" => 400];
            
            $check = $this->checkAddTaskDetail($work, $task_id);
            if(!$check['success']) return $check;
            $task_detail = new TaskDetail;
            $task_detail->component = $check['component'];
            $task_detail->task_id = $task_id;
            $task_detail->save();
            return ["success" => true, "message" => "Pekerjaan Berhasil Ditambah", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateTaskDetail($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $task_id = $request->get('task_id', null);
        $work = $request->get('work', []);
        $task_detail = TaskDetail::find($id);
        if($task_detail === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($task_detail->task_id !== $task_id) return ["success" => false, "message" => "Task Detail Bukan Milik Task", "status" => 400];
        if(!isset($work['name'])) return ["success" => false, "message" => "Nama Pekerjaan Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe Pekerjaan Masih Kosong", "status" => 400];
        
        try{
            if($task_detail->component->type !== $work['type']){
                $check = $this->checkAddTaskDetail($work, $task_id);
                if(!$check['success']) return $check;
                $component = $check['component'];
            } else {
                $component = $task_detail->component;
                if($work['type'] === 3){
                    $lists = $component->lists;
                    $values = $component->values;
                    if(count($work['update_rows'])){
                        foreach($work['update_rows'] as $row)$lists[$row['number']] = $row['value'];
                    }
                    if(count($work['add_rows'])){
                        foreach($work['add_rows'] as $row){
                            $lists[] = $row;
                            $values[] = false;
                        }
                    }
                    if(count($work['delete_rows'])){
                        foreach($work['delete_rows'] as $row){
                            unset($lists[$row]);
                            unset($values[$row]);
                        } 
                    } 
                    $component->lists = array_values($lists);
                    $component->values = array_values($values);
                } else if($work['type'] === 4){
                    $is_general = $work['is_general'];
                    if($is_general === null) return ["success" => false, "message" => "Pilihan Suku Cadang/Umum Belum Diisi", "status" => 400];
                    $columns = $component->columns;
                    $count_columns = count($columns);
                    if($is_general == $component->is_general){
                        if($is_general){
                            $rows = $component->rows;
                            $values = $component->values;
                            if(count($work['update_rows'])){
                                foreach($work['update_rows'] as $row){
                                    $rows[$row['number']] = $row['values'];
                                } 
                            }
                            if(count($work['delete_rows'])){
                                foreach($work['delete_rows'] as $row){
                                    for($i = 0; $i < $count_columns; $i++){
                                        unset($values[$i][$row]);
                                        if(!count($values[$i])) {
                                            $values = [];
                                            break;
                                        }
                                        $values[$i] = array_values($values[$i]);
                                    }
                                    unset($rows[$row]);
                                } 
                                $rows = array_values($rows);
                            }
                            if(count($work['add_rows'])){
                                foreach($work['add_rows'] as $row){
                                    $rows[] = $row;
                                    for($i = 0; $i < $count_columns; $i++){
                                        $values[$i][] = false;
                                    }
                                }
                            }
                        }
                    } else {
                        $rows = [];
                        $values = [];
                        if($is_general){
                            if(count($work['add_rows'])){
                                $row_values = [];
                                foreach($work['add_rows'] as $row){
                                    $rows[] = $row;
                                    $row_values[] = false;
                                }
                            } else return ["success" => false, "message" => "Kolom Baris Masih Kosong", "status" => 400];
                        } else {
                            $task = Task::with('inventories')->find($task_id);
                            if(!$task) return ["success" => false, "message" => "Task Pada Pekerjaan Sudah Dihapus", "status" => 400];
                            $inventory_ids = $task->inventories->pluck('id') ?? [];
                            $inventories = Inventory::with('modelInventory:id,name')->select('id','model_id')->find($inventory_ids);
                            if(count($inventories)){
                                foreach($inventories as $inventory){
                                    $rows[] = $inventory->modelInventory->name;
                                    $row_values[] = false;
                                }
                            } else return ["success" => false, "message" => "Task Tidak Memiliki Aset Sehingga Baris Suku Cadang Kosong", "status" => 400];
                        }
                        for($i = 0; $i < $count_columns; $i++){
                            $values[] = $row_values;
                        }
                    } 
                    if(count($work['update_columns'])){
                        foreach($work['update_columns'] as $column){
                            $columns[$column['number']] = $column['values'];
                        } 
                    }
                    if(count($work['delete_columns'])){
                        foreach($work['delete_columns'] as $column){
                            unset($columns[$column]);
                            unset($values[$column]);
                        } 
                        $columns = array_values($columns);
                        $values = array_values($values);
                    }
                    if(count($work['add_columns'])){
                        foreach($rows as $row) $column_values[] = false;
                        foreach($work['add_columns'] as $column){
                            $columns[] = $column;
                            $values[] = $column_values;
                        }
                    } 
                    $component->rows = $rows;
                    $component->columns = $columns;
                    $component->values = $values;
                    $component->is_general = $is_general;
                } else if($work['type'] === 5) {
                    $lists = $component->lists;
                    if(count($work['update_rows'])){
                        $index = 1;
                        foreach($work['update_rows'] as $row){
                            if(!isset($row['number'])) return ["success" => false, "message" => "Update Baris $index Belum Memiliki Nomor", "status" => 400];
                            if(!isset($row['type'])) return ["success" => false, "message" => "Update Baris $index Belum Memiliki Satuan", "status" => 400];
                            if(!isset($row['description'])) return ["success" => false, "message" => "Update Baris $index Belum Memiliki Keterangan", "status" => 400];
                            $lists[$row['number']] = (object)['type' => $row['type'], 'description' => $row['description'], 'values' => '-'];
                            $index++;
                        }
                    }
                    if(count($work['add_rows'])){
                        $index = 1;
                        foreach($work['add_rows'] as $row){
                            if(!isset($row['type'])) return ["success" => false, "message" => "Tambah Baris $index Belum Memiliki Satuan", "status" => 400];
                            if(!isset($row['description'])) return ["success" => false, "message" => "Tambah Baris $index Belum Memiliki Keterangan", "status" => 400];
                            $lists[] = (object)['type' => $row['type'], 'description' => $row['description'], 'values' => '-'];
                            $index++;
                        }
                    }
                    if(count($work['delete_rows'])){
                        foreach($work['delete_rows'] as $row) unset($lists[$row]);
                    } 
                    $component->lists = array_values($lists);
                } else if($work['type'] === 6) {
                    if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "Pekerjaan Belum Memiliki Nama Dropdown", "status" => 400];
                    $component->dropdown_name = $work['dropdown_name'];
                    $lists = $component->lists;
                    if(count($work['update_rows'])){
                        foreach($work['update_rows'] as $row)$lists[$row['number']] = $row['value'];
                    }
                    if(count($work['add_rows'])){
                        foreach($work['add_rows'] as $row)$lists[] = $row;
                    }
                    if(count($work['delete_rows'])){
                        foreach($work['delete_rows'] as $row) unset($lists[$row]);
                    } 
                    $component->lists = array_values($lists);
                }
                $component->name = $work['name'];
                $component->description = $work['description'];
            }
            $task_detail->component = $component;
            $task_detail->save();
            return ["success" => true, "message" => "Task Detail Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteTaskDetail($request, $route_name)
    {
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('id', null);
        $task_id = $request->get('task_id', null);
        $task_detail = TaskDetail::find($id);
        if($task_detail === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
        if($task_detail->task_id !== $task_id) return ["success" => false, "message" => "Task Detail Bukan Milik Task", "status" => 400];
        try{
            $task_detail->delete();
            $task_detail->users()->detach();
            return ["success" => true, "message" => "Task Detail Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
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
            
            $tasks = TaskType::select('id', 'name', 'description')->withCount('tasks');

            if($name) $tasks = $tasks->where('name', 'ilike', "%".$name."%");
            if($sort_by){
                if($sort_by === 'name') $tasks = $tasks->orderBy('name', $sort_type);
                else if($sort_by === 'count') $tasks = $tasks->orderBy('tasks_count', $sort_type);
            }
            
            $tasks = $tasks->paginate($rows);
            if($tasks->isEmpty()) return ["success" => true, "message" => "Tipe Task Masih Kosong", "data" => $tasks, "status" => 200];
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

    private function checkTypes($work, $index, $order_type = 'Add'){
        if($order_type === 'Update_Add') $pekerjaan = "Tambahan Pekerjaan $index";
        else if($order_type === 'Update_Update') $pekerjaan = "Update Pekerjaan Dengan Id $index";
        else $pekerjaan = "Pekerjaan $index";

        if(!isset($work['name'])) return ["success" => false, "message" => "Nama $pekerjaan Masih Kosong", "status" => 400];
        if(!isset($work['type'])) return ["success" => false, "message" => "Tipe $pekerjaan Masih Kosong", "status" => 400];
        $type = (int) $work['type'];
        $details = (object)[];

        if($type < 1 || $type > 6){
            return ["success" => false, "message" => "Tipe $pekerjaan Tidak Tepat", "status" => 400];
        } else if($type === 3){
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki List Checkbox", "status" => 400];
            $details = (object)["lists" => $lists];
        } else if($type === 4){
            if(isset($work['columns'])) $columns = $work['columns'];
            else $columns = [];
            if(!count($columns)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki Kolom", "status" => 400];
            $is_general = filter_var($work['is_general'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if($is_general){
                if(isset($work['rows'])) $rows = $work['rows'];
                else $rows = [];
                if(!count($rows)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki Baris", "status" => 400];
            }
            $details = (object)[
                "is_general" => $is_general,
                "rows" => $rows ?? [],
                "columns" => $columns
            ];
        } else if($type === 5){
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki List Numeral", "status" => 400];
            $index_number = 1;
            foreach($lists as $list){
                if(!isset($list['type'])) return ["success" => false, "message" => "$pekerjaan Dengan List $index_number Belum Memiliki Tipe", "status" => 400];
                if(!isset($list['description'])) return ["success" => false, "message" => "$pekerjaan Dengan List $index_number Belum Memiliki Keterangan", "status" => 400];
                $index_number++;
            }
            $details = (object)["lists" => $lists];
        } else if($type === 6){
            if(!isset($work['dropdown_name'])) return ["success" => false, "message" => "$pekerjaan Memiliki Nama Dropdown", "status" => 400];
            if(isset($work['lists'])) $lists = $work['lists'];
            else $lists = [];
            if(!count($lists)) return ["success" => false, "message" => "$pekerjaan Belum Memiliki List Dropdown", "status" => 400];
            $details = (object)["lists" => $lists, "dropdown_name" => $work['dropdown_name']];
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
            return ["success" => true, "message" => "Tipe Task Berhasil Dibuat", "id" => $task->id, "status" => 200];
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