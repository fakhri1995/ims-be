<?php

namespace App\Services;

use App\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScheduleService
{
    private $globalService;
    private $userService;
    private $agent_role_id;
    private $requester_role_id;
    private $guest_role_id;
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->userService = new UserService;
        $this->agent_role_id = $this->globalService->agent_role_id;
        $this->requester_role_id = $this->globalService->requester_role_id;
        $this->guest_role_id = $this->globalService->guest_role_id;
    }

    function getSchedules($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            'start_at' => 'required|date_format:Y-m-d',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $keyword = $request->keyword;
        $company_id = $request->company_id;
        $position = $request->position;
        $start_at = $request->start_at;
        $end_at = date('Y-m-d', strtotime($request->start_at . ' + 7 days'));

        try {
            $users = $this->userService->getUserList($request, $this->agent_role_id, true);
            $users = $users->with(['schedule' => function ($q) use ($start_at, $end_at) {
                $q->whereBetween('date', [$start_at, $end_at]);
            }])->when($company_id, function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })->when($position, function ($q) use ($position) {
                $q->where('position', $position);
            })->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($q1) use($keyword) {
                    $q1->orWhere('position', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhereHas('company', function ($q2) use($keyword) {
                            $q2->leftJoin('companies as top_parent', 'companies.parent_id', 'top_parent.id')
                                ->where(DB::raw("CONCAT(IFNULL(top_parent.name, ''), '-', companies.name)"), 'like', '%' . $keyword . '%');
                        });
                });
            });

            $users = $users->paginate($request->rows);
            foreach ($users as $user) {
                $user->company_name = $user->company->topParent ? $user->company->topParent->name . ' - ' . $user->company->name : $user->company->name;
                // $user->makeHidden(['company']);
            }

            return ["success" => true, "message" => "Daftar Berhasil Diambil", "data" => $users, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getSchedule($request, $route_name): array
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
            $schedule = Schedule::query()->with(['user.company', 'shift'])->find($request->id);
            if (!$schedule) {
                return ["success" => false, "message" => "Data Schedule tidak ditemukan", "status" => 404];
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $schedule, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addSchedule($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            'user_ids' => 'array|required',
            'user_ids.*' => 'numeric|exists:users,id',
            'shift_id' => 'required|numeric|exists:shifts,id',
            'date' => 'required|date_format:Y-m-d',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $exists = Schedule::query()
                ->whereIn('user_id', $request->user_ids)
                ->where('date', $request->date)
                ->with('user')
                ->first();
            if ($exists) return ["success" => false, "message" => 'Terdapat User yang sudah mempunyai jadwal', "data" => $exists, "status" => 400];
            foreach ($request->user_ids as $user_id) {
                $schedule = new Schedule();
                $schedule->user_id = $user_id;
                $schedule->shift_id = $request->shift_id;
                $schedule->date = $request->date;
                $schedule->save();
            }
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (\Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function updateSchedule($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
            'shift_id' => 'required|numeric|exists:shifts,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $schedule = Schedule::query()->with(['user'])->find($request->id);
            if (!$schedule) {
                return ["success" => false, "message" => "Data Schedule tidak ditemukan", "status" => 404];
            }
            $schedule->shift_id = $schedule->shift_id;
            $schedule->save();
            return ["success" => true, "message" => "Data Berhasil Diperbarui", "data" => $schedule, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function deleteSchedule($request, $route_name): array
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
            $schedule = Schedule::query()->with(['user'])->find($request->id);
            if (!$schedule) {
                return ["success" => false, "message" => "Data Schedule tidak ditemukan", "status" => 404];
            }
            $schedule->delete();

            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $schedule, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}
