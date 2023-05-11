<?php


namespace App\Helpers;

class MockApiHelper{
    public   function paginateTemplate($request, $data){
        return [
            "current_page" => 2,
            "data" => $data,
            "first_page_url" => env("APP_URL")."/".$request->path()."?page=1",
            "from" => 11,
            "last_page" => 2,
            "last_page_url" => env("APP_URL")."/".$request->path()."?page=2",
            "next_page_url" => env("APP_URL")."/".$request->path()."?page=3",
            "path" => env("APP_URL")."/".$request->path()."?",
            "per_page" => "10",
            "prev_page_url" => env("APP_URL")."/".$request->path()."?page=1",
            "to" => 20,
            "total" => 30
        ];
    }
}

