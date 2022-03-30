<?php

use App\Role;
use App\User;
use App\Group;
use App\Module;
use App\Company;
use App\AccessFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultStagingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultUsers()
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
            $user->nip = $data['nip'];
            $user->company_id = 1;
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = 1;
            $user->phone_number = "-";
            $user->profile_image = "-";
            $user->position = $data['position'];
            $user->is_enabled = true;
            $user->created_time = "2022-02-09 09:37:19";
            $user->save();

            $user->roles()->syncWithoutDetaching([$super_admin_role->id]);
        }

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
                $user->profile_image = "-";
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
                $user->profile_image = "-";
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
        $company->image_logo = "-";
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

    private function addTreeCompanies($data, $role, $highest_parent_id, $top_parent_id){
        $parent_id = $this->addCompany($data['name'], $role, $highest_parent_id, $top_parent_id);
        if(isset($data['children'])){
            foreach($data['children'] as $child){
                $this->addTreeCompanies($child, $role, $parent_id, $top_parent_id);
            }
        }
    }

    private function addDefaultClients($highest_parent_id)
    {
        $datas = $this->defaultDataClients();
        foreach($datas as $data){
            $parent_id = $this->addCompany($data['name'], 2, $highest_parent_id, null);
            if(isset($data['children'])){
                foreach($data['children'] as $child){
                    $this->addTreeCompanies($child, 2, $parent_id, $parent_id);
                }
            }
        }
    }

    private function addDefaultBranchs($highest_parent_id)
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

    private function addDefaultSubLocations()
    {
        $company_ids = [17, 77, 135];
        $companies = Company::find($company_ids);
        foreach($companies as $company){
            $parent_id = $this->addCompany("Sub ".$company->name, 4, $company->id, $company->id);
            for($i = 1; $i < 4; $i++) $this->addCompany("Sub Sub ".$company->name, 4, $parent_id, $company->id);
        }
    }

    public function addDefaultCompanies()
    {
        $name = "Mitramas Infosys Global";
        $parent_id = $this->addCompany($name, 1, null, null);
        $this->addDefaultBranchs($parent_id);
        $this->addDefaultClients($parent_id);
        $this->addDefaultSubLocations();
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
        $this->addDefaultCompanies();
        $this->addDefaultGroup();
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

    public function defaultDataClients()
    {
        $data = [
            [
                'name' => 'BANK BUKOPIN',
                'children' => [
                    [
                        'name' => 'PUSAT',
                        'children' => [
                            [
                                'name' => 'HUB I',
                                'children' => [
                                    [
                                        'name' => 'SAHARJO',
                                        'children' => [
                                            ['name' => 'KCP TEBET'],
                                            ['name' => 'KCP BAKRIE TOWER'],
                                            ['name' => 'KOTA KASABLANKA'],
                                            ['name' => 'MENARA KARYA']
                                        ],
                                    ]
                                ],
                            ],
                            [
                                'name' => 'HUB II',
                                'children' => [
                                    [
                                        'name' => 'S PARMAN',
                                        'children' => [
                                            ['name' => 'KCP TANAH ABANG'],
                                            ['name' => 'KK UNIV ESA UNGGUL'],
                                            ['name' => 'KCP S PARMAN']
                                        ],
                                    ]
                                ],
                            ],
                            [
                                'name' => 'HUB III',
                                'children' => [
                                    [
                                        'name' => 'SENTRAYA',
                                        'children' => [
                                            ['name' => 'KCP MAMPANG'],
                                            ['name' => 'KK PLN PUSAT'],
                                            ['name' => 'KCP KEMANG']
                                        ],
                                    ]
                                ],
                            ],
                        ]
                    ],
                    [
                        'name' => 'DAERAH',
                        'children' => [
                            [
                                'name' => 'SURABAYA',
                                'children' => [
                                    [
                                        'name' => 'SURABAYA',
                                        'children' => [
                                            ['name' => 'INDOMARET SIMOTAMBAAN'],
                                            ['name' => 'INDOMARET RAYA DUPAK'],
                                            ['name' => 'KCP PERAK BARAT']
                                        ],
                                    ],
                                    [
                                        'name' => 'KEDIRI',
                                        'children' => [
                                            ['name' => 'KCU KEDIRI'],
                                            ['name' => 'KCP NGANJUK'],
                                            ['name' => 'KCP BLITAR']
                                        ],
                                    ],
                                    [
                                        'name' => 'MADIUN',
                                        'children' => [
                                            ['name' => 'KCU MADIUN'],
                                            ['name' => 'KK PONOROGO'],
                                            ['name' => 'KK NGAWI']
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'BANDUNG',
                                'children' => [
                                    [
                                        'name' => 'BANDUNG',
                                        'children' => [
                                            ['name' => 'KCU BANDUNG'],
                                            ['name' => 'KCP CIMAHI'],
                                            ['name' => 'KK ITB']
                                        ],
                                    ],
                                    [
                                        'name' => 'TASIKMALAYA',
                                        'children' => [
                                            ['name' => 'KCU TASIKMALAYA'],
                                            ['name' => 'KCP CIAMIS'],
                                            ['name' => 'INDOMARET MARTADINATA']
                                        ],
                                    ],
                                    [
                                        'name' => 'CIREBON',
                                        'children' => [
                                            ['name' => 'KCU CIREBON'],
                                            ['name' => 'KCP PLERED'],
                                            ['name' => 'KCP KUNINGAN'],
                                            ['name' => 'KCP INDRAMAYU']
                                        ],
                                    ],
                                ],
                            ],[
                                'name' => 'MAKASSAR',
                                'children' => [
                                    [
                                        'name' => 'MAKASSAR',
                                        'children' => [
                                            ['name' => 'KCU MAKASSAR'],
                                            ['name' => 'KCP CENDRAWASIH'],
                                            ['name' => 'MARI MALL']
                                        ],
                                    ],
                                    [
                                        'name' => 'PARE-PARE',
                                        'children' => [
                                            ['name' => 'KCP PINRANG'],
                                            ['name' => 'KCP MAMUJU'],
                                            ['name' => 'KK POLWEALI']
                                        ],
                                    ],
                                    [
                                        'name' => 'MANADO',
                                        'children' => [
                                            ['name' => 'KCU MANADO'],
                                            ['name' => 'LIPPO MALL'],
                                            ['name' => 'INDOMARET SAMRATULANGI']
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'BANK BUKOPIN SYARIAH',
                'children' => [
                    [
                        'name' => 'INDUK',
                        'children' => [
                            [
                                'name' => 'BSB PUSAT',
                                'children' => [
                                    ['name' => 'KCU BSB KELAPA GADING'],
                                    ['name' => 'KCU BSB BEKASI'],
                                    ['name' => 'KCP BSB KRAMAT JATI'],
                                    ['name' => 'BSB MENTENG']
                                ],
                            ],
                            [
                                'name' => 'BSB MELAWAI',
                                'children' => [
                                    ['name' => 'KCU BSB MELAWAI'],
                                    ['name' => 'BSB RS JATISAMPURNA BEKASI'],
                                    ['name' => 'MOBILE KAS BSB MELAWAI']
                                ],
                            ]
                        ],
                    ],
                    [
                        'name' => 'SOLO',
                        'children' => [
                            [
                                'name' => 'BSB SOLO',
                                'children' => [
                                    ['name' => 'KCU SOLO'],
                                    ['name' => 'MOBILE KAS BSB SOLO']
                                ],
                            ]
                        ],
                    ],[
                        'name' => 'SURABAYA',
                        'children' => [
                            [
                                'name' => 'BSB SURABAYA',
                                'children' => [
                                    ['name' => 'KCU SURABAYA'],
                                    ['name' => 'RS DARMO'],
                                    ['name' => 'RS MATA UNDAAN'],
                                    ['name' => 'KCP MERR']
                                ],
                            ],
                            [
                                'name' => 'BSB SIDOARJO',
                                'children' => [
                                    ['name' => 'KCU SIDOARJO']
                                ],
                            ]
                        ],
                    ]
                ]
            ]

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
                            ['name' => 'ACEH']
                        ],
                    ],
                    [
                        'name' => 'BASE MEDAN',
                        'children' => [
                            ['name' => 'MEDAN']
                        ],
                    ],
                    [
                        'name' => 'BASE PADANG',
                        'children' => [
                            ['name' => 'PADANG'],
                            ['name' => 'JAMBI'],
                        ],
                    ],
                    [
                        'name' => 'BASE PEKANBARU',
                        'children' => [
                            ['name' => 'PEKANBARU']
                        ],
                    ],
                    [
                        'name' => 'BASE BATAM',
                        'children' => [
                            ['name' => 'BATAM'],
                            ['name' => 'TANJUNG PINANG'],
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
                            ['name' => 'JABODETABEK'],
                            ['name' => 'KARAWANG'],
                            ['name' => 'CILEGON'],
                            ['name' => 'SUKABUMI'],
                            ['name' => 'CIANJUR']
                        ],
                    ],
                    [
                        'name' => 'BASE PALEMBANG',
                        'children' => [
                            ['name' => 'PALEMBANG'],
                            ['name' => 'BANDAR LAMPUNG']
                        ],
                    ],
                ]
            ],
            [
                'name' => 'WILAYAH 3 JAWA',
                'children' => [
                    [
                        'name' => 'BASE BANDUNG',
                        'children' => [
                            ['name' => 'BANDUNG'],
                            ['name' => 'TASIK'],
                            ['name' => 'PENGALENGAN'],
                            ['name' => 'CIREBON']
                        ],
                    ],
                    [
                        'name' => 'BASE SEMARANG',
                        'children' => [
                            ['name' => 'SEMARANG'],
                            ['name' => 'PURWOKERTO'],
                            ['name' => 'TEGAL'],
                            ['name' => 'CILACAP'],
                            ['name' => 'KUDUS']
                        ],
                    ],
                    [
                        'name' => 'BASE YOGYAKARTA',
                        'children' => [
                            ['name' => 'YOGYAKARTA'],
                            ['name' => 'MAGELANG']
                        ],
                    ],
                    [
                        'name' => 'BASE SOLO',
                        'children' => [
                            ['name' => 'SOLO'],
                            ['name' => 'BOYOLALI'],
                            ['name' => 'KLATEN']
                        ],
                    ],
                    [
                        'name' => 'BASE SURABAYA',
                        'children' => [
                            ['name' => 'SURABAYA'],
                            ['name' => 'MADIUN'],
                            ['name' => 'SIDOARJO'],
                            ['name' => 'MALANG'],
                            ['name' => 'KEDIRI'],
                            ['name' => 'PROBOLINGGO'],
                            ['name' => 'JEMBER']
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
                            ['name' => 'DENPASAR'],
                            ['name' => 'MATARAM']
                        ],
                    ],
                    [
                        'name' => 'BASE KUPANG',
                        'children' => [
                            ['name' => 'KUPANG']
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
                            ['name' => 'SAMARINDA'],
                            ['name' => 'BALIKPAPAN']
                        ],
                    ],
                    [
                        'name' => 'BASE BANJARMASIN',
                        'children' => [
                            ['name' => 'BANJARMASIN']
                        ],
                    ],
                    [
                        'name' => 'BASE PONTIANAK',
                        'children' => [
                            ['name' => 'PONTIANAK']
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
                            ['name' => 'MAKASSAR'],
                            ['name' => 'MANADO'],
                            ['name' => 'PAPUA'],
                            ['name' => 'SORONG'],
                            ['name' => 'PALU']
                        ],
                    ]
                ]
            ],

        ];
        return $data;
    }
}
