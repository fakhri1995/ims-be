<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->notificationService = new NotificationService;
    }

    public function getNotifications(Request $request)
    {
        $route_name = "NOTIFICATIONS_GET";

        $response = $this->notificationService->getNotifications($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getNotification(Request $request)
    {
        $route_name = "NOTIFICATION_GET";

        $response = $this->notificationService->getNotification($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function readNotification(Request $request)
    {
        $route_name = "NOTIFICATION_READ";

        $response = $this->notificationService->readNotification($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function readAllNotifications(Request $request)
    {
        $route_name = "NOTIFICATIONS_READ";

        $response = $this->notificationService->readAllNotifications($request, $route_name);
        return response()->json($response, $response['status']);
    }
}