<?php

namespace App\Console\Commands;

use App\AttendanceUser;
use Illuminate\Console\Command;

class AutoCheckOutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically checking out have not checked out users';

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
        $current_timestamp = date("Y-m-d H:i:s");
        $user_attendance = AttendanceUser::whereNull('check_out')->update(['checked_out_by_system' => true, 'check_out' => $current_timestamp]); 
    }
}
