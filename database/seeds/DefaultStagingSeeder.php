<?php

use App\Role;
use App\User;
use App\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultStagingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultAgentsRequesters()
    {
        $positions = ['Permanent Employee', 'Contract Employee', 'Freelancer', 'Part-time Employee', 'Contingent Employee', 'Temporary Employee'];
        $default_requesters = Role::where('name', 'Default Requester Users')->firstOrCreate([
            'name' => 'Default Requester Users',
            'description' => 'For Created Requester Users From Default'
        ]);
        $company_requesters = [73, 86, 100];
        $index = 1;
        for($i = 0; $i < 3; $i++){
            for($j = 0; $j < 3; $j++){
                
                $user = new User;
                $user->name = "Requester ".$index;
                $user->nip = "20".$index;
                $user->company_id = $company_requesters[$i];
                $user->email = "requester".$index."@mitramas.com";
                $user->password = Hash::make("123456789");
                $user->role = 2;
                $user->phone_number = "-";
                $user->position = $positions[random_int(0, 5)];
                $user->is_enabled = true;
                $user->created_time = "2022-02-09 09:37:19";
                $user->save();
                $user->roles()->syncWithoutDetaching([$default_requesters->id]);
                $index++;
            }
        }

        $default_agents = Role::where('name', 'Default Agent Users')->firstOrCreate([
            'name' => 'Default Agent Users',
            'description' => 'For Created Agent Users From Default'
        ]);
        $company_agents = [2, 16, 26];
        $index = 1;
        for($i = 0; $i < 3; $i++){
            for($j = 0; $j < 3; $j++){
                
                $user = new User;
                $user->name = "Agent ".$index;
                $user->nip = "10".$index;
                $user->company_id = $company_agents[$i];
                $user->email = "agent".$index."@mitramas.com";
                $user->password = Hash::make("123456789");
                $user->role = 1;
                $user->phone_number = "-";
                $user->position = $positions[random_int(0, 5)];
                $user->is_enabled = true;
                $user->created_time = "2022-02-09 09:37:19";
                $user->save();
                $user->roles()->syncWithoutDetaching([$default_agents->id]);
                $index++;
            }
        }
    }

    private function addCompany($name, $role, $parent_id, $top_parent_id)
    {
        $company = new Company;
        $company->name = $name;
        $company->parent_id = $parent_id;
        $company->top_parent_id = $top_parent_id;
        $company->address = "-";
        $company->phone_number = "-";
        $company->role = $role;
        $company->created_time = "2022-02-09 19:37:19";
        if($role === 4) $company->is_enabled = false;
        else $company->is_enabled = true;
        
        $company->singkatan = "-";
        $company->tanggal_pkp = "2022-05-03";
        $company->penanggung_jawab = '-';
        $company->npwp = '-';
        $company->fax = '-';
        $company->email = '-';
        $company->website = '-';
        $company->save();
        return $company->id;
    }

    private function addDefaultSubLocations()
    {
        $company_ids = [17, 77, 135];
        $companies = Company::find($company_ids);
        foreach($companies as $company){
            $parent_id = $this->addCompany("Sub ".$company->name, 4, $company->id, $company->id);
            for($i = 1; $i < 4; $i++) $this->addCompany("Sub Sub ".$company->name, 4, $parent_id, $company->id);
        }
    }

    public function run()
    {
        $this->call(AccessFeatureSeeder::class);
        $this->call(AssetManagementSeeder::class);
        $this->call(VendorSeeder::class);
        $this->call(ManufacturerSeeder::class);
        $this->call(ModelInventorySeeder::class);
        $this->call(TaskSeeder::class);
        $this->call(PolymorphicCodeSeeder::class);
        $this->call(TicketManagementSeeder::class);
        $this->call(TicketSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(GroupSeeder::class);
        $this->addDefaultAgentsRequesters();
        $this->addDefaultSubLocations();
    }
}
