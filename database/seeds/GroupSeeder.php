<?php

use App\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultGroup()
    {
        $group = new Group;
        $group->name = "Engineer";
        $group->description = "For Engineer";
        $group->group_head = 1;
        $group->is_agent = true;
        $group->save();
        $group->users()->attach(1);
    }

    public function run()
    {
        $this->addDefaultGroup();
    }
}
