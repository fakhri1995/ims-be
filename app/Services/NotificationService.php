<?php

namespace App\Services;
use Exception;
use App\Notification;
// use App\Services\GlobalService;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct()
    {
        // $this->globalService = new GlobalService;
    }

    public function getNotification($request, $route_name)
    {
        // $access = $this->globalService->checkRoute($route_name);
        // if($access["success"] === false) return $access;
        
        $login_id = auth()->user()->id;
        $notifications = DB::table('notification_user')->where('user_id', $login_id)
        // ->select('id', 'description', 'is_read')
        ->join('notifications', 'notifications.id', '=', 'notification_user.notification_id')
        ->orderBy('notifications.created_at', 'desc')
        ->limit(10)->get();
        if($notifications->isEmpty()) return ["success" => true, "message" => "Notifikasi Masih Kosong", "data" => $notifications, "status" => 200];
        return ["success" => true, "message" => "Notifikasi Berhasil Diambil", "data" => $notifications, "status" => 200];
    }

    public function getNotifications($request, $route_name)
    {
        // $access = $this->globalService->checkRoute($route_name);
        // if($access["success"] === false) return $access;

        $rows = $request->get('rows', 10);

        if($rows > 100) $rows = 100;
        if($rows < 1) $rows = 10;
        $params = "?rows=$rows";
        
        $login_id = auth()->user()->id;
        $notifications = DB::table('notification_user')->where('user_id', $login_id)
        ->join('notifications', 'notifications.id', '=', 'notification_user.notification_id')
        ->orderBy('notifications.created_at', 'desc')
        ->paginate($rows); 
        $notifications->withPath(env('APP_URL').'/getNotifications'.$params);
        if($notifications->isEmpty()) return ["success" => true, "message" => "Notifikasi Masih Kosong", "data" => $notifications, "status" => 200];
        return ["success" => true, "message" => "Notifikasi Berhasil Diambil", "data" => $notifications, "status" => 200];
    }

    public function readNotification($request, $route_name)
    {
        // $access = $this->globalService->checkRoute($route_name);
        // if($access["success"] === false) return $access;
        
        $login_id = auth()->user()->id;
        $id = $request->get('id');
        $notifications = DB::table('notification_user')->where('notification_id', $id)->where('user_id', $login_id)->update(['is_read' => DB::raw( true )]);
        if(!$notifications) return ["success" => false, "message" => "Notifikasi Tidak Ditemukan", "status" => 400];
        return ["success" => true, "message" => "Notifikasi Berhasil Dibaca", "status" => 200];
    }

    public function readAllNotifications($request, $route_name)
    {
        // $access = $this->globalService->checkRoute($route_name);
        // if($access["success"] === false) return $access;
        
        $login_id = auth()->user()->id;
        $notifications = DB::table('notification_user')->where('user_id', $login_id)->where('is_read', false)->update(['is_read' => DB::raw( true )]);
        if(!$notifications) return ["success" => false, "message" => "Notifikasi Tidak Ditemukan", "status" => 400];
        return ["success" => true, "message" => "Notifikasi Berhasil Dibaca", "status" => 200];
    }

    public function addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, $created_by)
    {
        try{
            $new_notification = new Notification;
            $new_notification->created_at = date("Y-m-d H:i:s");
            $new_notification->created_by = $created_by;
            $new_notification->description = $description;
            $new_notification->link = $link;
            $new_notification->image_type = $image_type;
            $new_notification->color_type = $color_type;
            $new_notification->need_push_notification = $need_push_notification;
            $new_notification->notificationable_id = $notificationable_id;
            $new_notification->notificationable_type = $notificationable_type;
            $new_notification->save();
            $new_notification->users()->sync($users);
            return ["success" => true, "id" => $new_notification->id];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateNotification($notificationable_id, $notificationable_type, $users)
    {
        try{
            $notification = Notification::where('notificationable_id', $notificationable_id)->where('notificationable_type', $notificationable_type)->first();
            if($notification){
                $notification->users()->sync($users);
                return ["success" => true, "id" => $notification->id];
            } else {
                return ["success" => false, "id" => 0];
            }
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // public function generateOneHourLeftTask()
    // {
    //     $description = "Task akan mencapai waktu deadline pada satu jam dari sekarang"; 
    //     $image_type = "exclamation"; 
    //     $color_type = "red"; 
    //     $need_push_notification = true;
    //     $notificationable_type = 'App\Task';
    //     $default_link = env('APP_URL_WEB')."/task/";

    //     $notifications = Notification::with('users:id')->select('id', 'created_by', 'notificationable_id')->where('need_one_hour_notification', true)->get();
    //     if(count($notifications)){
    //         foreach($notifications as $notification){
    //             $users = $notification->users->pluck('id')->toArray();
    //             $users_without_creator = array_values(array_diff($users, [$notification->created_by]));
    //             $notificationable_id = $notification->notificationable_id;
    //             $link = $default_link.$notificationable_id;
    //             $this->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users_without_creator, 0);
    //             $notification->need_one_hour_notification = false;
    //             $notification->save();
    //         }
    //     }
    // }
}



