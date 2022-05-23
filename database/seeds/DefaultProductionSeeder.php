<?php

use Illuminate\Database\Seeder;

class DefaultProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $this->call(AccessFeatureSeeder::class);
        $this->call(AssetManagementSeeder::class);
        $this->call(PolymorphicCodeSeeder::class);
        $this->call(TicketManagementSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(GroupSeeder::class);
    }
}
