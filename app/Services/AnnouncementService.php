<?php

namespace App\Services;

use App\Announcement;
use App\AnnouncementMail;
use App\AnnouncementMailGroup;
use App\AnnouncementMailStaff;
use App\Group;
use App\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AnnouncementService
{
    protected $globalService;

    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    function getAnnouncements($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $keyword = $request->keyword;
        $status = $request->status;
        $orderBy = $request->order_by;
        $orderTo = $request->order_to;

        if (!$orderBy) {
            $orderBy = 'id';
        }
        if (!$orderTo) {
            'desc';
        }
        try {
            $data = Announcement::query()
                ->with(['user', 'thumbnailImage'])
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where(DB::raw("CONCAT(
                        title,'-',
                        text)"), 'like', '%' . $keyword . '%')
                        ->orWhereHas('user', function ($q1) use ($keyword) {
                            $q1->where('name', 'like', '%' . $keyword . '%');
                        });
                })
                ->when($status == 'published', function ($q) {
                    $q->where('publish_at', '<=', date('Y-m-d H:i:s'));
                })
                ->orderBy($orderBy, $orderTo)
                ->paginate($request->rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch (Throwable $err) {
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    function getAnnouncement($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $data = Announcement::query()->with(['thumbnailImage', 'user'])->find($request->id);
            if (!$data) {
                return ["success" => false, "message" => "Data Announcement tidak ditemukan", "status" => 404];
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addAnnouncement($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            'title' => 'required',
            'text' => 'required',
            'publish_type' => 'in:now,pending',
            'publish_at' => 'required_if:status,pending|date_format:Y-m-d H:i:s',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            DB::beginTransaction();
            $data = new Announcement();
            $data->title = $request->title;
            $data->text = $request->text;
            if ($request->publish_type == 'now') {
                $request->publish_at = date('Y-m-d H:i:s');
            }
            $data->publish_at = $request->publish_at;
            $data->user_id = auth()->user()->id;
            $data->save();

            if (method_exists($request, 'hasFile') && $request->hasFile('thumbnail_image')) {
                $fileService = new FileService;
                $file = $request->file('thumbnail_image');
                $table = 'App\Announcement';
                $description = 'thumbnail_image';
                $folder_detail = 'Announcement';

                $fileService->addFile($data->id, $file, $table, $description, $folder_detail);
            }

            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (Throwable $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    function updateAnnouncement($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
            'title' => 'required',
            'text' => 'required',
            'publish_type' => 'in:now,pending',
            'publish_at' => 'required_if:status,pending|date_format:Y-m-d H:i:s',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $data = Announcement::query()->with(['thumbnailImage'])->find($request->id);
        if (!$data) {
            return ["success" => false, "message" => "Data Announcement tidak ditemukan", "status" => 404];
        }
        try {
            DB::beginTransaction();
            $data->title = $request->title;
            $data->text = $request->text;
            if ($request->publish_type == 'now') {
                $request->publish_at = date('Y-m-d H:i:s');
            }
            $data->publish_at = $request->publish_at;
            $data->user_id = auth()->user()->id;
            $data->save();

            if ($request->hasFile('thumbnail_image')) {
                $fileService = new FileService;
                $file = $request->file('thumbnail_image');
                $table = 'App\Announcement';
                $description = 'thumbnail_image';
                $folder_detail = 'Announcement';
                if ($data->thumbnailImage->id) {
                    $del = $fileService->deleteForceFile($data->thumbnailImage->id);
                }
                $add = $fileService->addFile($data->id, $file, $table, $description, $folder_detail);
            }

            if (isset($request->thumbnail_image)) {
                if (empty($request->thumbnail_image) && $data->thumbnailImage->id) {
                    $fileService = new FileService;
                    $del = $fileService->deleteForceFile($data->thumbnailImage->id);
                }
            }
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Perbarui", "status" => 200];
        } catch (Throwable $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    function deleteAnnouncement($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $data = Announcement::query()->with(['thumbnailImage'])->find($request->id);
        if (!$data) {
            return ["success" => false, "message" => "Data Announcement tidak ditemukan", "status" => 404];
        }
        try {
            DB::beginTransaction();
            if ($data->thumbnailImage->id) {
                $fileService = new FileService;
                $del = $fileService->deleteForceFile($data->thumbnailImage->id);
            }
            $this->removeNotification($data->id);
            $data->delete();
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $data, "status" => 200];
        } catch (\Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // FOR EMPLOYEE ============================

    function getAnnouncementEmployee($request, $route_name): array
    {
        // $access = $this->globalService->checkRoute($route_name);
        // if ($access["success"] === false) return $access;
        try {
            $data = Announcement::query()
                ->with(['user', 'thumbnailImage'])
                ->where('publish_at', '<=', date('Y-m-d H:i:s'))
                ->orderBy('publish_at', 'desc')
                ->limit(3)
                ->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getAnnouncementMore($request, $route_name): array
    {
        // $access = $this->globalService->checkRoute($route_name);
        // if ($access["success"] === false) return $access;
        $rules = [
            "current_id" => "numeric|required",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $data = Announcement::query()
                ->with(['user', 'thumbnailImage'])
                ->where('publish_at', '<=', date('Y-m-d H:i:s'))
                ->where('id', '!=', $request->current_id)
                ->orderBy('publish_at', 'desc')
                ->limit(2)
                ->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function sendMailAnnouncement($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
            'purpose_type' => 'in:staff,group',
            'purpose_ids' => 'array|required',
            'purpose_ids.*' => 'numeric',
            'publish_type' => 'in:now,pending',
            'publish_at' => 'required_if:status,pending|date_format:Y-m-d H:i',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $data = Announcement::find($request->id);
        if (!$data) {
            return ["success" => false, "message" => "Data Announcement tidak ditemukan", "status" => 404];
        }
        try {
            DB::beginTransaction();
            $announce_mail = new AnnouncementMail();
            $announce_mail->announcement_id = $data->id;
            $announce_mail->publish_at = $request->publish_type == 'pending' ? date('Y-m-d H:i:00', strtotime($request->publish_at)) : date('Y-m-d H:i:00');
            $announce_mail->save();
            if ($request->purpose_type == 'staf') {
                foreach ($request->purpose_ids as $purpose) {
                    $announce_mail_staff = new AnnouncementMailStaff();
                    $announce_mail_staff->announcement_mail_id = $announce_mail->id;
                    $announce_mail_staff->user_id = $purpose;
                    $announce_mail_staff->save();
                }
            } else if ($request->purpose_type == 'group') {
                foreach ($request->purpose_ids as $purpose) {
                    $announce_mail_staff = new AnnouncementMailGroup();
                    $announce_mail_staff->announcement_mail_id = $announce_mail->id;
                    $announce_mail_staff->group_id = $purpose;
                    $announce_mail_staff->save();
                }
            }
            DB::commit();
            return ["success" => true, "message" => "Proses Berhasil", "data" => null, "status" => 200];
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return ["success" => false, "message" => $th->getMessage(), "status" => 400];
        }
    }

    function getMailAnnouncement($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $data = AnnouncementMail::query()
            ->with(['result'])
            ->where('announcement_id', $request->id)
            ->paginate($request->rows ?? 10);

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
    }

    private function removeNotification($notificationable_id)
    {
        $notification = Notification::where('notificationable_id', $notificationable_id)->first();
        $notification->users()->detach();
        $notification->delete();
    }
}
