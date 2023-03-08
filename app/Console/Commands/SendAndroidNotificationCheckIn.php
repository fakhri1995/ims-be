<?php

namespace App\Console\Commands;

use App\AttendanceUser;
use App\Services\AndroidService;
use App\User;
use Illuminate\Console\Command;

class SendAndroidNotificationCheckIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-android-notif-check-in';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send android push notification using firebase cloud messaging every 8 am for non-checked-in agents';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->androidService = new AndroidService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->SendAndroidPushNotificationCheckIn();
    }

    public function SendAndroidPushNotificationCheckIn()
    {
        $users = User::with('androidTokens')->get();
        $tokens = [];
         foreach($users as $user){
            if(count($user->androidTokens) > 0){      
                $user_attendance = AttendanceUser::where('user_id', $user->id)->orderBy('check_in', 'desc')->first();
                if($user_attendance && $user_attendance->check_out) {
                    foreach($user->androidTokens as $androidToken){
                        array_push($tokens, $androidToken->token);
                    }
                }
            }
         }  
        $notification_template = [
            'title' => "Jam 08:00. Sudah Clock-In?",
            'body' => "Jadwal kamu dimulai pukul 08:00 pas. Segera Clock-In sebelum mulai aktivitas"
        ];
        $this->androidService->sendPushNotification($tokens, $notification_template);
    }
}
