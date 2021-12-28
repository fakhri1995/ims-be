<?php

namespace App\Console\Commands;

use App\Task;
use Illuminate\Console\Command;

class SetOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:set-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set status into overdue for passed deadline tasks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $task_new_overdue_count = Task::whereIn('status', [2,3])->where('deadline', '<', date('Y-m-d H:i:s'))->update(['status' => 1]);
    }
}
