<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaskGeneratorService;

class GenerateOneHourLeftTaskNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:generate-one-hour-left-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate notification for task which have one hour left before deadline reached';

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
        $task_generator_service = new TaskGeneratorService;
        $task_generator_service->generateOneHourLeftTaskNotification();   
    }
}


