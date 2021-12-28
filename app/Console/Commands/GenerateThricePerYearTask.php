<?php

namespace App\Console\Commands;

use App\Services\TaskGeneratorService;
use Illuminate\Console\Command;

class GenerateThricePerYearTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate-thrice-year';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thrice per year task';

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
        $task_generator_service->generateTasks(7);    
    }
}