<?php

namespace App\Console\Commands\Exclusive;

use App\Schedule;
use App\Services\GlobalService;
use App\Services\UserService;
use App\Shift;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SetDayOffSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exclusive:set-dayoff-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dayoff from https://dayoffapi.vercel.app/';

    private $globalService;
    private $agent_role_id;
    private $cuti_bersama;
    private $libur_nasional;
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
        $this->cuti_bersama = Shift::where('title', 'Cuti Bersama')->first();
        $this->libur_nasional = Shift::where('title', 'Libur Nasional')->first();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->cuti_bersama || !$this->libur_nasional) {
            dd('Shift "Cuti Bersama" atau "Libur Nasional" not found');
        }

        $dataApi = Http::get('https://dayoffapi.vercel.app/api', [
            'year' => date('Y'),
        ]);

        $resultApi = $dataApi->json();

        foreach ($resultApi as $item) {
            $item = json_decode(json_encode($item));

            try {
                DB::beginTransaction();
                $users = $this->getUsers($this->agent_role_id);
                foreach ($users as $user_id) {
                    $shift_id = null;
                    if (str_contains($item->keterangan, 'Cuti Bersama')) {
                        $shift_id = $this->cuti_bersama->id;
                    } else {
                        $shift_id = $this->libur_nasional->id;
                    }

                    Schedule::updateOrCreate(
                        [
                            'user_id' => $user_id,
                            'date' => $item->tanggal
                        ],
                        ['shift_id' => $shift_id]
                    );
                }
                DB::commit();
                dump('Generate dayoff ' . $item->tanggal . ' complete');
            } catch (\Throwable $th) {
                DB::rollBack();
                // throw $th;
                dump($th->getMessage());
            }
        }
    }

    private function getUsers($role_id)
    {
        return User::where('users.role', $role_id)
            ->pluck('id');
    }
}
