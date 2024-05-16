<?php

namespace App\Console\Commands;

use App\RepeatScheduler;
use App\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateScheduleAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate-schedules-attendance {page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generete Schedule Attendance when date - 30 days from now';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $page = $this->argument('page');
        $rows = 50;

        $now = date('Y-m-d');
        $onMonthLater = date('Y-m-d', strtotime($now . ' + 30 days'));


        $data = RepeatScheduler::query()
            ->latest('date')
            ->whereBetween('date', [$now, $onMonthLater])
            ->where('status', false)
            ->simplePaginate($rows, ['*'], 'page', $page);

        foreach ($data as $item) {
            try {
                DB::beginTransaction();
                // Ambil tanggal dari request
                $startDate = Carbon::createFromFormat('Y-m-d', $item->date);
                // Tambahkan 3 bulan dari start date
                $endDate = $startDate->copy()->addMonths(3);
                // Tambahkan 1 hari dari end date
                $schedulerDate = $endDate->copy()->addDay();

                $inserts = [];
                array_push($inserts, [
                    "user_id" => $item->user_id,
                    "shift_id" => $item->shift_id,
                    "date" => $schedulerDate,
                    "repeats" => $item->repeats,
                    "created_at" => date('Y-m-d H:i:s')
                ]);
                DB::table('repeat_scheduler')->insert($inserts);
                unset($inserts);

                $currentDate = $startDate->copy();

                $dates = [];
                while ($currentDate->lessThanOrEqualTo($endDate)) {
                    $index_day = date('w', strtotime($currentDate));
                    if (in_array($index_day, json_decode($item->repeats))) {
                        array_push($dates, date('Y-m-d', strtotime($currentDate)));
                    }
                    $currentDate->addDay();
                }

                if (count($dates)) {
                    $existsMany = Schedule::query()
                        ->where('user_id', $item->user_id)
                        ->whereIn('date', $dates)
                        ->with('user')
                        ->latest('date')
                        ->first();
                    if ($existsMany) {
                        $dates = [];
                    }
                }

                $inserts = [];
                foreach ($dates as $date) {
                    array_push($inserts, [
                        "user_id" => $item->user_id,
                        "shift_id" => $item->shift_id,
                        "date" => $date,
                        "created_at" => date('Y-m-d H:i:s')
                    ]);
                }
                if (count($inserts)) {
                    DB::table('schedules')->insert($inserts);
                    unset($inserts);

                    // $item->status = true;
                    // $item->save();
                    $item->delete();
                    DB::commit();
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error($th);
            }
        }
    }
}
