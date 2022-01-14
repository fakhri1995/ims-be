<?php

namespace App\Console\Commands;

use App\Task;
use Illuminate\Console\Command;

class UnhideTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:unhide';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unhide tasks for passed created at time tasks';

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
        $task_unhide_count = Task::where('is_visible', false)->where('created_at', '<', date('Y-m-d H:i:s'))->update(['is_visible' => true]);
    }
}
