<?php

use App\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

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

    public function addDefaultCompanies()
    {
        $name = "Mitramas Infosys Global";
        $parent_id = $this->addCompany($name, 1, null, null);
        $this->addDefaultBranchs($parent_id);
        $this->addDefaultClients($parent_id);
    }

    public function run()
    {
        $this->addDefaultCompanies();
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
