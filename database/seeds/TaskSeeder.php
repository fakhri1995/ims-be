<?php

use App\Task;
use App\TaskType;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

    private function makeBulkTaskTypes()
    {
        $name_task = "Task Type";
        for($i = 1; $i < 21; $i++){
            $task = new TaskType;
            $task->name = "$name_task $i";
            $task->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit!";
            $task->save();
        }
    }

    private function makeBulkTasks()
    {
        $name_task = "Task";
        for($i = 1; $i < 100; $i++){
            $task = new Task;
            $task->name = "$name_task $i";
            $task->description = "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo eligendi dolore aspernatur nihil at voluptates tempora neque, fuga laudantium corporis dolorum velit facilis deserunt, nobis maiores optio illum magnam cum!";
            $task->task_type_id = random_int(1,20);
            $random_int = random_int(1,30);
            $task->location_id = $random_int;
            $task->created_by = random_int(15, 23);
            $task->created_at = date("Y-m-d H:i:s");
            $task->deadline = date("Y-m-d H:i:s", strtotime("+$random_int day"));
            $task->first_deadline = $task->deadline;
            $task->status = random_int(2,6);
            $task->is_replaceable = random_int(0, 1);
            $task->is_uploadable = random_int(0, 1);
            $task->is_from_ticket = false;
            $task->is_visible = true;
            $task->save();
            if($task->is_replaceable){
                $task->users()->attach(random_int(15, 23));
                $task->users()->attach(random_int(15, 23));
            }
        }
    }

    public function run()
    {
        $this->makeBulkTasks();
        $this->makeBulkTaskTypes();
    }
}
