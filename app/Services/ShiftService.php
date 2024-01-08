<?php

namespace App\Services;

use App\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftService
{
    protected $globalService;

    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    function getShifts($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $keyword = $request->keyword;
        try {
            $shift = Shift::query()
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where(DB::raw("CONCAT(
                        title, '-', DATE_FORMAT(start_at, '%H:%i'), '-',
                        DATE_FORMAT(end_at, '%H:%i'), '-', DATE_FORMAT(start_break, '%H:%i'), '-',
                        DATE_FORMAT(end_break, '%H:%i'), '-', status)"), 'like', '%' . $keyword . '%');
                })
                ->paginate();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $shift, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getShift($request, $route_name): array
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
            $shift = Shift::query()->find($request->id);
            if (!$shift) {
                return ["success" => false, "message" => "Data Shift tidak ditemukan", "status" => 404];
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $shift, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addShift($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            'title' => 'required',
            'start_at' => 'required|date_format:H:i',
            'end_at' => 'required|date_format:H:i',
            'start_break' => 'required|date_format:H:i',
            'end_break' => 'required|date_format:H:i',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            DB::beginTransaction();
            $data = new Shift();
            $data->title = $request->title;
            $data->start_at = date('H:i:00', strtotime($request->start_at));
            $data->end_at = date('H:i:00', strtotime($request->end_at));
            $data->start_break = date('H:i:00', strtotime($request->start_break));
            $data->end_break = date('H:i:00', strtotime($request->end_break));
            $data->save();
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function updateShift($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
            'title' => 'required',
            'start_at' => 'required|date_format:H:i',
            'end_at' => 'required|date_format:H:i',
            'start_break' => 'required|date_format:H:i',
            'end_break' => 'required|date_format:H:i',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $data = Shift::query()->find($request->id);
        if (!$data) {
            return ["success" => false, "message" => "Data Shift tidak ditemukan", "status" => 404];
        }
        try {
            DB::beginTransaction();
            $data->title = $request->title;
            $data->start_at = date('H:i:00', strtotime($request->start_at));
            $data->end_at = date('H:i:59', strtotime($request->end_at));
            $data->start_break = date('H:i:00', strtotime($request->start_break));
            $data->end_break = date('H:i:59', strtotime($request->end_break));
            $data->save();
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Perbarui", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function updateShiftStatus($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "numeric|required",
            'status' => 'required|boolean',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $data = Shift::query()->find($request->id);
        if (!$data) {
            return ["success" => false, "message" => "Data Shift tidak ditemukan", "status" => 404];
        }
        try {
            DB::beginTransaction();
            $data->status = $request->status;
            $data->save();
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Diperbarui", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function deleteShift($request, $route_name): array
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
        $shift = Shift::query()->find($request->id);
        if (!$shift) {
            return ["success" => false, "message" => "Data Shift tidak ditemukan", "status" => 404];
        }
        try {
            $shift->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $shift, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}
