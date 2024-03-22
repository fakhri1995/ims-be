<?php

namespace App\Console;

use App\Console\Commands\SendAndroidPushNotifications;
use App\Console\Commands\UnhideTasks;
use App\Console\Commands\SetOverdueTasks;
use App\Console\Commands\SearchGeoLocation;
use App\Console\Commands\AutoCheckOutAttendance;
use App\Console\Commands\Exclusive\SetContractHistory;
use App\Console\Commands\Exclusive\SetDisplayOrderResume;
use App\Console\Commands\Exclusive\SetEncryptionEmployee;
use App\Console\Commands\Exclusive\SetGetMostCommit;
use App\Console\Commands\Exclusive\SetStartEndDateFromGraduationYear;
use App\Console\Commands\GenerateDailyTask;
use App\Console\Commands\GenerateWeeklyTask;
use App\Console\Commands\GenerateMonthlyTask;
use App\Console\Commands\GenerateThricePerYearTask;
use App\Console\Commands\GenerateTwicePerMonthTask;
use App\Console\Commands\GenerateFourTimesPerYearTask;
use App\Console\Commands\GenerateOneHourLeftTaskNotification;
use App\Console\Commands\GenerateScheduleAttendance;
use App\Console\Commands\RaiseLastPeriodPayslip;
use App\Console\Commands\SendAndroidNotificationActivity;
use App\Console\Commands\SendAndroidNotificationCheckIn;
use App\Console\Commands\SendAnnounceNotification;
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
        SendAndroidNotificationCheckIn::class,
        SendAndroidNotificationActivity::class,
        SendAndroidPushNotifications::class,
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
        GenerateOneHourLeftTaskNotification::class,
        RaiseLastPeriodPayslip::class,
        SetContractHistory::class,
        SetDisplayOrderResume::class,
        SetEncryptionEmployee::class,
        SetStartEndDateFromGraduationYear::class,
        SetGetMostCommit::class,
        GenerateScheduleAttendance::class,
        SendAnnounceNotification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command(SendAndroidPushNotifications::class)->cron('* * * * *')->runInBackground();
        $schedule->command(SendAndroidNotificationCheckIn::class)->cron('0 8 * * *')->runInBackground();
        $schedule->command(SendAndroidNotificationActivity::class)->cron('0 13 * * *')->runInBackground();
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
        $schedule->command(RaiseLastPeriodPayslip::class)->cron('35 0 1 * *')->runInBackground();
        $schedule->command(SendAnnounceNotification::class)->cron('* * * * *')->runInBackground();

        $schedule->command('task:generate-schedules-attendance 1')->cron('*/15 1-2 * * *')->runInBackground();
        $schedule->command('task:generate-schedules-attendance 2')->cron('*/15 1-2 * * *')->runInBackground();
        $schedule->command('task:generate-schedules-attendance 3')->cron('*/15 1-2 * * *')->runInBackground();
        $schedule->command('task:generate-schedules-attendance 4')->cron('*/15 1-2 * * *')->runInBackground();
        $schedule->command('task:generate-schedules-attendance 5')->cron('*/15 1-2 * * *')->runInBackground();

        $schedule->command('exclusive:set-display-order-resume 1')->cron('0 * * * *')->runInBackground();
        $schedule->command('exclusive:set-display-order-resume 2')->cron('0 * * * *')->runInBackground();
        $schedule->command('exclusive:set-display-order-resume 3')->cron('0 * * * *')->runInBackground();
        $schedule->command('exclusive:set-display-order-resume 4')->cron('0 * * * *')->runInBackground();
        $schedule->command('exclusive:set-display-order-resume 5')->cron('0 * * * *')->runInBackground();
    }
}
