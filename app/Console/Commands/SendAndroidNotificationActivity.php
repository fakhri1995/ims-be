<?php

namespace App\Console\Commands;

use App\AttendanceUser;
use App\AttendanceActivity;
use App\Services\AndroidService;
use App\User;
use Illuminate\Console\Command;

class SendAndroidNotificationActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-android-notif-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send android push notification using firebase cloud messaging every 1 pm for checked in agents with no activities filled ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->androidService = new AndroidService();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->SendAndroidNotificationActivity();
    }

    public function SendAndroidNotificationActivity()
    {
        $users = User::with('androidTokens')->get();
        $tokens = [];
         foreach($users as $user){
            if(count($user->androidTokens) > 0){
                $user_attendance = AttendanceUser::where('user_id', $user->id)->whereDate('check_in', '=', date("Y-m-d"))->first();
                $today_attendance_activities = AttendanceActivity::where('user_id', $user->id)->whereDate('updated_at', '=', date("Y-m-d"))->get();
                if($user_attendance && count($today_attendance_activities) == 0) {
                    foreach($user->androidTokens as $androidToken){
                        array_push($tokens, $androidToken->token);
                    }
                }
            }
         }  
        $notification_template = [
            'title' => "Reminder: Isi Daftar Aktivitas",
            'body' => "Sudah setengah hari berjalan. Jangan lupa isi daftar pekerjaan untuk track progress harian"
        ];
        if(count($tokens) > 0){
            $this->androidService->sendPushNotification($tokens, $notification_template);
        }
    }
}
