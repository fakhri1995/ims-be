<?php

namespace App\Console\Commands;

use App\AccessFeature;
use App\Announcement;
use App\Role;
use App\Services\CompanyService;
use App\Services\GlobalService;
use App\Services\NotificationService;
use App\Services\UserService;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendAnnounceNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-announce-push-notif';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $globalService;
    private $agent_role_id;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->globalService = new GlobalService;
        $this->agent_role_id = $this->globalService->agent_role_id;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $list = Announcement::query()->where('push_notif', false)->limit(5)->get();

        foreach ($list as $data) {
            try {
                DB::beginTransaction();
                $description = $data->title;
                $link = env('APP_URL_WEB') . "/dashboard/announcement/detail/" . $data->id;
                $image_type = "announcement";
                $color_type = "blue";
                $need_push_notification = true;
                $notificationable_id = $data->id;
                $notificationable_type = 'App\Announcement';
                $users = $this->getUserList($this->agent_role_id, 'ANNOUNCEMENT_EMPLOYEE_GET');
                $data->push_notif = true;
                $data->save();

                $this->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, $data->user_id);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                // throw $th;
            }
        }
    }

    private function getUserList($role_id, $route_name)
    {
        $users = User::query()
            ->select('users.id')
            ->whereHas('roles', function ($q) use ($route_name) {
                $q->where(function ($q1) use ($route_name) {
                    $q1->where('name', 'Super Admin')
                        ->orWhereHas('features', function ($q2) use ($route_name) {
                            $q2->where('name', $route_name);
                        });
                });
            })
            ->where('users.role', $role_id);

        return $users->pluck('id');
    }

    private function addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, $created_by)
    {
        $notification_service = new NotificationService;
        $notification_service->addNotification($description, $link, $image_type, $color_type, $need_push_notification, $notificationable_id, $notificationable_type, $users, $created_by);
    }
}
