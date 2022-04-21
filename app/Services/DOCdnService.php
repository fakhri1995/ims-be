<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DOCdnService
{
    public function purge($fileName)
    {
        $response = Http::asJson()
        ->withToken(config('filesystems.disks.do.token_api'))
        ->delete(
            config('filesystems.disks.do.cdn_endpoint') . '/cache',
            [
                'files' => ["{$fileName}"],
            ]
        );
        return json_decode($response->getBody(), true);
    }
}