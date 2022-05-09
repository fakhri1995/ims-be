<?php

namespace App\Console;

use App\Console\Commands\UnhideTasks;
use App\Console\Commands\SetOverdueTasks;
use App\Console\Commands\SearchGeoLocation;
use App\Console\Commands\AutoCheckOutAttendance;
use App\Console\Commands\GenerateDailyTask;
use App\Console\Commands\GenerateWeeklyTask;
use App\Console\Commands\GenerateMonthlyTask;
use App\Console\Commands\GenerateThricePerYearTask;
use App\Console\Commands\GenerateTwicePerMonthTask;
use App\Console\Commands\GenerateFourTimesPerYearTask;
use App\Console\Commands\GenerateOneHourLeftTaskNotification;
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
        SearchGeoLocation::class,
        GenerateDailyTask::class,
        GenerateWeeklyTask::class,
        GenerateTwicePerMonthTask::class,
        GenerateMonthlyTask::class,
        GenerateThricePerYearTask::class,
        GenerateFourTimesPerYearTask::class,
        GenerateOneHourLeftTaskNotification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(UnhideTasks::class)->cron('* * * * *')->runInBackground();
        $schedule->command(SetOverdueTasks::class)->cron('* * * * *')->runInBackground();
        $schedule->command(SearchGeoLocation::class)->cron('* * * * *')->runInBackground();
        $schedule->command(GenerateOneHourLeftTaskNotification::class)->cron('* * * * *')->runInBackground();
        $schedule->command(AutoCheckOutAttendance::class)->cron('59 23 * * *')->runInBackground();
        $schedule->command(GenerateDailyTask::class)->cron('0 0 * * *')->runInBackground();	
        $schedule->command(GenerateWeeklyTask::class)->cron('0 0 * * 1')->runInBackground();
        $schedule->command(GenerateTwicePerMonthTask::class)->cron('0 0 1,15 * *')->runInBackground();	
        $schedule->command(GenerateMonthlyTask::class)->cron('0 0 1 * *')->runInBackground();
        $schedule->command(GenerateThricePerYearTask::class)->cron('0 0 1 */4 *')->runInBackground();
        $schedule->command(GenerateFourTimesPerYearTask::class)->cron('0 0 1 */3 *')->runInBackground();
    }
}