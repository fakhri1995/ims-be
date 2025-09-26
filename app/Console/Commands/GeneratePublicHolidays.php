<?php

namespace App\Console\Commands;

use App\PublicHoliday;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GeneratePublicHolidays extends Command
{
	
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate-public-holidays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate this years public holidays';

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
		$client = new Client();
		$res_string = $client->request('GET', "https://dayoffapi.vercel.app/api");
		$res = json_decode((string) $res_string->getBody());
		foreach($res as $date){
			$holiday = new PublicHoliday();
			$holiday->date = $date->tanggal;
			$holiday->name = $date->keterangan;
			$holiday->is_cuti = $date->is_cuti;
			$holiday->save();
		}
    }
}