<?php

namespace App\Services;
use App\AccessFeature;
use App\Role;

class GlobalService
{   
    public $agent_role_id = 1;
    public $requester_role_id = 2;
    public $guest_role_id = 3;

    public function checkRoute($route_name){
        //Super Admin Special Bypass
        $super_admin_role = Role::where('name', 'Super Admin')->first();
        $user_super_admin = auth()->user()->roles->where('id', $super_admin_role->id)->first();
        if($user_super_admin) return ["success" => true];
        //------

        //Route Name BYPASS for Special BYPASS
        if($route_name == "BYPASS") return ["success" => true];


        $access_feature = AccessFeature::with('roles')->where('name', $route_name)->first();
        if($access_feature === null) {
            return ["success" => false, "message" => "RUTE AKSES FITUR BELUM TERDAFTAR, SILAHKAN HUBUNGI CS MIG", "status" => 400];
        } else {
            $user_roles = auth()->user()->roles->pluck('id')->toArray();
            $feature_in_roles = $access_feature->roles->pluck('id')->toArray();
            $result = array_intersect($user_roles, $feature_in_roles);
            if(count($result)) return ["success" => true];
            else return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Fitur Ini", "status" => 403];
        }
        return $response;
    }    

    public function romanNumeral(){
        return ['-', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    }    

    public function statusPurchaseOrder(){
        return ['-', 'Draft', 'Disetujui', 'Ditolak', 'Dikirim', 'Diterima'];
    }

    public function diffForHuman($times){
        // 60 - minute
        // 3600 - hour
        // 86400 - day
        // 2592000 - month
        if($times === null) return "-";
        else if($times > 2591999) {
            $months = floor($times / 2592000);
            $remainder = $times % 2592000;
            if($remainder === 0) return "$months Bulan";
            if($remainder > 86399){
                $days = floor($remainder / 86400);
                return "$months Bulan $days Hari";
            } else if($remainder > 3599){
                $hours = floor($remainder / 3600);
                return "$months Bulan $hours Jam";
            } else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$months Bulan $minutes Menit";
            } else return "$months Bulan $remainder Detik";
        } else if($times > 86399) {
            $days = floor($times / 86400);
            $remainder = $times % 86400;
            if($remainder === 0) return "$days Hari";
            else if($remainder > 3599){
                $hours = floor($remainder / 3600);
                return "$days Hari $hours Jam";
            } else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$days Hari $minutes Menit";
            } else return "$days Hari $remainder Detik";
        } else if($times > 3599) {
            $hours = floor($times / 3600);
            $remainder = $times % 3600;
            if($remainder === 0) return "$hours Jam";
            else if($remainder > 59){
                $minutes = floor($remainder / 60);
                return "$hours Jam $minutes Menit";
            } else return "$hours Jam $remainder Detik";
        } else if($times > 59) {
            $minutes = floor($times / 60);
            $remainder = $times % 60;
            if($remainder === 0) return "$minutes Menit";
            else return "$minutes Menit $remainder Detik";
        } else return "$times Detik";
    }

    public function validateGoogleReCaptcha($value){
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array('secret' => env("G_RECAPTCHA_SECRET_KEY"), 'response' => $value);
        
        $options = array(
            'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query($data)
            )
        );
        
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $responseKeys = json_decode($response,true);
        return $responseKeys; 
    }

    public function isUserSuperAdmin()
    {
        $super_admin_role = Role::where('name', 'Super Admin')->first();
        $user_super_admin = auth()->user()->roles->where('id', $super_admin_role->id)->first();
        if($user_super_admin) return true;
        return false;
    }

    public function getIndonesiaMonth($monthInNumber)
    {   
        $monthInNumber = (int)$monthInNumber;
        if($monthInNumber < 1 && $monthInNumber > 12) return "Bulan tidak valid";
        $month = [
            1 => "Januari",
            2 => "Februari",
            3 => "Maret",
            4 => "April",
            5 => "Mei",
            6 => "Juni",
            7 => "Juli",
            8 => "Agustus",
            9 => "September",
            10 => "Oktober",
            11 => "November",
            12 => "Desember",
        ];
        return $month[$monthInNumber];
    }

    public function terbilang($number)
    {
        $number = str_replace('.', '', $number);

        if (! is_numeric($number)) {
            return "";
        }

        $base = array(
            'nol',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan');
        $numeric = array('1000000000000000', '1000000000000', '1000000000000', 1000000000, 1000000, 1000, 100, 10, 1);
        $unit    = array('kuadriliun', 'triliun', 'biliun', 'milyar', 'juta', 'ribu', 'ratus', 'puluh', '');
        $str     = null;

        $i = 0;

        if ($number == 0)
        {
            $str = 'nol';
        }
        else
        {
            while ($number != 0)
            {
                $count = (int)($number / $numeric[$i]);

                if ($count >= 10)
                {
                    $str .= static::terbilang($count) . ' ' . $unit[$i] . ' ';
                }
                elseif ($count > 0 && $count < 10)
                {
                    $str .= $base[$count] . ' ' . $unit[$i] . ' ';
                }

                $number -= $numeric[$i] * $count;

                $i++;
            }

            $str = preg_replace('/satu puluh (\w+)/i', '\1 belas', $str);
            $str = preg_replace('/satu (ribu|ratus|puluh|belas)/', 'se\1', $str);
            $str = preg_replace('/\s{2,}/', ' ', trim($str));
        }

        return $str;
    }
}