<?php

namespace App\Console\Commands;

use App\LongLatList;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class SearchGeoLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:search-geo-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search geo location from longitude latitude list which does not have geo location';

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
        $list_to_be_discovered = LongLatList::whereNull('geo_location')->orderBy('id', 'desc')->where('attempts', '<', 5)->limit(50)->get();
        $client = new Client();
        foreach($list_to_be_discovered as $item){
            $nominatim_response = $client->request('GET', 
                "https://nominatim.openstreetmap.org/reverse.php?lat=".$item->latitude."&lon=".$item->longitude."&zoom=18&format=jsonv2",
                [
                    'headers' => [
                        'User-Agent' => 'Mighty/1.0 (contact: kabar@mitrasolusi.group)'
                    ]
                ]
            );
            $response = json_decode((string) $nominatim_response->getBody());
            if(isset($response->error)) $item->geo_location = null;
            else{
                unset($response->lat);
                unset($response->lon);
                unset($response->importance);
                unset($response->boundingbox);
                
                $item->geo_location = $response;
            } 
            $item->attempts++;
            $item->save();
            sleep(1);
        }
    }
}
