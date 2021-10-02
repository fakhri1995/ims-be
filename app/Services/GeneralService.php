<?php

namespace App\Services;
use App\Services\GeneralService;
use DateTime;
use DateTimeZone;

class GeneralService
{
    public function getTimeNow(){
        $timezone = 'Asia/Jakarta';
        $timestamp = time();
        $datetime = new DateTime("now", new DateTimeZone($timezone)); 
        $datetime->setTimestamp($timestamp);
        return $datetime->format('Y-m-d H:i:s');
    }
}