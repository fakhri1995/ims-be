<?php

namespace App\Console\Commands;

use App\Notification;
use GuzzleHttp\Client;
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

        $this->client = new Client();
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
            $this->sendPushNotification($tokens, $notification_template);
            $notification->need_push_notification = false;
            $notification->save();
        }
    }

    private function sendPushNotification($registrations_ids, $notification)
    {
        $headers = [
            'Authorization' => 'key ='.env('KEY_ANDROID_FIREBASE'),
            'content-type' => 'application/json'
        ];

        $body = [
            'registration_ids' => $registrations_ids,
            'notification' => $notification
        ];

        $response = $this->client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
            'headers'  => $headers,
            'json' => $body
        ]);
    }
}
