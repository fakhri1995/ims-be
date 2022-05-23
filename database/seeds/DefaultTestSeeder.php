<?php

use Illuminate\Database\Seeder;

class DefaultTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(CompanySeeder::class);
    }
}
