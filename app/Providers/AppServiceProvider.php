<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Event::listen(MigrationsStarted::class, function (){
               if (env('ALLOW_DISABLED_PK')) DB::statement('SET SESSION sql_require_primary_key=0');
        });

        Event::listen(MigrationsEnded::class, function (){
               if (env('ALLOW_DISABLED_PK')) DB::statement('SET SESSION sql_require_primary_key=1');  
        });
        Storage::disk('do')->getDriver()->getAdapter()->getClient()->getHandlerList()->appendSign(function ($handler) {
            return function ($command, $request) use ($handler) {
                putenv('AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true');
                return $handler($command, $request);
            };
        });
    }
}
