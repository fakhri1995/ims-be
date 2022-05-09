<?php

namespace App\Services;
use App\Task;
use DateTime;
use App\Group;
use App\TaskType;
use DateInterval;
use App\Inventory;
use App\TaskDetail;

class TaskGeneratorService
{
    private function add_months($months, DateTime $dateObject) 
    {
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +'.$months.' month');

        if($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P'.$months.'M');
        }
    }

    private function endCycle($d1, $months)
    {
        $date = new DateTime($d1);

        // call second function to add the months
        $newDate = $date->add($this->add_months($months, $date));

        // goes back 1 day from date, remove if you want same day of month
        // $newDate->sub(new DateInterval('P1D')); 

        //formats final date to Y-m-d form
        $dateReturned = $newDate->format('Y-m-d'); 

        return $dateReturned;
    }

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

    private function newDeadline($last_deadline, $last_created_at, $type)
    {
        if($type === 2){
            $new_created_at = date('Y-m-d H:i:s', strtotime($last_created_at . ' +1 day'));
            $new_deadline = date('Y-m-d H:i:s', strtotime($last_deadline . ' +1 day'));
        } else if($type === 3){
            $new_created_at = date('Y-m-d H:i:s', strtotime($last_created_at . ' +7 day'));
            $new_deadline = date('Y-m-d H:i:s', strtotime($last_deadline . ' +7 day'));
        }  else if($type === 4){
            $new_created_at = date('Y-m-d H:i:s', strtotime($last_created_at . ' +14 day'));
            $new_deadline = date('Y-m-d H:i:s', strtotime($last_deadline . ' +14 day'));
        }  else if($type === 5){
            $temp_date = explode(" ", $last_created_at);
            $last_date = date('Y-m-d', strtotime($last_created_at)); 
            $final_date = $this->endCycle($last_date, 1); 
            $new_created_at = $final_date." ".$temp_date[1];

            $temp_date = explode(" ", $last_deadline);
            $last_date = date('Y-m-d', strtotime($last_deadline)); 
            $final_date = $this->endCycle($last_date, 1); 
            $new_deadline = $final_date." ".$temp_date[1];
        }  else if($type === 6){
            $temp_date = explode(" ", $last_created_at);
            $last_date = date('Y-m-d', strtotime($last_created_at)); 
            $final_date = $this->endCycle($last_date, 3); 
            $new_created_at = $final_date." ".$temp_date[1];
            
            $temp_date = explode(" ", $last_deadline);
            $last_date = date('Y-m-d', strtotime($last_deadline)); 
            $final_date = $this->endCycle($last_date, 3); 
            $new_deadline = $final_date." ".$temp_date[1];
        }  else if($type === 7){
            $temp_date = explode(" ", $last_created_at);
            $last_date = date('Y-m-d', strtotime($last_created_at)); 
            $final_date = $this->endCycle($last_date, 4); 
            $new_created_at = $final_date." ".$temp_date[1];

            $temp_date = explode(" ", $last_deadline);
            $last_date = date('Y-m-d', strtotime($last_deadline)); 
            $final_date = $this->endCycle($last_date, 4); 
            $new_deadline = $final_date." ".$temp_date[1];
        } 
        return ["created_at" => $new_created_at, "deadline" => $new_deadline];
    }
    
    private function generateTask($previous_task, $times, $current_timestamp)
    {
        $task_type = TaskType::with('works')->withTrashed()->find($previous_task->task_type_id);
        if($task_type === null) return ["success" => false, "message" => "Id Tipe Task Tidak Ditemukan", "status" => 400];
        $task = new Task;
        if($previous_task->group_id){
            $task->group_id = $previous_task->group_id;
            $group = Group::with('users')->find($previous_task->group_id);
        } else $task->group_id = null;
        
        if($times['deadline'] > $previous_task->end_repeat_at) $repeat = 0;
        else $repeat = $previous_task->repeat;

        $task->name = $previous_task->name;
        $task->description = $previous_task->description;
        $task->task_type_id = $previous_task->task_type_id;
        $task->location_id = $previous_task->location_id;
        $task->reference_id = $previous_task->reference_id;
        $task->created_by = $previous_task->created_by;
        $task->deadline = $times['deadline'];
        $task->first_deadline = $times['deadline'];
        $task->created_at = $times['created_at'];
        $task->is_replaceable = $previous_task->is_replaceable;
        $task->is_uploadable = $previous_task->is_uploadable;
        $task->end_repeat_at = $previous_task->end_repeat_at;
        $task->repeat = $repeat;
        $task->is_from_ticket = false;
        $task->files = [];
        $task->is_visible = false;
        $task->status = 2;
        $task->save();
        
        $previous_task->repeat = 0;
        $previous_task->save();
        
        $inventory_ids = $previous_task->inventories->pluck('id');
        if(count($task_type->works)){
            $new_works = $this->clusteringNewTaskWorks($task_type->works, $inventory_ids);
            $task->taskDetails()->saveMany($new_works);
        }
        
        if($previous_task->group_id){
            $task->users()->attach($group->users->pluck('id'));
        } else{
            $task->users()->attach($previous_task->users->pluck('id'));
            if(count($previous_task->users) === 1){
                foreach($task->taskDetails as $taskDetail){
                    $taskDetail->users()->attach($assign_ids);
                }
            }
        } 

        if(count($inventory_ids)){
            $attach_inventories = [];
            foreach($inventory_ids as $inventory_id){
                $attach_inventories[$inventory_id] = ['is_from_task' => true];
            }
            $task->inventories()->attach($attach_inventories);
        }
    }

    public function generateTasks($type)
    {
        $current_timestamp = date('Y-m-d H:i:s');
        $periodic_needed_generated_tasks = Task::with(['users', 'inventories' => function ($query) {
            $query->wherePivot('is_from_task', true);
        }])->where('repeat', $type)->get();

        foreach($periodic_needed_generated_tasks as $periodic_needed_generated_task){
            $times = $this->newDeadline($periodic_needed_generated_task->first_deadline, $periodic_needed_generated_task->created_at, $type);
            $this->generateTask($periodic_needed_generated_task, $times, $current_timestamp);
        }
    }

    public function generateOneHourLeftTaskNotification()
    {
        $description = "Task akan mencapai waktu deadline pada satu jam dari sekarang"; 
        $image_type = "exclamation"; 
        $color_type = "red"; 
        $need_push_notification = true;
        $notificationable_type = 'App\Task';
        $default_link = env('APP_URL_WEB')."/task/";

        $next_one_hour = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $tasks = Task::with('users:id')->select('id', 'need_one_hour_notification', 'deadline')->where('need_one_hour_notification', true)->where('deadline', '<', $next_one_hour)->get();
        if(count($tasks)){
            $notification_service = new NotificationService;
            foreach($tasks as $task){
                $users = $task->users->pluck('id')->toArray();
                // $users_without_creator = array_values(array_diff($users, [$task->created_by]));
                $notificationable_id = $task->id;
                $link = $default_link.$notificationable_id;
                $notification_service->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, 0);
                $task->need_one_hour_notification = false;
                $task->save();
            }
        }
    }

    public function unhideTasks()
    {
        $tasks = Task::select('id', 'is_visible', 'created_at', 'created_by')->where('is_visible', false)->where('created_at', '<', date('Y-m-d H:i:s'))->get();        
        $description = "Task Baru Telah Terbuat"; 
        $color_type = "green";  
        $image_type = "task"; 
        $need_push_notification = false;
        $notificationable_type = 'App\Task';
        $default_link = env('APP_URL_WEB')."/task/";
        if(count($tasks)){
            $notification_service = new NotificationService;
            foreach($tasks as $task){
                $task->is_visible = true;
                $task->save();
                $users = $task->users->pluck('id')->toArray();
                $users[] = $task->created_by;
                $notificationable_id = $task->id;
                $link = $default_link.$notificationable_id;
                $notification_service->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, $task->created_by);
            }
        }
    }
}