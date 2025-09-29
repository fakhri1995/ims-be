<?php 

namespace App\Services;

use App\Company;
use App\PublicHoliday;
use Exception;
use App\Services\GlobalService;
use App\Workday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkdayService{
    public function __construct()
    {
        $this->globalService = new GlobalService;
    }  

    public function getWorkdayCompanies(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try {
        $rows      = $request->get('rows', 10);
        $keyword   = $request->get('keyword', null);
        $sort_by   = $request->get('sort_by', null);
        $sort_type = $request->get('sort_type', null);

        if ($rows > 100) $rows = 100;
        if ($rows < 1)   $rows = 10;

        $companies = Company::select('id', 'name')
            ->withCount('workdays')
            ->withCount('employees')
            ->has('workdays');

        $params = "?rows=$rows";
        if ($keyword)   $params .= "&keyword=$keyword";
        if ($sort_by)   $params .= "&sort_by=$sort_by";
        if ($sort_type) $params .= "&sort_type=$sort_type";

        if ($keyword) {
            $companies = $companies->where('name', 'like', "%{$keyword}%");
        }

        if ($sort_by) {
            if ($sort_type === null) $sort_type = 'desc';

            if ($sort_by === 'name') {
                $companies = $companies->orderBy('name', $sort_type);
            } else if ($sort_by === 'workdays_count') {
                $companies = $companies->orderBy('workdays_count', $sort_type);
            } else if ($sort_by === 'employees_count') {
                $companies = $companies->orderBy('employees_count', $sort_type);
            } else {
                $companies = $companies->orderBy('workdays_count', 'desc');
            }
        } else {
            $companies = $companies->orderBy('workdays_count', 'desc');
        }

        $companies = $companies->paginate($rows);
        $companies->withPath(env('APP_URL').'/getWorkdayCompanies'.$params);

        if ($companies->isEmpty()) {
            return [
                "success" => true,
                "message" => "Company list with workdays is empty",
                "data"    => $companies,
                "status"  => 200
            ];
        }

        return [
            "success" => true,
            "message" => "Company list with workdays retrieved successfully",
            "data"    => $companies,
            "status"  => 200
        ];

        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getWorkdayStatistics(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try {
        $id    = $request->get('id');
        $year  = $request->get('year') ?? date('Y');
        $month = $request->get('month') ?? date('n');

        if (!$id || !$year || !$month) {
            return [
                "success" => false,
                "message" => "id, year and month are required",
                "status"  => 400
            ];
        }

        $workday = Workday::with(['company.employees', 'holidays'])->find($id);
        if (!$workday) {
            return [
                "success" => false,
                "message" => "Workday not found",
                "status"  => 404
            ];
        }

        $company = $workday->company;
        $total_employees        = $company->employees()->count();
        $total_workday_schedule = $company->workdays()->count();

        $schedule = is_array($workday->schedule)
            ? $workday->schedule
            : json_decode($workday->schedule, true);

        if (!$schedule) {
            return [
                "success" => false,
                "message" => "No schedule found for this workday",
                "status"  => 404
            ];
        }

        $activeDays = collect($schedule)
            ->where('active', true)
            ->pluck('day')
            ->map(fn($d) => ucfirst(strtolower($d)))
            ->toArray();

        $holidayDates = $workday->holidays
            ->filter(function ($holiday) use ($year, $month) {
                $date = Carbon::parse($holiday->date);
                return $date->year == $year && $date->month == $month;
            })
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $total_workdays_in_month = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $isActiveDay = in_array($cursor->format('l'), $activeDays);
            $isHoliday   = in_array($cursor->format('Y-m-d'), $holidayDates);

            if ($isActiveDay && !$isHoliday) {
                $total_workdays_in_month++;
            }
            $cursor->addDay();
        }

        $data = [
            "total_employees"         => $total_employees,
            "total_workday_schedules" => $total_workday_schedule,
            "total_workdays_in_month" => $total_workdays_in_month
        ];

        return [
            "success" => true,
            "message" => "Data Berhasil Diambil",
            "data"    => $data,
            "status"  => 200
        ];
    } catch (Exception $err) {
        return [
            "success" => false,
            "message" => $err->getMessage(),
            "status"  => 400
        ];
    }

    }
    
    public function getWorkdays(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $company_id = $request->company_id;
            $workdays = Workday::where('company_id', $company_id);
            $company_name = Company::find($company_id)->name;
            $data = [
                "company_name" => $company_name,
                "workdays" => $workdays->get()
            ];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data , "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $id = $request->id;
            
            $workday = Workday::with(["holidays"])->where('id', $id);
            $data = $workday->first();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPublicHolidays(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $holidays = PublicHoliday::get();
            $data = $holidays;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $holidays, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getPublicHolidaysWorkday(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        try {
            $workdayId = $request->workday_id;

            $workday = Workday::with('holidays')->find($workdayId);
            if (!$workday) {
                return [
                    "success" => false,
                    "message" => "Workday not found",
                    "status"  => 404
                ];
            }

            $holidays = PublicHoliday::get();

            $holidayIds = $workday->holidays->pluck('id')->toArray();

            $data = $holidays->map(function ($holiday) use ($holidayIds) {
                $holiday->is_libur = in_array($holiday->id, $holidayIds) ? 1 : 0;
                return $holiday;
            });

            return [
                "success" => true,
                "message" => "Data Berhasil Diambil",
                "data"    => $data,
                "status"  => 200
            ];

        } catch (Exception $err) {
            return [
                "success" => false,
                "message" => $err->getMessage(),
                "status"  => 400
            ];
        }
    }

    public function addWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        try{
            $year = $request->year;
            $date = $year . '-' . '01' . '-01';

            $schedule = $request->schedule;

            $workday = new Workday();
            $workday->name = $request->name;
            $workday->company_id = $request->company_id;
            $workday->date = $date;
            $workday->schedule = $schedule;
            $workday->save();

            $holidays = $request->holidays ?? [];
            $workday->holidays()->sync($holidays);
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "id" => $workday->id, "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }


    public function updateWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $year = $request->year;
            $date = $year . '-' . '01' . '-01';

            $schedule = $request->schedule;

            $workday = Workday::find($id);
            $workday->name = $request->name;
            $workday->company_id = $request->company_id;
            $workday->date = $date;
            $workday->schedule = $schedule;
            $workday->save();

            $holidays = $request->holidays ?? [];
            $workday->holidays()->sync($holidays);
            return ["success" => true, "message" => "Data Berhasil Diubah", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteWorkday(Request $request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        try{
            $id = $request->id;
            $workday = Workday::find($id);
            if($workday === null) return ["success" => false, "message" => "Id Tidak Ditemukan", "status" => 400];
            $workday->delete();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "status" => 200];
        }catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

}