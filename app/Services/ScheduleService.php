<?php

namespace App\Services;

use App\RepeatScheduler;
use App\Schedule;
use Carbon\Carbon;
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
                $q->with(['shift'])
                    ->whereBetween('date', [$start_at, $end_at]);
            }])->when($company_id, function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })->when($position, function ($q) use ($position) {
                $q->where('position', $position);
            })->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($q1) use ($keyword) {
                    $q1->orWhere('position', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhereHas('company', function ($q2) use ($keyword) {
                            $q2->leftJoin('companies as top_parent', 'companies.parent_id', 'top_parent.id')
                                ->where(DB::raw("CONCAT(IFNULL(top_parent.name, ''), '-', companies.name)"), 'like', '%' . $keyword . '%');
                        });
                });
            })->where('is_enabled', 0);

            $users = $users->paginate($request->rows);
            foreach ($users as $user) {
                $user->company_name = $user->company->topParent ? $user->company->topParent->name . ' - ' . $user->company->name : $user->company->name;
                // $user->makeHidden(['company']);
            }

            return ["success" => true, "message" => "Daftar Berhasil Diambil", "data" => $users, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
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
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
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
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'repeats' => 'nullable|array',
            'repeats.*' => 'nullable|numeric|between:0,6',
            'forever' => 'nullable|boolean',

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();

            // cek apakah user udah punya schedule selama nya
            $scheduler = RepeatScheduler::query()
                ->whereIn('user_id', $request->user_ids)
                ->when(count($request->repeats), function ($q) use ($request) {
                    $q->where('repeats', 'like', '%' . implode(', ', $request->repeats) . '%');
                })
                ->first();
            if ($scheduler) return ["success" => false, "message" => 'Terdapat User yang sudah mempunyai jadwal.', "data" => null, "status" => 400];

            $dates = [];
            // jika diulangi dengan range tanggal
            if (count($request->repeats) && ($request->start_date && $request->end_date)) {
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);

                $currentDate = $startDate->copy();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $index_day = date('w', strtotime($currentDate));
                    if (in_array($index_day, $request->repeats)) {
                        array_push($dates, date('Y-m-d', strtotime($currentDate)));
                    }
                    $currentDate->addDay();
                }
            }
            // jika di ulangi selama nya
            else if (count($request->repeats) && $request->forever) {
                // Ambil tanggal dari request
                $startDate = Carbon::createFromFormat('Y-m-d', $request->date);
                // Tambahkan 3 bulan dari start date
                $endDate = $startDate->copy()->addMonths(3);
                // Tambahkan 1 hari dari end date
                $schedulerDate = $endDate->copy()->addDay();

                $inserts = [];
                foreach ($request->user_ids as $user_id) {
                    array_push($inserts, [
                        "user_id" => $user_id,
                        "shift_id" => $request->shift_id,
                        "date" => $schedulerDate,
                        "repeats" => json_encode($request->repeats),
                        "created_at" => date('Y-m-d H:i:s')
                    ]);
                }
                DB::table('repeat_scheduler')->insert($inserts);
                unset($inserts);

                $currentDate = $startDate->copy();
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $index_day = date('w', strtotime($currentDate));
                    if (in_array($index_day, $request->repeats)) {
                        array_push($dates, date('Y-m-d', strtotime($currentDate)));
                    }
                    $currentDate->addDay();
                }
            }
            // jika tidak diulangi
            else {
                $exists = Schedule::query()
                    ->whereIn('user_id', $request->user_ids)
                    ->where('date', $request->date)
                    ->with('user')
                    ->first();
                if ($exists) return ["success" => false, "message" => 'Terdapat User yang sudah mempunyai jadwal', "data" => $exists, "status" => 400];
            }

            if (count($dates)) {
                $existsMany = Schedule::query()
                    ->whereIn('user_id', $request->user_ids)
                    ->whereIn('date', $dates)
                    ->with('user')
                    ->latest('date')
                    ->first();
                if ($existsMany) return ["success" => false, "message" => 'Terdapat User yang sudah mempunyai jadwal..', "data" => $existsMany, "status" => 400];
            }

            foreach ($request->user_ids as $user_id) {
                if (!count($dates)) {
                    $schedule = new Schedule();
                    $schedule->user_id = $user_id;
                    $schedule->shift_id = $request->shift_id;
                    $schedule->date = $request->date;
                    $schedule->save();
                } else {
                    $inserts = [];
                    foreach ($dates as $date) {
                        array_push($inserts, [
                            "user_id" => $user_id,
                            "shift_id" => $request->shift_id,
                            "date" => $date,
                            "created_at" => date('Y-m-d H:i:s')
                        ]);
                    }
                    DB::table('schedules')->insert($inserts);
                    unset($inserts);
                }
            }
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (\Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
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
            if($schedule->date < date('Y-m-d')){
                return ["success" => false, "message" => "Tidak bisa mengedit data yang sudah lewat", "status" => 404];
            }
            $schedule->shift_id = $request->shift_id;
            $schedule->save();
            return ["success" => true, "message" => "Data Berhasil Diperbarui", "data" => $schedule, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
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
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    function deleteAllSchedule($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            'user_ids' => 'array|required',
            'user_ids.*' => 'numeric|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            DB::beginTransaction();
            foreach ($request->user_ids as $item) {
                $schedules = Schedule::query()->where('user_id', $item)->get();
                foreach ($schedules as $schedule) {
                    $schedule->delete();
                }
            }
            RepeatScheduler::whereIn('user_id', $request->user_ids)->delete();
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => null, "status" => 200];
        } catch (\Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }

    function getCurrentSchedule($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "user_id" => "numeric|required",
            "date"    => "required|date_format:Y-m-d"
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $schedule = Schedule::query()->with(['shift'])
                ->where('user_id', $request->user_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->first();
            if (!$schedule) {
                return ["success" => false, "message" => "Data Schedule tidak ditemukan", "status" => 404];
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $schedule, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err->getMessage(), "status" => 400];
        }
    }
}
