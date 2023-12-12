<?php

namespace App\Console\Commands\Exclusive;

use App\ResumeEducation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetStartEndDateFromGraduationYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exclusive:set-start-end-date-from-graduation-year';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $list = ResumeEducation::query()->whereNull('end_date')->get();

        foreach ($list as $data) {
            if ($data->graduation_year) {
                $data->end_date = date('Y-m-t', strtotime($data->graduation_year));
                $data->save();
            }
        }
    }
}
