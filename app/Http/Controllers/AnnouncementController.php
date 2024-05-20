<?php

namespace App\Http\Controllers;

use App\Services\AnnouncementService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    protected $AnnouncementService;
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->AnnouncementService = new AnnouncementService;
    }

    public function getAnnouncements(Request $request){
        $route_name = "ANNOUNCEMENTS_GET";

        $response = $this->AnnouncementService->getAnnouncements($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAnnouncement(Request $request){
        $route_name = "ANNOUNCEMENT_GET";

        $response = $this->AnnouncementService->getAnnouncement($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addAnnouncement(Request $request){
        $route_name = "ANNOUNCEMENT_ADD";

        $response = $this->AnnouncementService->addAnnouncement($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function updateAnnouncement(Request $request){
        $route_name = "ANNOUNCEMENT_UPDATE";

        $response = $this->AnnouncementService->updateAnnouncement($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteAnnouncement(Request $request){
        $route_name = "ANNOUNCEMENT_DELETE";

        $response = $this->AnnouncementService->deleteAnnouncement($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // FOR EMPLOYEE ==================

    public function getAnnouncementEmployee(Request $request){
        $route_name = "ANNOUNCEMENT_EMPLOYEE_GET";

        $response = $this->AnnouncementService->getAnnouncementEmployee($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getAnnouncementMore(Request $request){
        $route_name = "ANNOUNCEMENT_MORE_GET";

        $response = $this->AnnouncementService->getAnnouncementMore($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function sendMailAnnouncement(Request $request){
        $route_name = "ANNOUNCEMENT_MAIL_SEND";

        $response = $this->AnnouncementService->sendMailAnnouncement($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getMailAnnouncement(Request $request){
        $route_name = "ANNOUNCEMENT_MAIL_GET";

        $response = $this->AnnouncementService->getMailAnnouncement($request, $route_name);
        return response()->json($response, $response['status']);
    }
}
