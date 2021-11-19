<?php

use App\Module;
use App\AccessFeature;
use Illuminate\Database\Seeder;

class AccessFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function makeBulkDataFeatures()
    {
        $features = $this->defaultDataFeatures();
        foreach($features as $feature){
            $new_feature = new AccessFeature;
            $new_feature->name = $feature['name'];
            $new_feature->description = $feature['description'];
            $new_feature->save();
        }
    }

    private function makeBulkDataModules()
    {
        $default_data_modules = $this->defaultDataModules();
        $access_features = AccessFeature::select('id', 'name')->get();
        foreach($default_data_modules as $module){
            $features = [];
            if(count($module['features'])){
                foreach($module['features'] as $feature){
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
        $this->makeBulkDataFeatures();
        $this->makeBulkDataModules();
    }

    public function defaultDataFeatures()
    {
        $data = [
            [
                "name" => "AGENT_GET",
                "description" => "Fitur untuk mengambil detail data agent"
            ],
            [
                "name" => "AGENTS_GET",
                "description" => "Fitur untuk mengambil list agent"
            ],
            [
                "name" => "AGENT_ADD",
                "description" => "Fitur untuk membuat agent baru"
            ],
            [
                "name" => "AGENT_UPDATE",
                "description" => "Fitur untuk memperbarui data agent"
            ],
            [
                "name" => "AGENT_PASSWORD_UPDATE",
                "description" => "Fitur untuk memperbarui password agent"
            ],
            [
                "name" => "AGENT_STATUS",
                "description" => "Fitur untuk merubah status agent"
            ],
            [
                "name" => "AGENT_RELATIONSHIP_INVENTORY_GET",
                "description" => "Fitur untuk AGENT_RELATIONSHIP_INVENTORY_GET"
            ],
            [
                "name" => "REQUESTER_STATUS",
                "description" => "Fitur untuk merubah status requester"
            ],
            [
                "name" => "REQUESTER_PASSWORD_UPDATE",
                "description" => "Fitur untuk merubah password requester"
            ],
            [
                "name" => "REQUESTER_UPDATE",
                "description" => "Fitur untuk memperbarui data requester"
            ],
            [
                "name" => "REQUESTER_ADD",
                "description" => "Fitur untuk membuat requester baru"
            ],
            [
                "name" => "REQUESTER_GET",
                "description" => "Fitur untuk mengambil data detail requester"
            ],
            [
                "name" => "REQUESTERS_GET",
                "description" => "Fitur untuk mengambil list requester"
            ],
            [
                "name" => "AGENT_UPDATE_FEATURE",
                "description" => "Fitur untuk merubah akses fitur agent"
            ],
            [
                "name" => "REQUESTER_UPDATE_FEATURE",
                "description" => "Fitur untuk merubah akses fitur requester"
            ],
            [
                "name" => "REQUESTER_RELATIONSHIP_INVENTORY_GET",
                "description" => "Fitur untuk REQUESTER_RELATIONSHIP_INVENTORY_GET"
            ],
            [
                "name" => "AGENT_GROUPS_GET",
                "description" => "Fitur untuk mengambil list group agent"
            ],
            [
                "name" => "AGENT_GROUP_ADD",
                "description" => "Fitur untuk membuat group agent"
            ],
            [
                "name" => "AGENT_GROUP_GET",
                "description" => "Fitur untuk mengambil data detail group agent"
            ],
            [
                "name" => "AGENT_GROUP_UPDATE",
                "description" => "Fitur untuk memperbarui data group agent"
            ],
            [
                "name" => "AGENT_GROUP_DELETE",
                "description" => "Fitur untuk menghapus group agent"
            ],
            [
                "name" => "REQUESTER_GROUPS_GET",
                "description" => "Fitur untuk mengambil list group requester"
            ],
            [
                "name" => "REQUESTER_GROUP_ADD",
                "description" => "Fitur untuk membuat group requester"
            ],
            [
                "name" => "REQUESTER_GROUP_GET",
                "description" => "Fitur untuk mengambil data detail group requester"
            ],
            [
                "name" => "REQUESTER_GROUP_UPDATE",
                "description" => "Fitur untuk memperbarui data group requester"
            ],
            [
                "name" => "REQUESTER_GROUP_DELETE",
                "description" => "Fitur untuk menghapus group requester"
            ],
            [
                "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET",
                "description" => "Fitur untuk COMPANY_RELATIONSHIP_INVENTORY_GET"
            ],
            [
                "name" => "MAIN_COMPANY_GET",
                "description" => "Fitur untuk mengambil data detail perusahaan MIG"
            ],
            [
                "name" => "MAIN_COMPANY_UPDATE",
                "description" => "Fitur untuk memperbarui data perusahaan MIG"
            ],
            [
                "name" => "MAIN_BANKS_GET",
                "description" => "Fitur untuk mengambil list Bank MIG"
            ],
            [
                "name" => "MAIN_BANK_ADD",
                "description" => "Fitur untuk membuat Bank MIG"
            ],
            [
                "name" => "MAIN_BANK_UPDATE",
                "description" => "Fitur untuk memperbarui Bank MIG"
            ],
            [
                "name" => "MAIN_BANK_DELETE",
                "description" => "Fitur untuk menghapus Bank MIG"
            ],
            [
                "name" => "COMPANY_BRANCHS_GET",
                "description" => "Fitur untuk mengambil list perusahaan cabang MIG"
            ],
            [
                "name" => "COMPANY_BRANCH_GET",
                "description" => "Fitur untuk mengambil data detail perusahaan cabang MIG"
            ],
            [
                "name" => "COMPANY_BRANCH_ADD",
                "description" => "Fitur untuk membuat perusahaan cabang MIG"
            ],
            [
                "name" => "COMPANY_BRANCH_UPDATE",
                "description" => "Fitur untuk memperbarui perusahaan cabang MIG"
            ],
            [
                "name" => "COMPANY_BRANCH_STATUS",
                "description" => "Fitur untuk merubah aktivasi status perusahaan cabang MIG"
            ],
            [
                "name" => "COMPANY_CLIENTS_GET",
                "description" => "Fitur untuk mengambil list data perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_GET",
                "description" => "Fitur untuk mengambil detail data perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_ADD",
                "description" => "Fitur untuk membuat perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_UPDATE",
                "description" => "Fitur untuk memperbarui perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_STATUS",
                "description" => "Fitur untuk merubah aktivasi status perusahaan client"
            ],
            [
                "name" => "CLIENT_BANKS_GET",
                "description" => "Fitur untuk mengambil list bank perusahaan client"
            ],
            [
                "name" => "CLIENT_BANK_ADD",
                "description" => "Fitur untuk membuat bank perusahaan client"
            ],
            [
                "name" => "CLIENT_BANK_UPDATE",
                "description" => "Fitur untuk memperbarui bank perusahaan client"
            ],
            [
                "name" => "CLIENT_BANK_DELETE",
                "description" => "Fitur untuk menghapus bank perusahaan client"
            ],
            [
                "name" => "DEPRECIATIONS_GET",
                "description" => "Fitur untuk mengambil list depresiasi"
            ],
            [
                "name" => "DEPRECIATION_ADD",
                "description" => "Fitur untuk membuat depresiasi"
            ],
            [
                "name" => "DEPRECIATION_UPDATE",
                "description" => "Fitur untuk memperbarui depresiasi"
            ],
            [
                "name" => "DEPRECIATION_DELETE",
                "description" => "Fitur untuk menghapus depresiasi"
            ],
            [
                "name" => "ROLES_GET",
                "description" => "Fitur untuk mengambil list role"
            ],
            [
                "name" => "ROLE_GET",
                "description" => "Fitur untuk mengambil detail fitur role"
            ],
            [
                "name" => "ROLE_USERS_GET",
                "description" => "Fitur untuk mengambil detail nama user dari role tertentu"
            ],
            [
                "name" => "ROLE_USER_FEATURES_GET",
                "description" => "Fitur untuk mengambil detail user dan fitur dari role tertentu"
            ],
            [
                "name" => "ROLE_ADD",
                "description" => "Fitur untuk membuat role"
            ],
            [
                "name" => "ROLE_UPDATE",
                "description" => "Fitur untuk memperbarui role"
            ],
            [
                "name" => "ROLE_DELETE",
                "description" => "Fitur untuk menghapus role"
            ],
            [
                "name" => "MODULES_GET",
                "description" => "Fitur untuk mengambil list module"
            ],
            [
                "name" => "MODULE_ADD",
                "description" => "Fitur untuk membuat module"
            ],
            [
                "name" => "MODULE_UPDATE",
                "description" => "Fitur untuk memperbarui module"
            ],
            [
                "name" => "MODULE_DELETE",
                "description" => "Fitur untuk menghapus module"
            ],
            [
                "name" => "ASSETS_GET",
                "description" => "Fitur untuk ASSETS_GET"
            ],
            [
                "name" => "ASSET_GET",
                "description" => "Fitur untuk ASSET_GET"
            ],
            [
                "name" => "ASSET_ADD",
                "description" => "Fitur untuk ASSET_ADD"
            ],
            [
                "name" => "ASSET_UPDATE",
                "description" => "Fitur untuk ASSET_UPDATE"
            ],
            [
                "name" => "ASSET_DELETE",
                "description" => "Fitur untuk ASSET_DELETE"
            ],
            [
                "name" => "RELATIONSHIP_ASSETS_GET",
                "description" => "Fitur untuk RELATIONSHIP_ASSETS_GET"
            ],
            [
                "name" => "RELATIONSHIP_ASSET_GET",
                "description" => "Fitur untuk RELATIONSHIP_ASSET_GET"
            ],
            [
                "name" => "RELATIONSHIP_ASSET_ADD",
                "description" => "Fitur untuk RELATIONSHIP_ASSET_ADD"
            ],
            [
                "name" => "RELATIONSHIP_ASSET_UPDATE",
                "description" => "Fitur untuk RELATIONSHIP_ASSET_UPDATE"
            ],
            [
                "name" => "RELATIONSHIP_ASSET_DELETE",
                "description" => "Fitur untuk RELATIONSHIP_ASSET_DELETE"
            ],
            [
                "name" => "MODELS_GET",
                "description" => "Fitur untuk MODELS_GET"
            ],
            [
                "name" => "MODEL_GET",
                "description" => "Fitur untuk MODEL_GET"
            ],
            [
                "name" => "MODEL_ADD",
                "description" => "Fitur untuk MODEL_ADD"
            ],
            [
                "name" => "MODEL_UPDATE",
                "description" => "Fitur untuk MODEL_UPDATE"
            ],
            [
                "name" => "MODEL_DELETE",
                "description" => "Fitur untuk MODEL_DELETE"
            ],
            [
                "name" => "INVENTORIES_GET",
                "description" => "Fitur untuk INVENTORIES_GET"
            ],
            [
                "name" => "INVENTORY_GET",
                "description" => "Fitur untuk INVENTORY_GET"
            ],
            [
                "name" => "INVENTORY_ADD",
                "description" => "Fitur untuk INVENTORY_ADD"
            ],
            [
                "name" => "INVENTORY_UPDATE",
                "description" => "Fitur untuk INVENTORY_UPDATE"
            ],
            [
                "name" => "INVENTORY_DELETE",
                "description" => "Fitur untuk INVENTORY_DELETE"
            ],
            [
                "name" => "INVENTORY_NOTES_ADD",
                "description" => "Fitur untuk INVENTORY_NOTES_ADD"
            ],
            [
                "name" => "INVENTORY_STATUS_CONDITION",
                "description" => "Fitur untuk INVENTORY_STATUS_CONDITION"
            ],
            [
                "name" => "INVENTORY_STATUS_USAGE",
                "description" => "Fitur untuk INVENTORY_STATUS_USAGE"
            ],
            [
                "name" => "INVENTORY_PARTS_ADD",
                "description" => "Fitur untuk INVENTORY_PARTS_ADD"
            ],
            [
                "name" => "INVENTORY_PART_REMOVE",
                "description" => "Fitur untuk INVENTORY_PART_REMOVE"
            ],
            [
                "name" => "INVENTORY_PART_REPLACE",
                "description" => "Fitur untuk INVENTORY_PART_REPLACE"
            ],
            [
                "name" => "INVENTORY_LOG_GET",
                "description" => "Fitur untuk INVENTORY_LOG_GET"
            ],
            [
                "name" => "RELATIONSHIP_INVENTORY_GET",
                "description" => "Fitur untuk RELATIONSHIP_INVENTORY_GET"
            ],
            [
                "name" => "RELATIONSHIP_INVENTORY_ADD",
                "description" => "Fitur untuk RELATIONSHIP_INVENTORY_ADD"
            ],
            [
                "name" => "RELATIONSHIP_INVENTORY_UPDATE",
                "description" => "Fitur untuk RELATIONSHIP_INVENTORY_UPDATE"
            ],
            [
                "name" => "RELATIONSHIP_INVENTORY_DELETE",
                "description" => "Fitur untuk RELATIONSHIP_INVENTORY_DELETE"
            ],
            [
                "name" => "VENDORS_GET",
                "description" => "Fitur untuk VENDORS_GET"
            ],
            [
                "name" => "VENDOR_GET",
                "description" => "Fitur untuk VENDOR_GET"
            ],
            [
                "name" => "VENDOR_ADD",
                "description" => "Fitur untuk VENDOR_ADD"
            ],
            [
                "name" => "VENDOR_UPDATE",
                "description" => "Fitur untuk VENDOR_UPDATE"
            ],
            [
                "name" => "VENDOR_DELETE",
                "description" => "Fitur untuk VENDOR_DELETE"
            ],
            [
                "name" => "MANUFACTURERS_GET",
                "description" => "Fitur untuk MANUFACTURERS_GET"
            ],
            [
                "name" => "MANUFACTURER_ADD",
                "description" => "Fitur untuk MANUFACTURER_ADD"
            ],
            [
                "name" => "MANUFACTURER_UPDATE",
                "description" => "Fitur untuk MANUFACTURER_UPDATE"
            ],
            [
                "name" => "MANUFACTURER_DELETE",
                "description" => "Fitur untuk MANUFACTURER_DELETE"
            ],
            [
                "name" => "RELATIONSHIPS_GET",
                "description" => "Fitur untuk RELATIONSHIPS_GET"
            ],
            [
                "name" => "RELATIONSHIP_ADD",
                "description" => "Fitur untuk RELATIONSHIP_ADD"
            ],
            [
                "name" => "RELATIONSHIP_UPDATE",
                "description" => "Fitur untuk RELATIONSHIP_UPDATE"
            ],
            [
                "name" => "RELATIONSHIP_DELETE",
                "description" => "Fitur untuk RELATIONSHIP_DELETE"
            ],
            [
                "name" => "CLIENT_TICKETS_GET",
                "description" => "Fitur untuk CLIENT_TICKETS_GET"
            ],
            [
                "name" => "CLIENT_CLOSED_TICKETS_GET",
                "description" => "Fitur untuk CLIENT_CLOSED_TICKETS_GET"
            ],
            [
                "name" => "CLIENT_TICKET_GET",
                "description" => "Fitur untuk CLIENT_TICKET_GET"
            ],
            [
                "name" => "CLIENT_CANCEL_TICKET",
                "description" => "Fitur untuk CLIENT_CANCEL_TICKET"
            ],
            [
                "name" => "CLIENT_TICKET_LOG_GET",
                "description" => "Fitur untuk CLIENT_TICKET_LOG_GET"
            ],
            [
                "name" => "CLIENT_TICKET_NOTE_ADD",
                "description" => "Fitur untuk CLIENT_TICKET_NOTE_ADD"
            ],
            [
                "name" => "CLIENT_TICKET_EXPORT",
                "description" => "Fitur untuk CLIENT_TICKET_EXPORT"
            ],
            [
                "name" => "TICKETS_GET",
                "description" => "Fitur untuk TICKETS_GET"
            ],
            [
                "name" => "CLOSED_TICKETS_GET",
                "description" => "Fitur untuk CLOSED_TICKETS_GET"
            ],
            [
                "name" => "TICKET_GET",
                "description" => "Fitur untuk TICKET_GET"
            ],
            [
                "name" => "TICKET_LOG_GET",
                "description" => "Fitur untuk TICKET_LOG_GET"
            ],
            [
                "name" => "TICKET_ADD",
                "description" => "Fitur untuk TICKET_ADD"
            ],
            [
                "name" => "TICKET_UPDATE",
                "description" => "Fitur untuk TICKET_UPDATE"
            ],
            [
                "name" => "TICKET_SET_STATUS",
                "description" => "Fitur untuk TICKET_SET_STATUS"
            ],
            [
                "name" => "TICKET_ASSIGN",
                "description" => "Fitur untuk TICKET_ASSIGN"
            ],
            [
                "name" => "TICKET_SET_ITEM",
                "description" => "Fitur untuk TICKET_SET_ITEM"
            ],
            [
                "name" => "TICKET_NOTE_ADD",
                "description" => "Fitur untuk TICKET_NOTE_ADD"
            ],
            [
                "name" => "TICKETS_EXPORT",
                "description" => "Fitur untuk TICKETS_EXPORT"
            ],
            [
                "name" => "TICKET_EXPORT",
                "description" => "Fitur untuk TICKET_EXPORT"
            ],
            [
                "name" => "SERVICE_CATEGORIES_GET",
                "description" => "Fitur untuk mengambil list service kategori"
            ],
            [
                "name" => "SERVICE_CATEGORY_ADD",
                "description" => "Fitur untuk membuat service kategori"
            ],
            [
                "name" => "SERVICE_CATEGORY_UPDATE",
                "description" => "Fitur untuk memperbarui service kategori"
            ],
            [
                "name" => "SERVICE_CATEGORY_DELETE",
                "description" => "Fitur untuk menghapus service kategori"
            ],
            [
                "name" => "SERVICE_ITEMS_GET",
                "description" => "Fitur untuk mengambil list service item"
            ],
            [
                "name" => "SERVICE_ITEM_GET",
                "description" => "Fitur untuk mengambil detail data service item"
            ],
            [
                "name" => "SERVICE_ITEM_ADD",
                "description" => "Fitur untuk membuat service item"
            ],
            [
                "name" => "SERVICE_ITEM_UPDATE",
                "description" => "Fitur untuk memperbarui service item"
            ],
            [
                "name" => "SERVICE_ITEM_DELETE",
                "description" => "Fitur untuk menghapus service item"
            ],
            [
                "name" => "SERVICE_ITEM_PUBLISH",
                "description" => "Fitur untuk merubah status publikasi service item"
            ],
            [
                "name" => "SERVICE_ITEM_DEPUBLISH",
                "description" => "Fitur untuk merubah status depublikasi service item"
            ],
            [
                "name" => "CONTRACTS_GET",
                "description" => "Fitur untuk mengambil list contract"
            ],
            [
                "name" => "CONTRACT_GET",
                "description" => "Fitur untuk mengambil detail data contract"
            ],
            [
                "name" => "CONTRACT_ADD",
                "description" => "Fitur untuk membuat contract"
            ],
            [
                "name" => "CONTRACT_UPDATE",
                "description" => "Fitur untuk memperbarui contract"
            ],
            [
                "name" => "CONTRACT_DELETE",
                "description" => "Fitur untuk menghapus contract"
            ],
            [
                "name" => "CONTRACT_ACTIVE",
                "description" => "Fitur untuk merubah status aktif contract"
            ],
            [
                "name" => "CONTRACT_DEACTIVE",
                "description" => "Fitur untuk merubah status non aktif contract"
            ],
            [
                "name" => "CONTRACT_SERVICE_ITEM_ACTIVE",
                "description" => "Fitur untuk merubah status aktif contract service item"
            ],
            [
                "name" => "CONTRACT_SERVICE_ITEM_DEACTIVE",
                "description" => "Fitur untuk merubah status non aktif contract service item"
            ],
            [
                "name" => "CONTRACT_TYPES_GET",
                "description" => "Fitur untuk mengambil list tipe contract"
            ],
            [
                "name" => "CONTRACT_TYPE_ADD",
                "description" => "Fitur untuk membuat tipe contract"
            ],
            [
                "name" => "CONTRACT_TYPE_UPDATE",
                "description" => "Fitur untuk memperbarui tipe contract"
            ],
            [
                "name" => "CONTRACT_TYPE_DELETE",
                "description" => "Fitur untuk menghapus tipe contract"
            ],
            [
                "name" => "CAREER_ADD",
                "description" => "Fitur untuk membuat career baru pada company profile"
            ],
            [
                "name" => "CAREER_UPDATE",
                "description" => "Fitur untuk memperbarui career pada company profile"
            ],
            [
                "name" => "CAREER_DELETE",
                "description" => "Fitur untuk menghapus career pada company profile"
            ],
            [
                "name" => "MESSAGES_GET",
                "description" => "Fitur untuk mengambil list message dari company profile"
            ],
            [
                "name" => "COMPANY_SUB_ADD",
                "description" => "Fitur untuk COMPANY_SUB_ADD"
            ],
            [
                "name" => "COMPANY_SUB_UPDATE",
                "description" => "Fitur untuk COMPANY_SUB_UPDATE"
            ],
            [
                "name" => "COMPANY_SUB_DELETE",
                "description" => "Fitur untuk COMPANY_SUB_DELETE"
            ],
            [
                "name" => "LOG_COMPANY_GET",
                "description" => "Fitur untuk LOG_COMPANY_GET"
            ]
        ];
        return $data;
    }

    public function defaultDataModules()
    {
        $data = [
            [
                "name" => "Agent",
                "description" => "Modul yang berisi fitur-fitur agent",
                "features" => [
                    [
                        "name" => "AGENT_GET"
                    ],
                    [
                        "name" => "AGENTS_GET"
                    ],
                    [
                        "name" => "AGENT_ADD"
                    ],
                    [
                        "name" => "AGENT_UPDATE"
                    ],
                    [
                        "name" => "AGENT_PASSWORD_UPDATE"
                    ],
                    [
                        "name" => "AGENT_STATUS"
                    ],
                    [
                        "name" => "AGENT_UPDATE_FEATURE"
                    ],
                    [
                        "name" => "AGENT_GROUPS_GET"
                    ],
                    [
                        "name" => "AGENT_GROUP_ADD"
                    ],
                    [
                        "name" => "AGENT_GROUP_GET"
                    ],
                    [
                        "name" => "AGENT_GROUP_UPDATE"
                    ],
                    [
                        "name" => "AGENT_GROUP_DELETE"
                    ],
                    [
                        "name" => "AGENT_RELATIONSHIP_INVENTORY_GET"
                    ]
                ]
            ],
            [
                "name" => "Requester",
                "description" => "Modul yang berisi fitur-fitur requester",
                "features" => [
                    [
                        "name" => "REQUESTER_STATUS"
                    ],
                    [
                        "name" => "REQUESTER_PASSWORD_UPDATE"
                    ],
                    [
                        "name" => "REQUESTER_UPDATE"
                    ],
                    [
                        "name" => "REQUESTER_ADD"
                    ],
                    [
                        "name" => "REQUESTER_GET"
                    ],
                    [
                        "name" => "REQUESTERS_GET"
                    ],
                    [
                        "name" => "REQUESTER_UPDATE_FEATURE"
                    ],
                    [
                        "name" => "REQUESTER_GROUPS_GET"
                    ],
                    [
                        "name" => "REQUESTER_GROUP_ADD"
                    ],
                    [
                        "name" => "REQUESTER_GROUP_GET"
                    ],
                    [
                        "name" => "REQUESTER_GROUP_UPDATE"
                    ],
                    [
                        "name" => "REQUESTER_GROUP_DELETE"
                    ],
                    [
                        "name" => "REQUESTER_RELATIONSHIP_INVENTORY_GET"
                    ]
                ]
            ],
            [
                "name" => "Main Company",
                "description" => "Modul yang berisi fitur-fitur My company",
                "features" => [
                    [
                        "name" => "MAIN_COMPANY_GET"
                    ],
                    [
                        "name" => "MAIN_COMPANY_UPDATE"
                    ],
                    [
                        "name" => "MAIN_BANKS_GET"
                    ],
                    [
                        "name" => "MAIN_BANK_ADD"
                    ],
                    [
                        "name" => "MAIN_BANK_UPDATE"
                    ],
                    [
                        "name" => "MAIN_BANK_DELETE"
                    ],
                    [
                        "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET"
                    ],
                    [
                        "name" => "LOG_COMPANY_GET"
                    ]
                ]
            ],
            [
                "name" => "Sub Company",
                "description" => "Modul yang berisi fitur-fitur Sub company",
                "features" => [
                    [
                        "name" => "COMPANY_SUB_ADD"
                    ],
                    [
                        "name" => "COMPANY_SUB_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_SUB_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Branch Company",
                "description" => "Modul yang berisi fitur-fitur perusahaan cabang",
                "features" => [
                    [
                        "name" => "COMPANY_BRANCHS_GET"
                    ],
                    [
                        "name" => "COMPANY_BRANCH_GET"
                    ],
                    [
                        "name" => "COMPANY_BRANCH_ADD"
                    ],
                    [
                        "name" => "COMPANY_BRANCH_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_BRANCH_STATUS"
                    ],
                    [
                        "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET"
                    ]
                ]
            ],
            [
                "name" => "Client Company",
                "description" => "Modul yang berisi fitur-fitur perusahaan client",
                "features" => [
                    [
                        "name" => "COMPANY_CLIENTS_GET"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_GET"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_ADD"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_STATUS"
                    ],
                    [
                        "name" => "CLIENT_BANKS_GET"
                    ],
                    [
                        "name" => "CLIENT_BANK_ADD"
                    ],
                    [
                        "name" => "CLIENT_BANK_UPDATE"
                    ],
                    [
                        "name" => "CLIENT_BANK_DELETE"
                    ],
                    [
                        "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET"
                    ]
                ]
            ],
            [
                "name" => "Depreciation",
                "description" => "Modul yang berisi fitur-fitur depresiasi",
                "features" => [
                    [
                        "name" => "DEPRECIATIONS_GET"
                    ],
                    [
                        "name" => "DEPRECIATION_ADD"
                    ],
                    [
                        "name" => "DEPRECIATION_UPDATE"
                    ],
                    [
                        "name" => "DEPRECIATION_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Module",
                "description" => "Modul yang berisi fitur-fitur module",
                "features" => [
                    [
                        "name" => "MODULES_GET"
                    ],
                    [
                        "name" => "MODULE_ADD"
                    ],
                    [
                        "name" => "MODULE_UPDATE"
                    ],
                    [
                        "name" => "MODULE_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Role",
                "description" => "Modul yang berisi fitur-fitur tentang role",
                "features" => [
                    [
                        "name" => "ROLES_GET"
                    ],
                    [
                        "name" => "ROLE_GET"
                    ],
                    [
                        "name" => "ROLE_ADD"
                    ],
                    [
                        "name" => "ROLE_UPDATE"
                    ],
                    [
                        "name" => "ROLE_DELETE"
                    ],
                    [
                        "name" => "ROLE_USER_FEATURES_GET"
                    ]
                ]
            ],
            [
                "name" => "Company Profile",
                "description" => "Modul yang berisi fitur-fitur untuk company profile",
                "features" => [
                    [
                        "name" => "CAREER_ADD"
                    ],
                    [
                        "name" => "CAREER_UPDATE"
                    ],
                    [
                        "name" => "CAREER_DELETE"
                    ],
                    [
                        "name" => "MESSAGES_GET"
                    ]
                ]
            ],
            [
                "name" => "Asset",
                "description" => "Modul yang berisi fitur-fitur untuk asset",
                "features" => [
                    [
                        "name" => "ASSETS_GET"
                    ],
                    [
                        "name" => "ASSET_GET"
                    ],
                    [
                        "name" => "ASSET_ADD"
                    ],
                    [
                        "name" => "ASSET_UPDATE"
                    ],
                    [
                        "name" => "ASSET_DELETE"
                    ],
                    [
                        "name" => "RELATIONSHIP_ASSET_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_ASSETS_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_ASSET_ADD"
                    ],
                    [
                        "name" => "RELATIONSHIP_ASSET_UPDATE"
                    ],
                    [
                        "name" => "RELATIONSHIP_ASSET_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Model",
                "description" => "Modul yang berisi fitur-fitur untuk model",
                "features" => [
                    [
                        "name" => "MODELS_GET"
                    ],
                    [
                        "name" => "MODEL_GET"
                    ],
                    [
                        "name" => "MODEL_ADD"
                    ],
                    [
                        "name" => "MODEL_UPDATE"
                    ],
                    [
                        "name" => "MODEL_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Inventory",
                "description" => "Modul yang berisi fitur-fitur untuk inventory",
                "features" => [
                    [
                        "name" => "INVENTORIES_GET"
                    ],
                    [
                        "name" => "INVENTORY_GET"
                    ],
                    [
                        "name" => "INVENTORY_ADD"
                    ],
                    [
                        "name" => "INVENTORY_UPDATE"
                    ],
                    [
                        "name" => "INVENTORY_DELETE"
                    ],
                    [
                        "name" => "INVENTORY_NOTES_ADD"
                    ],
                    [
                        "name" => "INVENTORY_STATUS_USAGE"
                    ],
                    [
                        "name" => "INVENTORY_STATUS_CONDITION"
                    ],
                    [
                        "name" => "INVENTORY_PARTS_ADD"
                    ],
                    [
                        "name" => "INVENTORY_PART_REMOVE"
                    ],
                    [
                        "name" => "INVENTORY_PART_REPLACE"
                    ],
                    [
                        "name" => "INVENTORY_LOG_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORIES_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_ADD"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_UPDATE"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Vendor",
                "description" => "Modul yang berisi fitur-fitur untuk vendor",
                "features" => [
                    [
                        "name" => "VENDORS_GET"
                    ],
                    [
                        "name" => "VENDOR_GET"
                    ],
                    [
                        "name" => "VENDOR_ADD"
                    ],
                    [
                        "name" => "VENDOR_UPDATE"
                    ],
                    [
                        "name" => "VENDOR_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Manufacturer",
                "description" => "Modul yang berisi fitur-fitur untuk manufacturer",
                "features" => [
                    [
                        "name" => "MANUFACTURERS_GET"
                    ],
                    [
                        "name" => "MANUFACTURER_ADD"
                    ],
                    [
                        "name" => "MANUFACTURER_UPDATE"
                    ],
                    [
                        "name" => "MANUFACTURER_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Relationship",
                "description" => "Modul yang berisi fitur-fitur untuk relationship",
                "features" => [
                    [
                        "name" => "RELATIONSHIPS_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_ADD"
                    ],
                    [
                        "name" => "RELATIONSHIP_UPDATE"
                    ],
                    [
                        "name" => "RELATIONSHIP_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Admin Ticket",
                "description" => "Modul yang berisi fitur-fitur untuk ticket admin",
                "features" => [
                    [
                        "name" => "TICKETS_GET"
                    ],
                    [
                        "name" => "TICKET_GET"
                    ],
                    [
                        "name" => "TICKET_LOG_GET"
                    ],
                    [
                        "name" => "TICKET_ADD"
                    ],
                    [
                        "name" => "TICKET_UPDATE"
                    ],
                    [
                        "name" => "TICKET_SET_STATUS"
                    ],
                    [
                        "name" => "TICKET_ASSIGN"
                    ],
                    [
                        "name" => "TICKET_SET_ITEM"
                    ],
                    [
                        "name" => "TICKET_NOTE_ADD"
                    ],
                    [
                        "name" => "TICKET_EXPORT"
                    ],
                    [
                        "name" => "TICKETS_EXPORT"
                    ],
                    [
                        "name" => "CLOSED_TICKETS_GET"
                    ]
                ]
            ],
            [
                "name" => "Client Ticket",
                "description" => "Modul yang berisi fitur-fitur untuk ticket client",
                "features" => [
                    [
                        "name" => "CLIENT_TICKETS_GET"
                    ],
                    [
                        "name" => "CLIENT_TICKET_GET"
                    ],
                    [
                        "name" => "CLIENT_TICKET_LOG_GET"
                    ],
                    [
                        "name" => "CLIENT_TICKET_NOTE_ADD"
                    ],
                    [
                        "name" => "CLIENT_TICKET_EXPORT"
                    ],
                    [
                        "name" => "CLIENT_CANCEL_TICKET"
                    ],
                    [
                        "name" => "CLIENT_CLOSED_TICKETS_GET"
                    ],
                    [
                        "name" => "TICKET_ADD"
                    ]
                ]
            ],
            [
                "name" => "Contract",
                "description" => "Modul yang berisi fitur-fitur kontrak",
                "features" => [
                    [
                        "name" => "CONTRACTS_GET"
                    ],
                    [
                        "name" => "CONTRACT_GET"
                    ],
                    [
                        "name" => "CONTRACT_ADD"
                    ],
                    [
                        "name" => "CONTRACT_UPDATE"
                    ],
                    [
                        "name" => "CONTRACT_DELETE"
                    ],
                    [
                        "name" => "CONTRACT_ACTIVE"
                    ],
                    [
                        "name" => "CONTRACT_DEACTIVE"
                    ],
                    [
                        "name" => "CONTRACT_SERVICE_ITEM_ACTIVE"
                    ],
                    [
                        "name" => "CONTRACT_SERVICE_ITEM_DEACTIVE"
                    ],
                    [
                        "name" => "CONTRACT_TYPES_GET"
                    ],
                    [
                        "name" => "CONTRACT_TYPE_ADD"
                    ],
                    [
                        "name" => "CONTRACT_TYPE_UPDATE"
                    ],
                    [
                        "name" => "CONTRACT_TYPE_DELETE"
                    ]
                ]
            ]
        ];
        return $data;
    }
}
