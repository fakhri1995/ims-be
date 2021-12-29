<?php

use App\Role;
use App\User;
use App\Group;
use App\Module;
use App\Company;
use App\AccessFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function addDefaultUsers()
    {
        $positions = ['Permanent Employee', 'Contract Employee', 'Freelancer', 'Part-time Employee', 'Contingent Employee', 'Temporary Employee'];
        $super_admin_role = Role::where('name', 'Super Admin')->firstOrCreate([
            'name' => 'Super Admin',
            'description' => 'The Highest Role Access for Users'
        ]);
        
        $datas = $this->defaultUsers();
        foreach($datas as $data){
            $user = new User;
            $user->name = $data['name'];
            $user->company_id = 1;
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = 1;
            $user->phone_number = "-";
            $user->profile_image = "-";
            $user->position = $data['position'];
            $user->is_enabled = true;
            $user->created_time = "2021-02-09 09:37:19";
            $user->save();

            $user->roles()->syncWithoutDetaching([$super_admin_role->id]);
        }

        $default_requesters = Role::where('name', 'Default Requester Users')->firstOrCreate([
            'name' => 'Default Requester Users',
            'description' => 'For Created Requester Users From Default'
        ]);
        $company_requesters = [72, 86, 100];
        $index = 1;
        for($i = 0; $i < 3; $i++){
            for($j = 0; $j < 3; $j++){
                
                $user = new User;
                $user->name = "Requester ".$index;
                $user->company_id = $company_requesters[$i];
                $user->email = "requester".$index."@mitramas.com";
                $user->password = Hash::make("123456789");
                $user->role = 2;
                $user->phone_number = "-";
                $user->profile_image = "-";
                $user->position = $positions[random_int(0, 5)];
                $user->is_enabled = true;
                $user->created_time = "2021-02-09 09:37:19";
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
                $user->company_id = $company_agents[$i];
                $user->email = "agent".$index."@mitramas.com";
                $user->password = Hash::make("123456789");
                $user->role = 1;
                $user->phone_number = "-";
                $user->profile_image = "-";
                $user->position = $positions[random_int(0, 5)];
                $user->is_enabled = true;
                $user->created_time = "2021-02-09 09:37:19";
                $user->save();
                $user->roles()->syncWithoutDetaching([$default_agents->id]);
                $index++;
            }
        }
    }

    public function addCompany($name, $role, $parent_id, $top_parent_id)
    {
        $company = new Company;
        $company->name = $name;
        $company->parent_id = $parent_id;
        $company->top_parent_id = $top_parent_id;
        $company->address = "-";
        $company->phone_number = "-";
        $company->image_logo = "-";
        $company->role = $role;
        $company->created_time = "2021-02-09 19:37:19";
        $company->is_enabled = true;
        
        $company->singkatan = "-";
        $company->tanggal_pkp = "2021-05-03";
        $company->penanggung_jawab = '-';
        $company->npwp = '-';
        $company->fax = '-';
        $company->email = '-';
        $company->website = '-';
        $company->save();
        return $company->id;
    }

    public function addTreeCompanies($data, $role, $highest_parent_id, $top_parent_id){
        $parent_id = $this->addCompany($data['name'], 3, $highest_parent_id, $top_parent_id);
        if(isset($data['children'])){
            foreach($data['children'] as $child){
                $this->addTreeCompanies($child, 3, $parent_id, $top_parent_id);
            }
        }
    }

    public function addDefaultClients($highest_parent_id)
    {
        for($i = 1; $i < 4; $i++){
            $code = "Infosys Client $i";
            $parent_id = $this->addCompany($code, 2, $highest_parent_id, null);
            for($j = 1; $j < 4; $j++){
                $code_a = "$code.$j";
                $parent_id_a = $this->addCompany($code_a, 2, $parent_id, $parent_id);
                for($k = 1; $k < 4; $k++){
                    $code_b = "$code_a.$k";
                    $parent_id_b = $this->addCompany($code_b, 2, $parent_id_a, $parent_id);
                }
            }
        }
    }

    public function addDefaultBranchs($highest_parent_id)
    {
        $datas = $this->defaultDataBranchs();
        foreach($datas as $data){
            $parent_id = $this->addCompany($data['name'], 3, $highest_parent_id, null);
            if(isset($data['children'])){
                foreach($data['children'] as $child){
                    $this->addTreeCompanies($child, 3, $parent_id, $parent_id);
                }
            }
        }
    }

    public function addDefaultCompanies()
    {
        $name = "Mitramas Infosys Global";
        $parent_id = $this->addCompany($name, 1, null, null);
        $this->addDefaultBranchs($parent_id);
        $this->addDefaultClients($parent_id);
    }

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
        $this->addDefaultUsers();
        // $this->addDefaultCompanies();
        // $this->addDefaultGroup();
    }

    public function defaultUsers()
    {
        $data = [
            [
                "name" => "Narendra Hanif",
                "email" => "hanif@mitramas.com",
                "password" => "m1tramas",
                "position" => "-"
            ],
            [
                "name" => "Yues Tadrik Hafiyan",
                "email" => "yues@mitramas.com",
                "password" => "123456789",
                "position" => "Frontend Developer"
            ],
            [
                "name" => "Muhammad Faris Makarim",
                "email" => "faris@mitramas.com",
                "password" => "123456789",
                "position" => "Backend Developer"
            ],
            [
                "name" => "Bintang Agung Nusantara",
                "email" => "bintang@mitramas.com",
                "password" => "123456789",
                "position" => "Product Manager"
            ],
            [
                "name" => "Yusron Taufiq",
                "email" => "yusron@mitramas.com",
                "password" => "123456789",
                "position" => "UI & UX Designer"
            ],
            [
                "name" => "Nauval Adiyasa",
                "email" => "nauval@mitramas.com",
                "password" => "123456789",
                "position" => "Android Developer"
            ],
        ];
        return $data;
    }

    public function defaultDataBranchs()
    {
        $data = [
            [
                'name' => 'WILAYAH 1 SUMATRA',
                'children' => [
                    [
                        'name' => 'BASE ACEH',
                        'children' => [
                            ['name' => 'KCU ACEH']
                        ],
                    ],
                    [
                        'name' => 'BASE MEDAN',
                        'children' => [
                            ['name' => 'KCU MEDAN']
                        ],
                    ],
                    [
                        'name' => 'BASE PADANG',
                        'children' => [
                            ['name' => 'KCU PADANG'],
                            ['name' => 'KCU JAMBI'],
                        ],
                    ],
                    [
                        'name' => 'BASE PEKANBARU',
                        'children' => [
                            ['name' => 'KCU PEKANBARU']
                        ],
                    ],
                    [
                        'name' => 'BASE BATAM',
                        'children' => [
                            ['name' => 'KCU BATAM'],
                            ['name' => 'KCU TANJUNG PINANG'],
                        ],
                    ],
                ]
            ],
            [
                'name' => 'WILAYAH 2 JAKARTA',
                'children' => [
                    [
                        'name' => 'BASE PUSAT',
                        'children' => [
                            ['name' => 'KCU JABODETABEK'],
                            ['name' => 'KCU KARAWANG'],
                            ['name' => 'KCU CILEGON'],
                            ['name' => 'KCU SUKABUMI'],
                            ['name' => 'KCU CIANJUR'],
                            ['name' => 'KCU BANDAR LAMPUNG'],
                            ['name' => 'KCU PALEMBANG']
                        ],
                    ]
                ]
            ],
            [
                'name' => 'WILAYAH 3 JAWA',
                'children' => [
                    [
                        'name' => 'BASE BANDUNG',
                        'children' => [
                            ['name' => 'KCU BANDUNG'],
                            ['name' => 'KCU TASIK'],
                            ['name' => 'KCU PENGALENGAN'],
                            ['name' => 'KCU CIREBON']
                        ],
                    ],
                    [
                        'name' => 'BASE SEMARANG',
                        'children' => [
                            ['name' => 'KCU SEMARANG'],
                            ['name' => 'KCU PURWOKERTO'],
                            ['name' => 'KCU TEGAL'],
                            ['name' => 'KCU CILACAP'],
                            ['name' => 'KCU KUDUS']
                        ],
                    ],
                    [
                        'name' => 'BASE YOGYAKARTA',
                        'children' => [
                            ['name' => 'KCU YOGYAKARTA'],
                            ['name' => 'KCU MAGELANG']
                        ],
                    ],
                    [
                        'name' => 'BASE SOLO',
                        'children' => [
                            ['name' => 'KCU SOLO'],
                            ['name' => 'KCU BOYOLALI'],
                            ['name' => 'KCU KLATEN']
                        ],
                    ],
                    [
                        'name' => 'BASE SURABAYA',
                        'children' => [
                            ['name' => 'KCU SURABAYA'],
                            ['name' => 'KCU MADIUN'],
                            ['name' => 'KCU SIDOARJO'],
                            ['name' => 'KCU MALANG'],
                            ['name' => 'KCU KEDIRI'],
                            ['name' => 'KCU PROBOLINGGO'],
                            ['name' => 'KCU JEMBER']
                        ],
                    ],
                ]
            ],
            [
                'name' => 'WILAYAH 4 BALI - NUSA TENGGARA',
                'children' => [
                    [
                        'name' => 'BASE DENPASAR',
                        'children' => [
                            ['name' => 'KCU DENPASAR'],
                            ['name' => 'KCU MATARAM']
                        ],
                    ],
                    [
                        'name' => 'BASE KUPANG',
                        'children' => [
                            ['name' => 'KCU KUPANG']
                        ],
                    ]
                ]
            ],
            [
                'name' => 'WILAYAH 5 KALIMANTAN',
                'children' => [
                    [
                        'name' => 'BASE SAMARINDA',
                        'children' => [
                            ['name' => 'KCU SAMARINDA'],
                            ['name' => 'KCU BALIKPAPAN']
                        ],
                    ],
                    [
                        'name' => 'BASE BANJARMASIN',
                        'children' => [
                            ['name' => 'KCU BANJARMASIN']
                        ],
                    ],
                    [
                        'name' => 'BASE PONTIANAK',
                        'children' => [
                            ['name' => 'KCU PONTIANAK']
                        ],
                    ],
                ]
            ],
            [
                'name' => 'WILAYAH 6 SULAWESI',
                'children' => [
                    [
                        'name' => 'BASE MAKASSAR',
                        'children' => [
                            ['name' => 'KCU MAKASSAR'],
                            ['name' => 'KCU MANADO'],
                            ['name' => 'KCU PAPUA'],
                            ['name' => 'KCU SORONG'],
                            ['name' => 'KCU PALU']
                        ],
                    ]
                ]
            ],

        ];
        return $data;
    }
}
