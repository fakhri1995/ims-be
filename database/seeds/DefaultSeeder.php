<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\AccessFeature;
use App\Company;
use App\Module;
use App\User;
use App\UserRolePivot;
use App\Role;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function addDefaultUser()
    {
        $user = new User;
        $user->fullname = "narendra hanif";
        $user->company_id = 1;
        $user->email = "hanif@mitramas.com";
        $user->password = Hash::make("m1tramas");
        $user->role = 1;
        $user->phone_number = "0811982675";
        $user->profile_image = "https://cgx.co.id/img/user/default-account.png";
        $user->is_enabled = true;
        $user->created_time = "2021-02-09 09:37:19";
        $user->save();
    }

    public function addDefaultCompany()
    {
        $company = new Company;
        $company->company_name = "mitramas infosys global";
        $company->parent_id = null;
        $company->address = "Tebet raya nomor 42, jakarta selatan";
        $company->phone_number = "0811982675";
        $company->image_logo = "https://cgx.co.id/img/partner/mitramas-infosys-global.jpg";
        $company->role = 1;
        $company->created_time = "2021-02-09 09:37:19";
        $company->is_enabled = true;
        
        $company->singkatan = "MIG";
        $company->tanggal_pkp = "2021-04-03";
        $company->penanggung_jawab = 'Yues';
        $company->npwp = '-';
        $company->fax = '02129192021';
        $company->email = 'mig@mitrasolusi.group';
        $company->website = 'https://migsys.herokuapp.com/';
        $company->save();
    }

    public function addDefaultRole()
    {
        $super_admin_role = Role::where('name', 'Super Admin')->first();
        if($super_admin_role === null){
            $super_admin_role = new Role;
            $super_admin_role->name = "Super Admin";
            $super_admin_role->description = "The Highest Role Access for Users";
            $super_admin_role->save();
        }
        if($super_admin_role){
            $pivots = UserRolePivot::where('user_id', 1)->get();
            if(count($pivots)){
                $pivot_super_admin = $pivots->where('role_id', $super_admin_role->id)->first();
                if($pivot_super_admin === null){
                    $new_super_admin_user = new UserRolePivot;
                    $new_super_admin_user->user_id = 1;
                    $new_super_admin_user->role_id = $super_admin_role->id;
                    $new_super_admin_user->save();
                } 
            } else {
                $new_super_admin_user = new UserRolePivot;
                $new_super_admin_user->user_id = 1;
                $new_super_admin_user->role_id = $super_admin_role->id;
                $new_super_admin_user->save();
            }
        }
    }

    public function addDefaultFeatures()
    {
        $default_data_features = $this->defaultDataFeatures();
        foreach($default_data_features as $features){
            $access_feature = new AccessFeature;
            $access_feature->name = $features['name'];
            $access_feature->description = $features['description'];
            $access_feature->feature_id = 1;
            $access_feature->feature_key = $features['feature_key'];
            $access_feature->save();
        }
    }

    public function addDefaultModules()
    {
        $default_data_modules = $this->defaultDataModules();
        $access_features = AccessFeature::select('id', 'name')->get();
        foreach($default_data_modules as $module){
            $features = [];
            if(count($module['feature'])){
                foreach($module['feature'] as $feature){
                    $access_feature = $access_features->where('name', $feature['name'])->first();
                    if($access_feature) $features[] = $access_feature->id;
                }
            }
            $new_module = new Module;
            $new_module->name = $module['name'];
            $new_module->description = $module['description'];
            $new_module->features = $features;
            $new_module->save();
        }
    }

    public function run()
    {
        $this->addDefaultUser();
        $this->addDefaultCompany();
        $this->addDefaultRole();
        $this->addDefaultFeatures();
        $this->addDefaultModules();
    }

    public function defaultDataModules()
    {
        $data = [
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur agent",
                "feature" => [
                    [
                        "id" => 107,
                        "key" => "b699dca3-9908-41f9-9583-e34f07f7e5c7",
                        "name" => "AGENT_GET"
                    ],
                    [
                        "id" => 108,
                        "key" => "35eaa6ba-26f7-4fb3-9c5f-70997bc7c7df",
                        "name" => "AGENTS_GET"
                    ],
                    [
                        "id" => 109,
                        "key" => "18e7b0ce-5381-4373-b900-bf1f1a2404a8",
                        "name" => "AGENT_ADD"
                    ],
                    [
                        "id" => 110,
                        "key" => "1162e26e-3c40-455d-80d7-30b8c2980636",
                        "name" => "AGENT_UPDATE"
                    ],
                    [
                        "id" => 111,
                        "key" => "35389229-9026-4caf-b9d4-a63dd5a9c10d",
                        "name" => "AGENT_PASSWORD_UPDATE"
                    ],
                    [
                        "id" => 112,
                        "key" => "285e11c1-973b-432f-8856-36deefc52f2e",
                        "name" => "AGENT_STATUS"
                    ],
                    [
                        "id" => 132,
                        "key" => "7bf52f77-7a9d-4fb0-8b86-682b1c5afbd0",
                        "name" => "AGENT_UPDATE_FEATURE"
                    ],
                    [
                        "id" => 134,
                        "key" => "e7a75e1a-84bf-4cd6-9b90-959e1deabf57",
                        "name" => "AGENT_GROUPS_GET"
                    ],
                    [
                        "id" => 135,
                        "key" => "74df6b7f-f7e3-43bc-989a-71dbcd08d747",
                        "name" => "AGENT_GROUP_ADD"
                    ],
                    [
                        "id" => 136,
                        "key" => "42a27715-5217-4906-b486-5dae631105e1",
                        "name" => "AGENT_GROUP_GET"
                    ],
                    [
                        "id" => 137,
                        "key" => "3ddf2248-6e55-4a41-a1b9-a6e1f3af6036",
                        "name" => "AGENT_GROUP_UPDATE"
                    ],
                    [
                        "id" => 138,
                        "key" => "0fc381b0-4c43-43d4-b964-17888b861227",
                        "name" => "AGENT_GROUP_DELETE"
                    ]
                ],
                "id" => 9,
                "key" => "1e19e339-14c5-4e4a-aaa2-0fd734010370",
                "name" => "Agent",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur requester",
                "feature" => [
                    [
                        "id" => 114,
                        "key" => "7ffd9e74-5399-46d9-8d01-f80016c4f9f2",
                        "name" => "REQUESTER_STATUS"
                    ],
                    [
                        "id" => 115,
                        "key" => "5454137c-9441-43cc-bce0-342a7856aa29",
                        "name" => "REQUESTER_PASSWORD_UPDATE"
                    ],
                    [
                        "id" => 116,
                        "key" => "ebb04195-5e1b-4a4a-8dc6-fac8ba0c4648",
                        "name" => "REQUESTER_UPDATE"
                    ],
                    [
                        "id" => 117,
                        "key" => "51d584e0-be3c-45c4-84e8-a5c7a10d6233",
                        "name" => "REQUESTER_ADD"
                    ],
                    [
                        "id" => 118,
                        "key" => "d05cd99c-3ee7-4cb9-ada9-7482a0aae2ff",
                        "name" => "REQUESTER_GET"
                    ],
                    [
                        "id" => 119,
                        "key" => "5607785a-f42e-4b09-a391-3f378868394c",
                        "name" => "REQUESTERS_GET"
                    ],
                    [
                        "id" => 133,
                        "key" => "68c0bc7a-2366-49f7-8579-e82fee9a5a1e",
                        "name" => "REQUESTER_UPDATE_FEATURE"
                    ],
                    [
                        "id" => 139,
                        "key" => "bedca85a-1073-4c36-b8d0-86b05a79451f",
                        "name" => "REQUESTER_GROUPS_GET"
                    ],
                    [
                        "id" => 140,
                        "key" => "dbee04f9-21d4-499c-9b77-6ae0cf248159",
                        "name" => "REQUESTER_GROUP_ADD"
                    ],
                    [
                        "id" => 141,
                        "key" => "a288ea00-b73e-4816-9096-21b5ac3bd3b8",
                        "name" => "REQUESTER_GROUP_GET"
                    ],
                    [
                        "id" => 142,
                        "key" => "502f71d7-b769-4db3-8df6-536026d89bd8",
                        "name" => "REQUESTER_GROUP_UPDATE"
                    ],
                    [
                        "id" => 143,
                        "key" => "14bb5610-abb8-4a02-ae21-58dfd7a222d8",
                        "name" => "REQUESTER_GROUP_DELETE"
                    ]
                ],
                "id" => 10,
                "key" => "a9c8a276-6ae2-48d7-8871-c5ab95f46e2e",
                "name" => "Requester",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur MIG company",
                "feature" => [
                    [
                        "id" => 144,
                        "key" => "499e1844-d4dd-4725-b98a-27523e0994df",
                        "name" => "MAIN_COMPANY_GET"
                    ],
                    [
                        "id" => 145,
                        "key" => "b9bba2f3-8cf3-4d4c-b0c2-937decba72c7",
                        "name" => "MAIN_COMPANY_UPDATE"
                    ],
                    [
                        "id" => 146,
                        "key" => "012ce495-a6fb-4c9f-846c-53341a3ddc63",
                        "name" => "MAIN_BANKS_GET"
                    ],
                    [
                        "id" => 147,
                        "key" => "11eef373-28ba-444e-ab59-542b13a72d56",
                        "name" => "MAIN_BANK_ADD"
                    ],
                    [
                        "id" => 148,
                        "key" => "4947152c-658c-4df7-9fd7-03421e99d7c0",
                        "name" => "MAIN_BANK_UPDATE"
                    ],
                    [
                        "id" => 149,
                        "key" => "044f5d80-91d0-43a3-a283-24f44f045b03",
                        "name" => "MAIN_BANK_DELETE"
                    ]
                ],
                "id" => 13,
                "key" => "c290f4d0-4fb4-4949-a08d-c27456d5b876",
                "name" => "Main Company",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur perusahaan cabang",
                "feature" => [
                    [
                        "id" => 150,
                        "key" => "67d07ba8-f37c-4649-8642-a17807863b35",
                        "name" => "COMPANY_BRANCHS_GET"
                    ],
                    [
                        "id" => 151,
                        "key" => "f419c740-1316-431d-8347-9446e1b92159",
                        "name" => "COMPANY_BRANCH_GET"
                    ],
                    [
                        "id" => 152,
                        "key" => "8ff00868-1df4-41fc-b402-377a94aaec09",
                        "name" => "COMPANY_BRANCH_ADD"
                    ],
                    [
                        "id" => 153,
                        "key" => "233d1e25-d083-49c3-88a8-bbcc5b235ca3",
                        "name" => "COMPANY_BRANCH_UPDATE"
                    ],
                    [
                        "id" => 154,
                        "key" => "f311acf9-0d9c-481b-ba36-5ca512ea1715",
                        "name" => "COMPANY_BRANCH_STATUS"
                    ]
                ],
                "id" => 14,
                "key" => "43f7ca5b-1725-4a4e-a5fa-7b1dab8c37b0",
                "name" => "Branch Company",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur perusahaan client",
                "feature" => [
                    [
                        "id" => 155,
                        "key" => "8c8eba57-f0b2-431a-9a34-398e32d4d174",
                        "name" => "COMPANY_CLIENTS_GET"
                    ],
                    [
                        "id" => 156,
                        "key" => "74f590ef-5dde-44d4-be74-549884482b31",
                        "name" => "COMPANY_CLIENT_GET"
                    ],
                    [
                        "id" => 157,
                        "key" => "b83ff8df-f7b2-493b-ad59-7a54670da8d6",
                        "name" => "COMPANY_CLIENT_ADD"
                    ],
                    [
                        "id" => 158,
                        "key" => "604b1d8e-5d87-404f-bfda-123b80dcf3ed",
                        "name" => "COMPANY_CLIENT_UPDATE"
                    ],
                    [
                        "id" => 159,
                        "key" => "310239c1-6caa-435f-beb2-3d77b081d5e7",
                        "name" => "COMPANY_CLIENT_STATUS"
                    ],
                    [
                        "id" => 160,
                        "key" => "1c119d97-7590-440a-a4b7-d3a15e4c19bb",
                        "name" => "CLIENT_BANKS_GET"
                    ],
                    [
                        "id" => 161,
                        "key" => "78789b9f-89ae-452a-bece-9293737cf05d",
                        "name" => "CLIENT_BANK_ADD"
                    ],
                    [
                        "id" => 162,
                        "key" => "f0da33b4-9a38-4d9a-b37e-5d41f7bd66a1",
                        "name" => "CLIENT_BANK_UPDATE"
                    ],
                    [
                        "id" => 163,
                        "key" => "78784eb1-5c46-432e-ad17-30a1dbadb1a8",
                        "name" => "CLIENT_BANK_DELETE"
                    ]
                ],
                "id" => 15,
                "key" => "11d6947a-eb01-40fe-ae3f-9a5eb3e24eb0",
                "name" => "Client Company",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur depresiasi",
                "feature" => [
                    [
                        "id" => 169,
                        "key" => "06d410ef-dee4-4c7b-bf31-e66ad0ae5067",
                        "name" => "DEPRECIATIONS_GET"
                    ],
                    [
                        "id" => 170,
                        "key" => "4975553e-e021-445c-a3d1-10a8b8e7861e",
                        "name" => "DEPRECIATION_ADD"
                    ],
                    [
                        "id" => 171,
                        "key" => "f6da9391-0246-469d-8ee0-0ae39fa21035",
                        "name" => "DEPRECIATION_UPDATE"
                    ],
                    [
                        "id" => 172,
                        "key" => "c3d02d64-1f39-4fec-8f35-e6c31263521e",
                        "name" => "DEPRECIATION_DELETE"
                    ]
                ],
                "id" => 17,
                "key" => "7c612be1-9a63-4a1f-afda-cf9c3a4aea39",
                "name" => "Depreciation",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur module",
                "feature" => [
                    [
                        "id" => 179,
                        "key" => "20fd544a-da26-4485-ad7b-6425175e676a",
                        "name" => "MODULES_GET"
                    ],
                    [
                        "id" => 180,
                        "key" => "a79e06a6-7bf0-4258-9bb2-0a2d848750b3",
                        "name" => "MODULE_ADD"
                    ],
                    [
                        "id" => 181,
                        "key" => "a7f9c7f5-4b40-4f89-a10a-f5cc31f3411b",
                        "name" => "MODULE_UPDATE"
                    ],
                    [
                        "id" => 182,
                        "key" => "7be3a723-eb06-4b84-ae02-f094d8595343",
                        "name" => "MODULE_DELETE"
                    ]
                ],
                "id" => 19,
                "key" => "ce4abd85-21b8-4aad-acd6-ffffa48c9347",
                "name" => "Module",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur kontrak",
                "feature" => [
                    [
                        "id" => 194,
                        "key" => "719e083d-9e0a-4862-b7f8-cc581f7a0303",
                        "name" => "CONTRACTS_GET"
                    ],
                    [
                        "id" => 195,
                        "key" => "8c3cd2b7-ba26-42e3-b096-924baf2c9e16",
                        "name" => "CONTRACT_GET"
                    ],
                    [
                        "id" => 196,
                        "key" => "706e63d0-6237-4624-a2e6-0ac00f92843b",
                        "name" => "CONTRACT_ADD"
                    ],
                    [
                        "id" => 197,
                        "key" => "1ad5167d-75e8-4720-95cb-3cec999f648f",
                        "name" => "CONTRACT_UPDATE"
                    ],
                    [
                        "id" => 198,
                        "key" => "b241f64d-d6d9-4329-9ae2-db1fd5fc8ff1",
                        "name" => "CONTRACT_DELETE"
                    ],
                    [
                        "id" => 199,
                        "key" => "8edab208-8cf6-4cfe-b4ae-5e2958606d57",
                        "name" => "CONTRACT_ACTIVE"
                    ],
                    [
                        "id" => 200,
                        "key" => "835c78e3-5b3e-4fd6-bd22-3ea3ee9aebc8",
                        "name" => "CONTRACT_DEACTIVE"
                    ],
                    [
                        "id" => 201,
                        "key" => "c999ec74-8996-4f5e-8498-10ceec7c0f66",
                        "name" => "CONTRACT_SERVICE_ITEM_ACTIVE"
                    ],
                    [
                        "id" => 202,
                        "key" => "969f1a28-8f23-4c69-b7cb-6bd33ebd7f6f",
                        "name" => "CONTRACT_SERVICE_ITEM_DEACTIVE"
                    ],
                    [
                        "id" => 203,
                        "key" => "2b9a4881-5404-415e-a57e-9324f1a5a193",
                        "name" => "CONTRACT_TYPES_GET"
                    ],
                    [
                        "id" => 204,
                        "key" => "55b3f452-0f0c-4759-bfa4-6d441a74f9a6",
                        "name" => "CONTRACT_TYPE_ADD"
                    ],
                    [
                        "id" => 205,
                        "key" => "af1d3753-4340-472d-98eb-43826b34a6e6",
                        "name" => "CONTRACT_TYPE_UPDATE"
                    ],
                    [
                        "id" => 206,
                        "key" => "c7371755-e05c-4033-9570-cceff6e1e476",
                        "name" => "CONTRACT_TYPE_DELETE"
                    ]
                ],
                "id" => 21,
                "key" => "5b3feb73-ea29-4d54-af65-d9ccef7bf1ff",
                "name" => "Contract",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur tentang role",
                "feature" => [
                    [
                        "id" => 173,
                        "key" => "373e0155-14d1-4fcd-ad96-5ec973b11e83",
                        "name" => "ROLES_GET"
                    ],
                    [
                        "id" => 174,
                        "key" => "439bf5a7-6502-4e33-b7d2-a9252a9e1a60",
                        "name" => "ROLE_GET"
                    ],
                    [
                        "id" => 176,
                        "key" => "59745622-9f6e-4d38-87bc-34d7f924b9c6",
                        "name" => "ROLE_ADD"
                    ],
                    [
                        "id" => 177,
                        "key" => "92c6c6d1-346e-440e-84bc-3f9f06bf8f35",
                        "name" => "ROLE_UPDATE"
                    ],
                    [
                        "id" => 178,
                        "key" => "a2c6cf19-d55c-4cde-9307-efd477b902b4",
                        "name" => "ROLE_DELETE"
                    ],
                    [
                        "id" => 220,
                        "key" => "bed624d9-c3c9-40d7-91c4-2d88f8eabcae",
                        "name" => "ROLE_USER_FEATURES_GET"
                    ]
                ],
                "id" => 22,
                "key" => "d7eb228a-2381-4a82-bf52-3142bc9a6a31",
                "name" => "Role",
                "status" => 1
            ],
            [
                "company_id" => 66,
                "description" => "Modul yang berisi fitur-fitur untuk company profile",
                "feature" => [
                    [
                        "id" => 209,
                        "key" => "8066c7cb-df25-4577-b84f-e087c77777b7",
                        "name" => "CAREER_ADD"
                    ],
                    [
                        "id" => 210,
                        "key" => "e529eb4d-dcc0-4a30-b4c1-525807737df7",
                        "name" => "CAREER_UPDATE"
                    ],
                    [
                        "id" => 211,
                        "key" => "0fbd618f-5727-445e-83b2-9093e681383e",
                        "name" => "CAREER_DELETE"
                    ],
                    [
                        "id" => 212,
                        "key" => "7a4e14a1-735c-4281-9a6a-0e56e5ca93c0",
                        "name" => "MESSAGES_GET"
                    ]
                ],
                "id" => 24,
                "key" => "33429eab-9dec-4d4b-bfcc-97be7201dd48",
                "name" => "Company Profile",
                "status" => 1
            ]
        ];
        return $data;
    }

    public function defaultDataFeatures()
    {
        $data = [
            [
                "id" => 10,
                "feature_id" => 107,
                "feature_key" => "b699dca3-9908-41f9-9583-e34f07f7e5c7",
                "name" => "AGENT_GET",
                "description" => "Fitur untuk mengambil detail data agent"
            ],
            [
                "id" => 11,
                "feature_id" => 108,
                "feature_key" => "35eaa6ba-26f7-4fb3-9c5f-70997bc7c7df",
                "name" => "AGENTS_GET",
                "description" => "Fitur untuk mengambil list agent"
            ],
            [
                "id" => 12,
                "feature_id" => 109,
                "feature_key" => "18e7b0ce-5381-4373-b900-bf1f1a2404a8",
                "name" => "AGENT_ADD",
                "description" => "Fitur untuk membuat agent baru"
            ],
            [
                "id" => 13,
                "feature_id" => 110,
                "feature_key" => "1162e26e-3c40-455d-80d7-30b8c2980636",
                "name" => "AGENT_UPDATE",
                "description" => "Fitur untuk memperbarui data agent"
            ],
            [
                "id" => 14,
                "feature_id" => 111,
                "feature_key" => "35389229-9026-4caf-b9d4-a63dd5a9c10d",
                "name" => "AGENT_PASSWORD_UPDATE",
                "description" => "Fitur untuk memperbarui password agent"
            ],
            [
                "id" => 15,
                "feature_id" => 112,
                "feature_key" => "285e11c1-973b-432f-8856-36deefc52f2e",
                "name" => "AGENT_STATUS",
                "description" => "Fitur untuk merubah status agent"
            ],
            [
                "id" => 17,
                "feature_id" => 114,
                "feature_key" => "7ffd9e74-5399-46d9-8d01-f80016c4f9f2",
                "name" => "REQUESTER_STATUS",
                "description" => "Fitur untuk merubah status requester"
            ],
            [
                "id" => 18,
                "feature_id" => 115,
                "feature_key" => "5454137c-9441-43cc-bce0-342a7856aa29",
                "name" => "REQUESTER_PASSWORD_UPDATE",
                "description" => "Fitur untuk merubah password requester"
            ],
            [
                "id" => 19,
                "feature_id" => 116,
                "feature_key" => "ebb04195-5e1b-4a4a-8dc6-fac8ba0c4648",
                "name" => "REQUESTER_UPDATE",
                "description" => "Fitur untuk memperbarui data requester"
            ],
            [
                "id" => 20,
                "feature_id" => 117,
                "feature_key" => "51d584e0-be3c-45c4-84e8-a5c7a10d6233",
                "name" => "REQUESTER_ADD",
                "description" => "Fitur untuk membuat requester baru"
            ],
            [
                "id" => 21,
                "feature_id" => 118,
                "feature_key" => "d05cd99c-3ee7-4cb9-ada9-7482a0aae2ff",
                "name" => "REQUESTER_GET",
                "description" => "Fitur untuk mengambil data detail requester"
            ],
            [
                "id" => 22,
                "feature_id" => 119,
                "feature_key" => "5607785a-f42e-4b09-a391-3f378868394c",
                "name" => "REQUESTERS_GET",
                "description" => "Fitur untuk mengambil list requester"
            ],
            [
                "id" => 31,
                "feature_id" => 132,
                "feature_key" => "7bf52f77-7a9d-4fb0-8b86-682b1c5afbd0",
                "name" => "AGENT_UPDATE_FEATURE",
                "description" => "Fitur untuk merubah akses fitur agent"
            ],
            [
                "id" => 32,
                "feature_id" => 133,
                "feature_key" => "68c0bc7a-2366-49f7-8579-e82fee9a5a1e",
                "name" => "REQUESTER_UPDATE_FEATURE",
                "description" => "Fitur untuk merubah akses fitur requester"
            ],
            [
                "id" => 33,
                "feature_id" => 134,
                "feature_key" => "e7a75e1a-84bf-4cd6-9b90-959e1deabf57",
                "name" => "AGENT_GROUPS_GET",
                "description" => "Fitur untuk mengambil list group agent"
            ],
            [
                "id" => 34,
                "feature_id" => 135,
                "feature_key" => "74df6b7f-f7e3-43bc-989a-71dbcd08d747",
                "name" => "AGENT_GROUP_ADD",
                "description" => "Fitur untuk membuat group agent"
            ],
            [
                "id" => 35,
                "feature_id" => 136,
                "feature_key" => "42a27715-5217-4906-b486-5dae631105e1",
                "name" => "AGENT_GROUP_GET",
                "description" => "Fitur untuk mengambil data detail group agent"
            ],
            [
                "id" => 36,
                "feature_id" => 137,
                "feature_key" => "3ddf2248-6e55-4a41-a1b9-a6e1f3af6036",
                "name" => "AGENT_GROUP_UPDATE",
                "description" => "Fitur untuk memperbarui data group agent"
            ],
            [
                "id" => 37,
                "feature_id" => 138,
                "feature_key" => "0fc381b0-4c43-43d4-b964-17888b861227",
                "name" => "AGENT_GROUP_DELETE",
                "description" => "Fitur untuk menghapus group agent"
            ],
            [
                "id" => 38,
                "feature_id" => 139,
                "feature_key" => "bedca85a-1073-4c36-b8d0-86b05a79451f",
                "name" => "REQUESTER_GROUPS_GET",
                "description" => "Fitur untuk mengambil list group requester"
            ],
            [
                "id" => 39,
                "feature_id" => 140,
                "feature_key" => "dbee04f9-21d4-499c-9b77-6ae0cf248159",
                "name" => "REQUESTER_GROUP_ADD",
                "description" => "Fitur untuk membuat group requester"
            ],
            [
                "id" => 40,
                "feature_id" => 141,
                "feature_key" => "a288ea00-b73e-4816-9096-21b5ac3bd3b8",
                "name" => "REQUESTER_GROUP_GET",
                "description" => "Fitur untuk mengambil data detail group requester"
            ],
            [
                "id" => 41,
                "feature_id" => 142,
                "feature_key" => "502f71d7-b769-4db3-8df6-536026d89bd8",
                "name" => "REQUESTER_GROUP_UPDATE",
                "description" => "Fitur untuk memperbarui data group requester"
            ],
            [
                "id" => 42,
                "feature_id" => 143,
                "feature_key" => "14bb5610-abb8-4a02-ae21-58dfd7a222d8",
                "name" => "REQUESTER_GROUP_DELETE",
                "description" => "Fitur untuk menghapus group requester"
            ],
            [
                "id" => 43,
                "feature_id" => 144,
                "feature_key" => "499e1844-d4dd-4725-b98a-27523e0994df",
                "name" => "MAIN_COMPANY_GET",
                "description" => "Fitur untuk mengambil data detail perusahaan MIG"
            ],
            [
                "id" => 44,
                "feature_id" => 145,
                "feature_key" => "b9bba2f3-8cf3-4d4c-b0c2-937decba72c7",
                "name" => "MAIN_COMPANY_UPDATE",
                "description" => "Fitur untuk memperbarui data perusahaan MIG"
            ],
            [
                "id" => 45,
                "feature_id" => 146,
                "feature_key" => "012ce495-a6fb-4c9f-846c-53341a3ddc63",
                "name" => "MAIN_BANKS_GET",
                "description" => "Fitur untuk mengambil list Bank MIG"
            ],
            [
                "id" => 46,
                "feature_id" => 147,
                "feature_key" => "11eef373-28ba-444e-ab59-542b13a72d56",
                "name" => "MAIN_BANK_ADD",
                "description" => "Fitur untuk membuat Bank MIG"
            ],
            [
                "id" => 47,
                "feature_id" => 148,
                "feature_key" => "4947152c-658c-4df7-9fd7-03421e99d7c0",
                "name" => "MAIN_BANK_UPDATE",
                "description" => "Fitur untuk memperbarui Bank MIG"
            ],
            [
                "id" => 48,
                "feature_id" => 149,
                "feature_key" => "044f5d80-91d0-43a3-a283-24f44f045b03",
                "name" => "MAIN_BANK_DELETE",
                "description" => "Fitur untuk menghapus Bank MIG"
            ],
            [
                "id" => 49,
                "feature_id" => 150,
                "feature_key" => "67d07ba8-f37c-4649-8642-a17807863b35",
                "name" => "COMPANY_BRANCHS_GET",
                "description" => "Fitur untuk mengambil list perusahaan cabang MIG"
            ],
            [
                "id" => 50,
                "feature_id" => 151,
                "feature_key" => "f419c740-1316-431d-8347-9446e1b92159",
                "name" => "COMPANY_BRANCH_GET",
                "description" => "Fitur untuk mengambil data detail perusahaan cabang MIG"
            ],
            [
                "id" => 51,
                "feature_id" => 152,
                "feature_key" => "8ff00868-1df4-41fc-b402-377a94aaec09",
                "name" => "COMPANY_BRANCH_ADD",
                "description" => "Fitur untuk membuat perusahaan cabang MIG"
            ],
            [
                "id" => 52,
                "feature_id" => 153,
                "feature_key" => "233d1e25-d083-49c3-88a8-bbcc5b235ca3",
                "name" => "COMPANY_BRANCH_UPDATE",
                "description" => "Fitur untuk memperbarui perusahaan cabang MIG"
            ],
            [
                "id" => 53,
                "feature_id" => 154,
                "feature_key" => "f311acf9-0d9c-481b-ba36-5ca512ea1715",
                "name" => "COMPANY_BRANCH_STATUS",
                "description" => "Fitur untuk merubah aktivasi status perusahaan cabang MIG"
            ],
            [
                "id" => 54,
                "feature_id" => 155,
                "feature_key" => "8c8eba57-f0b2-431a-9a34-398e32d4d174",
                "name" => "COMPANY_CLIENTS_GET",
                "description" => "Fitur untuk mengambil list data perusahaan client"
            ],
            [
                "id" => 55,
                "feature_id" => 156,
                "feature_key" => "74f590ef-5dde-44d4-be74-549884482b31",
                "name" => "COMPANY_CLIENT_GET",
                "description" => "Fitur untuk mengambil detail data perusahaan client"
            ],
            [
                "id" => 56,
                "feature_id" => 157,
                "feature_key" => "b83ff8df-f7b2-493b-ad59-7a54670da8d6",
                "name" => "COMPANY_CLIENT_ADD",
                "description" => "Fitur untuk membuat perusahaan client"
            ],
            [
                "id" => 57,
                "feature_id" => 158,
                "feature_key" => "604b1d8e-5d87-404f-bfda-123b80dcf3ed",
                "name" => "COMPANY_CLIENT_UPDATE",
                "description" => "Fitur untuk memperbarui perusahaan client"
            ],
            [
                "id" => 58,
                "feature_id" => 159,
                "feature_key" => "310239c1-6caa-435f-beb2-3d77b081d5e7",
                "name" => "COMPANY_CLIENT_STATUS",
                "description" => "Fitur untuk merubah aktivasi status perusahaan client"
            ],
            [
                "id" => 59,
                "feature_id" => 160,
                "feature_key" => "1c119d97-7590-440a-a4b7-d3a15e4c19bb",
                "name" => "CLIENT_BANKS_GET",
                "description" => "Fitur untuk mengambil list bank perusahaan client"
            ],
            [
                "id" => 60,
                "feature_id" => 161,
                "feature_key" => "78789b9f-89ae-452a-bece-9293737cf05d",
                "name" => "CLIENT_BANK_ADD",
                "description" => "Fitur untuk membuat bank perusahaan client"
            ],
            [
                "id" => 61,
                "feature_id" => 162,
                "feature_key" => "f0da33b4-9a38-4d9a-b37e-5d41f7bd66a1",
                "name" => "CLIENT_BANK_UPDATE",
                "description" => "Fitur untuk memperbarui bank perusahaan client"
            ],
            [
                "id" => 62,
                "feature_id" => 163,
                "feature_key" => "78784eb1-5c46-432e-ad17-30a1dbadb1a8",
                "name" => "CLIENT_BANK_DELETE",
                "description" => "Fitur untuk menghapus bank perusahaan client"
            ],
            [
                "id" => 67,
                "feature_id" => 169,
                "feature_key" => "06d410ef-dee4-4c7b-bf31-e66ad0ae5067",
                "name" => "DEPRECIATIONS_GET",
                "description" => "Fitur untuk mengambil list depresiasi"
            ],
            [
                "id" => 68,
                "feature_id" => 170,
                "feature_key" => "4975553e-e021-445c-a3d1-10a8b8e7861e",
                "name" => "DEPRECIATION_ADD",
                "description" => "Fitur untuk membuat depresiasi"
            ],
            [
                "id" => 69,
                "feature_id" => 171,
                "feature_key" => "f6da9391-0246-469d-8ee0-0ae39fa21035",
                "name" => "DEPRECIATION_UPDATE",
                "description" => "Fitur untuk memperbarui depresiasi"
            ],
            [
                "id" => 70,
                "feature_id" => 172,
                "feature_key" => "c3d02d64-1f39-4fec-8f35-e6c31263521e",
                "name" => "DEPRECIATION_DELETE",
                "description" => "Fitur untuk menghapus depresiasi"
            ],
            [
                "id" => 71,
                "feature_id" => 173,
                "feature_key" => "373e0155-14d1-4fcd-ad96-5ec973b11e83",
                "name" => "ROLES_GET",
                "description" => "Fitur untuk mengambil list role"
            ],
            [
                "id" => 72,
                "feature_id" => 174,
                "feature_key" => "439bf5a7-6502-4e33-b7d2-a9252a9e1a60",
                "name" => "ROLE_GET",
                "description" => "Fitur untuk mengambil detail fitur role"
            ],
            [
                "id" => 73,
                "feature_id" => 175,
                "feature_key" => "c9e4022d-d741-413e-ba13-438f22ccbd4b",
                "name" => "ROLE_USERS_GET",
                "description" => "Fitur untuk mengambil detail nama user dari role tertentu"
            ],
            [
                "id" => 74,
                "feature_id" => 176,
                "feature_key" => "59745622-9f6e-4d38-87bc-34d7f924b9c6",
                "name" => "ROLE_ADD",
                "description" => "Fitur untuk membuat role"
            ],
            [
                "id" => 75,
                "feature_id" => 177,
                "feature_key" => "92c6c6d1-346e-440e-84bc-3f9f06bf8f35",
                "name" => "ROLE_UPDATE",
                "description" => "Fitur untuk memperbarui role"
            ],
            [
                "id" => 76,
                "feature_id" => 178,
                "feature_key" => "a2c6cf19-d55c-4cde-9307-efd477b902b4",
                "name" => "ROLE_DELETE",
                "description" => "Fitur untuk menghapus role"
            ],
            [
                "id" => 77,
                "feature_id" => 179,
                "feature_key" => "20fd544a-da26-4485-ad7b-6425175e676a",
                "name" => "MODULES_GET",
                "description" => "Fitur untuk mengambil list module"
            ],
            [
                "id" => 78,
                "feature_id" => 180,
                "feature_key" => "a79e06a6-7bf0-4258-9bb2-0a2d848750b3",
                "name" => "MODULE_ADD",
                "description" => "Fitur untuk membuat module"
            ],
            [
                "id" => 79,
                "feature_id" => 181,
                "feature_key" => "a7f9c7f5-4b40-4f89-a10a-f5cc31f3411b",
                "name" => "MODULE_UPDATE",
                "description" => "Fitur untuk memperbarui module"
            ],
            [
                "id" => 80,
                "feature_id" => 182,
                "feature_key" => "7be3a723-eb06-4b84-ae02-f094d8595343",
                "name" => "MODULE_DELETE",
                "description" => "Fitur untuk menghapus module"
            ],
            [
                "id" => 81,
                "feature_id" => 183,
                "feature_key" => "903df1f9-6f68-4aa1-a52b-e2dcd1b8e6c7",
                "name" => "SERVICE_CATEGORIES_GET",
                "description" => "Fitur untuk mengambil list service kategori"
            ],
            [
                "id" => 82,
                "feature_id" => 184,
                "feature_key" => "0dc602f7-a6c8-41c9-b3bf-941b710d109b",
                "name" => "SERVICE_CATEGORY_ADD",
                "description" => "Fitur untuk membuat service kategori"
            ],
            [
                "id" => 83,
                "feature_id" => 185,
                "feature_key" => "e95570cc-e1f7-4931-bd6e-6340e3130707",
                "name" => "SERVICE_CATEGORY_UPDATE",
                "description" => "Fitur untuk memperbarui service kategori"
            ],
            [
                "id" => 84,
                "feature_id" => 186,
                "feature_key" => "2a1669a1-f8dd-4f93-90d5-6a4c53573e28",
                "name" => "SERVICE_CATEGORY_DELETE",
                "description" => "Fitur untuk menghapus service kategori"
            ],
            [
                "id" => 85,
                "feature_id" => 187,
                "feature_key" => "de2054aa-c6a9-4fe7-96ff-40c59844f223",
                "name" => "SERVICE_ITEMS_GET",
                "description" => "Fitur untuk mengambil list service item"
            ],
            [
                "id" => 86,
                "feature_id" => 188,
                "feature_key" => "313766ac-666e-4fca-a2e8-35fdcbaa7f84",
                "name" => "SERVICE_ITEM_GET",
                "description" => "Fitur untuk mengambil detail data service item"
            ],
            [
                "id" => 87,
                "feature_id" => 189,
                "feature_key" => "17d42de5-9c28-4b25-baa4-fce2764c46ad",
                "name" => "SERVICE_ITEM_ADD",
                "description" => "Fitur untuk membuat service item"
            ],
            [
                "id" => 88,
                "feature_id" => 190,
                "feature_key" => "6a9438dc-4421-4e64-b369-f50ba41c9e60",
                "name" => "SERVICE_ITEM_UPDATE",
                "description" => "Fitur untuk memperbarui service item"
            ],
            [
                "id" => 89,
                "feature_id" => 191,
                "feature_key" => "90d78df5-de98-4bd5-95ab-ffc22fda4bb3",
                "name" => "SERVICE_ITEM_DELETE",
                "description" => "Fitur untuk menghapus service item"
            ],
            [
                "id" => 90,
                "feature_id" => 192,
                "feature_key" => "821c1a53-6b76-4f2b-8197-55d462794889",
                "name" => "SERVICE_ITEM_PUBLISH",
                "description" => "Fitur untuk merubah status publikasi service item"
            ],
            [
                "id" => 91,
                "feature_id" => 193,
                "feature_key" => "c64c4be9-187b-4083-9e31-0190778cfd08",
                "name" => "SERVICE_ITEM_DEPUBLISH",
                "description" => "Fitur untuk merubah status depublikasi service item"
            ],
            [
                "id" => 92,
                "feature_id" => 194,
                "feature_key" => "719e083d-9e0a-4862-b7f8-cc581f7a0303",
                "name" => "CONTRACTS_GET",
                "description" => "Fitur untuk mengambil list contract"
            ],
            [
                "id" => 93,
                "feature_id" => 195,
                "feature_key" => "8c3cd2b7-ba26-42e3-b096-924baf2c9e16",
                "name" => "CONTRACT_GET",
                "description" => "Fitur untuk mengambil detail data contract"
            ],
            [
                "id" => 94,
                "feature_id" => 196,
                "feature_key" => "706e63d0-6237-4624-a2e6-0ac00f92843b",
                "name" => "CONTRACT_ADD",
                "description" => "Fitur untuk membuat contract"
            ],
            [
                "id" => 95,
                "feature_id" => 197,
                "feature_key" => "1ad5167d-75e8-4720-95cb-3cec999f648f",
                "name" => "CONTRACT_UPDATE",
                "description" => "Fitur untuk memperbarui contract"
            ],
            [
                "id" => 96,
                "feature_id" => 198,
                "feature_key" => "b241f64d-d6d9-4329-9ae2-db1fd5fc8ff1",
                "name" => "CONTRACT_DELETE",
                "description" => "Fitur untuk menghapus contract"
            ],
            [
                "id" => 97,
                "feature_id" => 199,
                "feature_key" => "8edab208-8cf6-4cfe-b4ae-5e2958606d57",
                "name" => "CONTRACT_ACTIVE",
                "description" => "Fitur untuk merubah status aktif contract"
            ],
            [
                "id" => 98,
                "feature_id" => 200,
                "feature_key" => "835c78e3-5b3e-4fd6-bd22-3ea3ee9aebc8",
                "name" => "CONTRACT_DEACTIVE",
                "description" => "Fitur untuk merubah status non aktif contract"
            ],
            [
                "id" => 99,
                "feature_id" => 201,
                "feature_key" => "c999ec74-8996-4f5e-8498-10ceec7c0f66",
                "name" => "CONTRACT_SERVICE_ITEM_ACTIVE",
                "description" => "Fitur untuk merubah status aktif contract service item"
            ],
            [
                "id" => 100,
                "feature_id" => 202,
                "feature_key" => "969f1a28-8f23-4c69-b7cb-6bd33ebd7f6f",
                "name" => "CONTRACT_SERVICE_ITEM_DEACTIVE",
                "description" => "Fitur untuk merubah status non aktif contract service item"
            ],
            [
                "id" => 101,
                "feature_id" => 203,
                "feature_key" => "2b9a4881-5404-415e-a57e-9324f1a5a193",
                "name" => "CONTRACT_TYPES_GET",
                "description" => "Fitur untuk mengambil list tipe contract"
            ],
            [
                "id" => 102,
                "feature_id" => 204,
                "feature_key" => "55b3f452-0f0c-4759-bfa4-6d441a74f9a6",
                "name" => "CONTRACT_TYPE_ADD",
                "description" => "Fitur untuk membuat tipe contract"
            ],
            [
                "id" => 103,
                "feature_id" => 205,
                "feature_key" => "af1d3753-4340-472d-98eb-43826b34a6e6",
                "name" => "CONTRACT_TYPE_UPDATE",
                "description" => "Fitur untuk memperbarui tipe contract"
            ],
            [
                "id" => 104,
                "feature_id" => 206,
                "feature_key" => "c7371755-e05c-4033-9570-cceff6e1e476",
                "name" => "CONTRACT_TYPE_DELETE",
                "description" => "Fitur untuk menghapus tipe contract"
            ],
            [
                "id" => 106,
                "feature_id" => 209,
                "feature_key" => "8066c7cb-df25-4577-b84f-e087c77777b7",
                "name" => "CAREER_ADD",
                "description" => "Fitur untuk membuat career baru pada company profile"
            ],
            [
                "id" => 107,
                "feature_id" => 210,
                "feature_key" => "e529eb4d-dcc0-4a30-b4c1-525807737df7",
                "name" => "CAREER_UPDATE",
                "description" => "Fitur untuk memperbarui career pada company profile"
            ],
            [
                "id" => 108,
                "feature_id" => 211,
                "feature_key" => "0fbd618f-5727-445e-83b2-9093e681383e",
                "name" => "CAREER_DELETE",
                "description" => "Fitur untuk menghapus career pada company profile"
            ],
            [
                "id" => 109,
                "feature_id" => 212,
                "feature_key" => "7a4e14a1-735c-4281-9a6a-0e56e5ca93c0",
                "name" => "MESSAGES_GET",
                "description" => "Fitur untuk mengambil list message dari company profile"
            ],
            [
                "id" => 111,
                "feature_id" => 218,
                "feature_key" => "46c680e6-f260-407e-b485-ae9ef9835787",
                "name" => "tes",
                "description" => "tes"
            ],
            [
                "id" => 112,
                "feature_id" => 220,
                "feature_key" => "bed624d9-c3c9-40d7-91c4-2d88f8eabcae",
                "name" => "ROLE_USER_FEATURES_GET",
                "description" => "Fitur untuk mengambil detail user dan fitur dari role tertentu"
            ]
        ];
        return $data;
    }
}
