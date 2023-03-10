<?php

namespace App\Console\Commands;

use App\Notification;
use App\Services\AndroidService;
use Illuminate\Console\Command;

class SendAndroidPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-android-push-notif';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send android push notification using firebase cloud messaging';

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
        $this->sendAndroidPushNotifications();
    }

    private function sendAndroidPushNotifications()
    {
        $need_push_notifications = Notification::with('users:id', 'users.androidTokens')->where('need_push_notification', true)->select('id','description','need_push_notification', 'notificationable_type')->get();
        foreach($need_push_notifications as $notification){
            $tokens = [];
            foreach($notification->users as $user){
                if(count($user->androidTokens)){
                    foreach($user->androidTokens as $android_token) $tokens[] = $android_token->token;
                }
            }
            $notification_template = [
                'title' => ltrim($notification->notificationable_type, "App\\"),
                'body' => $notification->description
            ];
            $this->androidService->sendPushNotification($tokens, $notification_template);
            $notification->need_push_notification = false;
            $notification->save();
        }
    }
}
