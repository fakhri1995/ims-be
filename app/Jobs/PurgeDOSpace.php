<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;

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
        Http::asJson()
        ->withToken(config('filesystems.disks.do.token_api'))
        ->delete(
            config('filesystems.disks.do.cdn_endpoint') . '/cache',
            [
                'files' => ["{$this->link}"],
            ]
        );
    }
}
