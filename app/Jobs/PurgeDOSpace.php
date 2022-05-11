<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class PurgeDOSpace extends Job
{
    public $link;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($link)
    {
        $this->link = $link;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $body = [
                'files' => [$this->link],
        ];
        $headers = [
            'Authorization' => 'Bearer '.config('filesystems.disks.do.token_api'),
            'content-type' => 'application/json'
        ];
        $client = new Client();
        $client->request('DELETE', config('filesystems.disks.do.cdn_endpoint') . '/cache', [
                'headers'  => $headers,
                'json' => $body
        ]);
        // Http::asJson()
        // ->withToken(config('filesystems.disks.do.token_api'))
        // ->delete(
        //     config('filesystems.disks.do.cdn_endpoint') . '/cache',
        //     [
        //         'files' => ["{$this->link}"],
        //     ]
        // );
    }
}
