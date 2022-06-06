<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultUsers()
    {
        $datas = $this->defaultUsers();
        foreach($datas as $data){
            $user = new User;
            $user->name = $data['name'];
            $user->nip = $data['nip'];
            $user->company_id = 1;
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = 1;
            $user->phone_number = "-";
            $user->position = $data['position'];
            $user->is_enabled = true;
            $user->created_time = "2022-02-09 09:37:19";
            $user->save();

            $user->roles()->syncWithoutDetaching([1]);
        }
    }

    public function run()
    {
        $this->addDefaultUsers();
    }

    public function defaultUsers()
    {
        $data = [
            [
                "name" => "Admin MIG",
                "nip" => "5120101",
                "email" => "admin@mitramas.com",
                "password" => "123456789",
                "position" => "-"
            ],
            [
                "name" => "Narendra Hanif",
                "nip" => "5120102",
                "email" => "hanif@mitramas.com",
                "password" => "123456789",
                "position" => "-"
            ],
            [
                "name" => "Kennan Fattahillah Herdyhanto",
                "nip" => "5120103",
                "email" => "kennan@mitramas.com",
                "password" => "123456789",
                "position" => "Frontend Developer"
            ],
            [
                "name" => "Muhammad Faris Makarim",
                "nip" => "5120104",
                "email" => "faris@mitramas.com",
                "password" => "123456789",
                "position" => "Backend Developer"
            ],
            [
                "name" => "Bintang Agung Nusantara",
                "nip" => "5120105",
                "email" => "bintang@mitramas.com",
                "password" => "123456789",
                "position" => "Product Manager"
            ],
            [
                "name" => "Yusron Taufiq",
                "nip" => "5120106",
                "email" => "yusron@mitramas.com",
                "password" => "123456789",
                "position" => "UI & UX Designer"
            ],
            [
                "name" => "Nauval Adiyasa",
                "nip" => "5120107",
                "email" => "nauval@mitramas.com",
                "password" => "123456789",
                "position" => "Android Developer"
            ],
        ];
        return $data;
    }
}
