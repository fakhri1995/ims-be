<?php

namespace App\Console;

use App\Console\Commands\UnhideTasks;
use App\Console\Commands\SetOverdueTasks;
use App\Console\Commands\AutoCheckOutAttendance;
use App\Console\Commands\GenerateDailyTask;
use App\Console\Commands\GenerateWeeklyTask;
use App\Console\Commands\GenerateMonthlyTask;
use App\Console\Commands\GenerateThricePerYearTask;
use App\Console\Commands\GenerateTwicePerMonthTask;
use App\Console\Commands\GenerateFourTimesPerYearTask;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        UnhideTasks::class,
        SetOverdueTasks::class,
        AutoCheckOutAttendance::class,
        GenerateDailyTask::class,
        GenerateWeeklyTask::class,
        GenerateTwicePerMonthTask::class,
        GenerateMonthlyTask::class,
        GenerateThricePerYearTask::class,
        GenerateFourTimesPerYearTask::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(UnhideTasks::class)->cron('* * * * *');
        $schedule->command(SetOverdueTasks::class)->cron('* * * * *');
        $schedule->command(AutoCheckOutAttendance::class)->cron('59 23 * * *');
        $schedule->command(GenerateDailyTask::class)->cron('0 0 * * *');	
        $schedule->command(GenerateWeeklyTask::class)->cron('0 0 * * 1');
        $schedule->command(GenerateTwicePerMonthTask::class)->cron('0 0 1,15 * *');	
        $schedule->command(GenerateMonthlyTask::class)->cron('0 0 1 * *');
        $schedule->command(GenerateThricePerYearTask::class)->cron('0 0 1 */4 *');
        $schedule->command(GenerateFourTimesPerYearTask::class)->cron('0 0 1 */3 *');
    }
}