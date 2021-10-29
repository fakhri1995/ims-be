<?php

use App\PolymorphicCode;
use Illuminate\Database\Seeder;

class PolymorphicCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function makePolymorphicCodes()
    {
        $polymorphic_codes = [
            ['code' => 'App\Group' , 'name' => 'Group'],
            ['code' => 'App\User' , 'name' => 'Engineer'],
            ['code' => 'App\Incident' , 'name' => 'Incident']
        ];
        foreach($polymorphic_codes as $polymorphic_code){
            $status = new PolymorphicCode;
            $status->name = $polymorphic_code['name'];
            $status->code = $polymorphic_code['code'];
            $status->save();
        }
    }

    public function run()
    {
        $this->makePolymorphicCodes();
    }

}
