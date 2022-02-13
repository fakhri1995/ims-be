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
                "name" => "USERS_GET",
                "description" => "Fitur untuk mengambil detail data seluruh user"
            ],
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
                "name" => "AGENT_DELETE",
                "description" => "Fitur untuk menghapus data agent"
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
                "name" => "REQUESTER_GET",
                "description" => "Fitur untuk mengambil data detail requester"
            ],
            [
                "name" => "REQUESTERS_GET",
                "description" => "Fitur untuk mengambil list requester"
            ],
            [
                "name" => "REQUESTER_ADD",
                "description" => "Fitur untuk membuat requester baru"
            ],
            [
                "name" => "REQUESTER_UPDATE",
                "description" => "Fitur untuk memperbarui data requester"
            ],
            [
                "name" => "REQUESTER_DELETE",
                "description" => "Fitur untuk menghapus data requester"
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
                "name" => "REQUESTER_RELATIONSHIP_INVENTORY_GET",
                "description" => "Fitur untuk REQUESTER_RELATIONSHIP_INVENTORY_GET"
            ],
            [
                "name" => "GROUPS_GET",
                "description" => "Fitur untuk mengambil list group"
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
                "name" => "COMPANY_LISTS_GET",
                "description" => "Fitur untuk mengambil data seluruh tree list perusahaan"
            ],
            [
                "name" => "COMPANY_BRANCHS_GET",
                "description" => "Fitur untuk mengambil data seluruh tree list perusahaan branch"
            ],
            [
                "name" => "COMPANY_CLIENTS_GET",
                "description" => "Fitur untuk mengambil data seluruh tree list perusahaan client"
            ],
            [
                "name" => "COMPANY_INVENTORIES_GET",
                "description" => "Fitur untuk mengambil data inventory yang dimiliki perusahaan"
            ],
            [
                "name" => "COMPANY_MAIN_LOCATIONS_GET",
                "description" => "Fitur untuk mengambil list tree lokasi dari perusahaan user login"
            ],
            [
                "name" => "COMPANY_LOCATIONS_GET",
                "description" => "Fitur untuk mengambil list tree lokasi dari perusahaan tertentu"
            ],
            [
                "name" => "COMPANY_SUB_LOCATIONS_GET",
                "description" => "Fitur untuk mengambil list tree lokasi dari sub perusahaan tertentu"
            ],
            [
                "name" => "COMPANY_DETAIL_GET",
                "description" => "Fitur untuk mengambil data detail perusahaan"
            ],
            [
                "name" => "COMPANY_SUB_DETAIL_GET",
                "description" => "Fitur untuk mengambil data sub detail perusahaan"
            ],
            [
                "name" => "COMPANY_SUB_PROFILE_GET",
                "description" => "Fitur untuk mengambil data sub profile perusahaanG"
            ],
            [
                "name" => "COMPANY_LOG_GET",
                "description" => "Fitur untuk mengambil log company"
            ],
            [
                "name" => "COMPANY_STATUS",
                "description" => "Fitur untuk merubah aktivasi status perusahaan"
            ],
            [
                "name" => "COMPANY_UPDATE",
                "description" => "Fitur untuk memperbarui data perusahaan"
            ],
            [
                "name" => "COMPANY_DELETE",
                "description" => "Fitur untuk menghapus data perusahaan"
            ],
            [
                "name" => "COMPANY_BRANCH_ADD",
                "description" => "Fitur untuk membuat perusahaan branch"
            ],
            [
                "name" => "COMPANY_CLIENT_ADD",
                "description" => "Fitur untuk membuat perusahaan client"
            ],
            [
                "name" => "COMPANY_SUB_ADD",
                "description" => "Fitur untuk membuat sub perusahaan"
            ],
            [
                "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET",
                "description" => "Fitur untuk COMPANY_RELATIONSHIP_INVENTORY_GET"
            ],
            [
                "name" => "COMPANY_MAIN_BANKS_GET",
                "description" => "Fitur untuk mengambil list Bank User Login"
            ],
            [
                "name" => "COMPANY_MAIN_BANK_ADD",
                "description" => "Fitur untuk membuat Bank User Login"
            ],
            [
                "name" => "COMPANY_MAIN_BANK_UPDATE",
                "description" => "Fitur untuk memperbarui Bank User Login"
            ],
            [
                "name" => "COMPANY_MAIN_BANK_DELETE",
                "description" => "Fitur untuk menghapus Bank User Login"
            ],
            [
                "name" => "COMPANY_CLIENT_BANKS_GET",
                "description" => "Fitur untuk mengambil list bank perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_BANK_ADD",
                "description" => "Fitur untuk membuat bank perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_BANK_UPDATE",
                "description" => "Fitur untuk memperbarui bank perusahaan client"
            ],
            [
                "name" => "COMPANY_CLIENT_BANK_DELETE",
                "description" => "Fitur untuk menghapus bank perusahaan client"
            ],
            [
                "name" => "FEATURES_GET",
                "description" => "Fitur untuk mengambil list feature"
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
                "name" => "MODULE_FEATURES_ADD",
                "description" => "Fitur untuk menambahkan feature pada module"
            ],
            [
                "name" => "MODULE_FEATURES_DELETE",
                "description" => "Fitur untuk menghapus feature pada module"
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
                "name" => "INVENTORY_IMPORT",
                "description" => "Fitur untuk INVENTORY_IMPORT"
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
                "name" => "TICKET_TASK_STATUS_COUNTS_GET",
                "description" => "Fitur untuk TICKET_TASK_STATUS_COUNTS_GET"
            ],
            [
                "name" => "TICKETS_CLIENT_GET",
                "description" => "Fitur untuk TICKETS_CLIENT_GET"
            ],
            [
                "name" => "TICKETS_CLIENT_CLOSED_GET",
                "description" => "Fitur untuk TICKETS_CLIENT_CLOSED_GET"
            ],
            [
                "name" => "TICKET_CLIENT_GET",
                "description" => "Fitur untuk TICKET_CLIENT_GET"
            ],
            [
                "name" => "TICKET_CLIENT_CANCEL",
                "description" => "Fitur untuk TICKET_CLIENT_CANCEL"
            ],
            [
                "name" => "TICKET_CLIENT_LOG_GET",
                "description" => "Fitur untuk TICKET_CLIENT_LOG_GET"
            ],
            [
                "name" => "TICKET_CLIENT_NOTE_ADD",
                "description" => "Fitur untuk TICKET_CLIENT_NOTE_ADD"
            ],
            [
                "name" => "TICKET_CLIENT_EXPORT",
                "description" => "Fitur untuk TICKET_CLIENT_EXPORT"
            ],
            [
                "name" => "TICKETS_GET",
                "description" => "Fitur untuk TICKETS_GET"
            ],
            [
                "name" => "TICKETS_CLOSED_GET",
                "description" => "Fitur untuk TICKETS_CLOSED_GET"
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
                "name" => "TICKET_CANCEL",
                "description" => "Fitur untuk TICKET_CANCEL"
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
                "name" => "TICKET_DEADLINE_SET",
                "description" => "Fitur untuk TICKET_DEADLINE_SET"
            ],
            [
                "name" => "TICKET_ASSIGN",
                "description" => "Fitur untuk TICKET_ASSIGN"
            ],
            [
                "name" => "TICKET_ITEM_SET",
                "description" => "Fitur untuk TICKET_ITEM_SET"
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
                "name" => "TICKET_TASK_TYPES_GET",
                "description" => "Fitur untuk mengambil list tipe tiket task"
            ],
            [
                "name" => "TICKET_TASK_TYPE_ADD",
                "description" => "Fitur untuk membuat tipe tiket task"
            ],
            [
                "name" => "TICKET_TASK_TYPE_UPDATE",
                "description" => "Fitur untuk memperbarui tipe tiket task"
            ],
            [
                "name" => "TICKET_TASK_TYPE_DELETE",
                "description" => "Fitur untuk menghapus tipe tiket task"
            ],
            [
                "name" => "TASKS_GET",
                "description" => "Fitur untuk mengambil list task"
            ],
            [
                "name" => "TASK_PICK_LIST_GET",
                "description" => "Fitur untuk mengambil list task dengan status open dan belum diassign kepada siapapun"
            ],
            [
                "name" => "TASK_GET",
                "description" => "Fitur untuk mengambil detail task"
            ],
            [
                "name" => "TASK_ADD",
                "description" => "Fitur untuk membuat task"
            ],
            [
                "name" => "TASK_UPDATE",
                "description" => "Fitur untuk memperbarui task"
            ],
            [
                "name" => "TASK_DELETE",
                "description" => "Fitur untuk menghapus task"
            ],
            [
                "name" => "TASK_FILES_SAVE",
                "description" => "Fitur untuk menyimpan link data attachment task"
            ],
            [
                "name" => "TASK_STATUS_TOGGLE",
                "description" => "Fitur untuk mengubah status menjadi on hold atau on progress"
            ],
            [
                "name" => "TASK_ATTENDANCE_TOGGLE",
                "description" => "Fitur check in pada task untuk user"
            ],
            [
                "name" => "TASK_SUBMIT",
                "description" => "Fitur untuk submit pada task"
            ],
            [
                "name" => "TASK_DECLINE",
                "description" => "Fitur untuk menolak task"
            ],
            [
                "name" => "TASK_APPROVE",
                "description" => "Fitur untuk menyetujui task"
            ],
            [
                "name" => "TASK_ASSIGN_SELF",
                "description" => "Fitur untuk mengassign diri sendiri pada suatu task"
            ],
            [
                "name" => "TASK_TYPES_GET",
                "description" => "Fitur untuk mengambil list tipe task"
            ],
            [
                "name" => "TASK_TYPE_GET",
                "description" => "Fitur untuk mengambil detail fitur tipe task"
            ],
            [
                "name" => "TASK_TYPE_ADD",
                "description" => "Fitur untuk membuat tipe task"
            ],
            [
                "name" => "TASK_TYPE_UPDATE",
                "description" => "Fitur untuk memperbarui tipe task"
            ],
            [
                "name" => "TASK_TYPE_DELETE",
                "description" => "Fitur untuk menghapus tipe task"
            ],
            [
                "name" => "TASK_DETAIL_FILL",
                "description" => "Fitur untuk mengisi detail task"
            ],
            [
                "name" => "TASK_DETAIL_ASSIGN",
                "description" => "Fitur untuk mengassign detail task pada user"
            ],
            [
                "name" => "TASK_DETAIL_ADD",
                "description" => "Fitur untuk membuat detail task"
            ],
            [
                "name" => "TASK_DETAIL_UPDATE",
                "description" => "Fitur untuk memperbarui detail task"
            ],
            [
                "name" => "TASK_DETAIL_DELETE",
                "description" => "Fitur untuk menghapus detail task"
            ],
            [
                "name" => "TASK_STAFF_STATUSES_GET",
                "description" => "Fitur untuk mengambil seluruh user dengan data jumlah status tasknya"
            ],
            [
                "name" => "TASK_STATUS_LIST_GET",
                "description" => "Fitur untuk mengambil seluruh data list status task"
            ],
            [
                "name" => "TASK_TYPE_COUNTS_GET",
                "description" => "Fitur untuk mengambil seluruh data list jumlah tipe task"
            ],
            [
                "name" => "TASK_DEADLINE_GET",
                "description" => "Fitur untuk mengambil data deadline task pada interval waktu tertentu"
            ],
            [
                "name" => "TASK_STAFF_COUNTS_GET",
                "description" => "Fitur untuk mengambil data jumlah staff yang mengerjakan task"
            ],
            [
                "name" => "TASK_USER_STATUSES_GET",
                "description" => "Fitur untuk mengambil data seluruh list status task user"
            ],
            [
                "name" => "TASKS_USER_LAST_TWO_GET",
                "description" => "Fitur untuk mengambil dua data terakhir yang sedang dikerjakan user"
            ],
            [
                "name" => "TASKS_USER_GET",
                "description" => "Fitur untuk mengambil data seluruh task yang berhubungan dengan user"
            ],
            [
                "name" => "TASK_TYPE_USER_COUNTS_GET",
                "description" => "Fitur untuk mengambil data jumlah tipe task user"
            ],
            [
                "name" => "TASK_SPARE_PART_LIST_GET",
                "description" => "Fitur untuk mengambil data spare part yang berhubungan dengan task"
            ],
            [
                "name" => "TASK_SEND_INVENTORIES",
                "description" => "Fitur untuk mengirim atau mengeluarkan ke atau dari tempat task"
            ],
            [
                "name" => "TASK_CANCEL_SEND_IN_INVENTORY",
                "description" => "Fitur untuk mengeluarkan barang yang akan dikirim ke tempat task"
            ],
            [
                "name" => "TASK_CANCEL_SEND_OUT_INVENTORY",
                "description" => "Fitur untuk memasukkan kembali barang yang akan dikeluarkan dari tempat task"
            ],
            [
                "name" => "PURCHASE_ORDERS_GET",
                "description" => "Fitur untuk mengambil list pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_GET",
                "description" => "Fitur untuk mengambil detail fitur pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_ADD",
                "description" => "Fitur untuk membuat pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_UPDATE",
                "description" => "Fitur untuk memperbarui pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_DELETE",
                "description" => "Fitur untuk menghapus pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_REJECT",
                "description" => "Fitur untuk menolak pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_ACCEPT",
                "description" => "Fitur untuk menyetujui pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_SEND",
                "description" => "Fitur untuk mengirim pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_RECEIVE",
                "description" => "Fitur untuk menerima pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_DETAILS_GET",
                "description" => "Fitur untuk mengambil list detail pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_DETAIL_ADD",
                "description" => "Fitur untuk membuat detail pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_DETAIL_UPDATE",
                "description" => "Fitur untuk memperbarui detail pesanan pembelian"
            ],
            [
                "name" => "PURCHASE_ORDER_DETAIL_DELETE",
                "description" => "Fitur untuk menghapus detail pesanan pembelian"
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
                "name" => "MESSAGE_DELETE",
                "description" => "Fitur untuk mengambil list message dari company profile"
            ],


            // [
            //     "name" => "DEPRECIATIONS_GET",
            //     "description" => "Fitur untuk mengambil list depresiasi"
            // ],
            // [
            //     "name" => "DEPRECIATION_ADD",
            //     "description" => "Fitur untuk membuat depresiasi"
            // ],
            // [
            //     "name" => "DEPRECIATION_UPDATE",
            //     "description" => "Fitur untuk memperbarui depresiasi"
            // ],
            // [
            //     "name" => "DEPRECIATION_DELETE",
            //     "description" => "Fitur untuk menghapus depresiasi"
            // ],
            // [
            //     "name" => "SERVICE_CATEGORIES_GET",
            //     "description" => "Fitur untuk mengambil list service kategori"
            // ],
            // [
            //     "name" => "SERVICE_CATEGORY_ADD",
            //     "description" => "Fitur untuk membuat service kategori"
            // ],
            // [
            //     "name" => "SERVICE_CATEGORY_UPDATE",
            //     "description" => "Fitur untuk memperbarui service kategori"
            // ],
            // [
            //     "name" => "SERVICE_CATEGORY_DELETE",
            //     "description" => "Fitur untuk menghapus service kategori"
            // ],
            // [
            //     "name" => "SERVICE_ITEMS_GET",
            //     "description" => "Fitur untuk mengambil list service item"
            // ],
            // [
            //     "name" => "SERVICE_ITEM_GET",
            //     "description" => "Fitur untuk mengambil detail data service item"
            // ],
            // [
            //     "name" => "SERVICE_ITEM_ADD",
            //     "description" => "Fitur untuk membuat service item"
            // ],
            // [
            //     "name" => "SERVICE_ITEM_UPDATE",
            //     "description" => "Fitur untuk memperbarui service item"
            // ],
            // [
            //     "name" => "SERVICE_ITEM_DELETE",
            //     "description" => "Fitur untuk menghapus service item"
            // ],
            // [
            //     "name" => "SERVICE_ITEM_PUBLISH",
            //     "description" => "Fitur untuk merubah status publikasi service item"
            // ],
            // [
            //     "name" => "SERVICE_ITEM_DEPUBLISH",
            //     "description" => "Fitur untuk merubah status depublikasi service item"
            // ],
            // [
            //     "name" => "CONTRACTS_GET",
            //     "description" => "Fitur untuk mengambil list contract"
            // ],
            // [
            //     "name" => "CONTRACT_GET",
            //     "description" => "Fitur untuk mengambil detail data contract"
            // ],
            // [
            //     "name" => "CONTRACT_ADD",
            //     "description" => "Fitur untuk membuat contract"
            // ],
            // [
            //     "name" => "CONTRACT_UPDATE",
            //     "description" => "Fitur untuk memperbarui contract"
            // ],
            // [
            //     "name" => "CONTRACT_DELETE",
            //     "description" => "Fitur untuk menghapus contract"
            // ],
            // [
            //     "name" => "CONTRACT_ACTIVE",
            //     "description" => "Fitur untuk merubah status aktif contract"
            // ],
            // [
            //     "name" => "CONTRACT_DEACTIVE",
            //     "description" => "Fitur untuk merubah status non aktif contract"
            // ],
            // [
            //     "name" => "CONTRACT_SERVICE_ITEM_ACTIVE",
            //     "description" => "Fitur untuk merubah status aktif contract service item"
            // ],
            // [
            //     "name" => "CONTRACT_SERVICE_ITEM_DEACTIVE",
            //     "description" => "Fitur untuk merubah status non aktif contract service item"
            // ],
            // [
            //     "name" => "CONTRACT_TYPES_GET",
            //     "description" => "Fitur untuk mengambil list tipe contract"
            // ],
            // [
            //     "name" => "CONTRACT_TYPE_ADD",
            //     "description" => "Fitur untuk membuat tipe contract"
            // ],
            // [
            //     "name" => "CONTRACT_TYPE_UPDATE",
            //     "description" => "Fitur untuk memperbarui tipe contract"
            // ],
            // [
            //     "name" => "CONTRACT_TYPE_DELETE",
            //     "description" => "Fitur untuk menghapus tipe contract"
            // ],
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
                        "name" => "USERS_GET"
                    ],
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
                        "name" => "AGENT_DELETE"
                    ],
                    [
                        "name" => "AGENT_PASSWORD_UPDATE"
                    ],
                    [
                        "name" => "AGENT_STATUS"
                    ],
                    [
                        "name" => "AGENT_RELATIONSHIP_INVENTORY_GET"
                    ],
                    [
                        "name" => "GROUPS_GET"
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
                ]
            ],
            [
                "name" => "Requester",
                "description" => "Modul yang berisi fitur-fitur requester",
                "features" => [
                    [
                        "name" => "REQUESTER_GET"
                    ],
                    [
                        "name" => "REQUESTERS_GET"
                    ],
                    [
                        "name" => "REQUESTER_ADD"
                    ],
                    [
                        "name" => "REQUESTER_UPDATE"
                    ],
                    [
                        "name" => "REQUESTER_DELETE"
                    ],
                    [
                        "name" => "REQUESTER_STATUS"
                    ],
                    [
                        "name" => "REQUESTER_PASSWORD_UPDATE"
                    ],
                    [
                        "name" => "REQUESTER_RELATIONSHIP_INVENTORY_GET"
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
                ]
            ],
            [
                "name" => "Non-Client Company",
                "description" => "Modul yang berisi fitur-fitur Non-Client company",
                "features" => [
                    [
                        "name" => "COMPANY_LISTS_GET"
                    ],
                    [
                        "name" => "COMPANY_BRANCHS_GET"
                    ],
                    [
                        "name" => "COMPANY_CLIENTS_GET"
                    ],
                    [
                        "name" => "COMPANY_INVENTORIES_GET"
                    ],
                    [
                        "name" => "COMPANY_MAIN_LOCATIONS_GET"
                    ],
                    [
                        "name" => "COMPANY_LOCATIONS_GET"
                    ],
                    [
                        "name" => "COMPANY_SUB_LOCATIONS_GET"
                    ],
                    [
                        "name" => "COMPANY_DETAIL_GET"
                    ],
                    [
                        "name" => "COMPANY_SUB_DETAIL_GET"
                    ],
                    [
                        "name" => "COMPANY_SUB_PROFILE_GET"
                    ],
                    [
                        "name" => "COMPANY_LOG_GET"
                    ],
                    [
                        "name" => "COMPANY_STATUS"
                    ],
                    [
                        "name" => "COMPANY_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_DELETE"
                    ],
                    [
                        "name" => "COMPANY_BRANCH_ADD"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_ADD"
                    ],
                    [
                        "name" => "COMPANY_SUB_ADD"
                    ],
                    [
                        "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANKS_GET"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANK_ADD"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANK_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANK_DELETE"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_BANKS_GET"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_BANK_ADD"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_BANK_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_BANK_DELETE"
                    ],
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
                        "name" => "COMPANY_INVENTORIES_GET"
                    ],
                    [
                        "name" => "COMPANY_MAIN_LOCATIONS_GET"
                    ],
                    [
                        "name" => "COMPANY_LOCATIONS_GET"
                    ],
                    [
                        "name" => "COMPANY_SUB_LOCATIONS_GET"
                    ],
                    [
                        "name" => "COMPANY_DETAIL_GET"
                    ],
                    [
                        "name" => "COMPANY_SUB_DETAIL_GET"
                    ],
                    [
                        "name" => "COMPANY_SUB_PROFILE_GET"
                    ],
                    [
                        "name" => "COMPANY_LOG_GET"
                    ],
                    [
                        "name" => "COMPANY_STATUS"
                    ],
                    [
                        "name" => "COMPANY_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_DELETE"
                    ],
                    [
                        "name" => "COMPANY_CLIENT_ADD"
                    ],
                    [
                        "name" => "COMPANY_SUB_ADD"
                    ],
                    [
                        "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANKS_GET"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANK_ADD"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANK_UPDATE"
                    ],
                    [
                        "name" => "COMPANY_MAIN_BANK_DELETE"
                    ],
                ]
            ],
            [
                "name" => "Module",
                "description" => "Modul yang berisi fitur-fitur module",
                "features" => [
                    [
                        "name" => "FEATURES_GET"
                    ],
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
                    ],
                    [
                        "name" => "MODULE_FEATURES_ADD"
                    ],
                    [
                        "name" => "MODULE_FEATURES_DELETE"
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
                        "name" => "ROLE_USER_FEATURES_GET"
                    ],
                    [
                        "name" => "ROLE_ADD"
                    ],
                    [
                        "name" => "ROLE_UPDATE"
                    ],
                    [
                        "name" => "ROLE_DELETE"
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
                        "name" => "INVENTORY_IMPORT"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_GET"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_ADD"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_UPDATE"
                    ],
                    [
                        "name" => "RELATIONSHIP_INVENTORY_DELETE"
                    ],
                    [
                        "name" => "AGENT_RELATIONSHIP_INVENTORY_GET",
                    ],
                    [
                        "name" => "REQUESTER_RELATIONSHIP_INVENTORY_GET",
                    ],
                    [
                        "name" => "COMPANY_RELATIONSHIP_INVENTORY_GET",
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
                        "name" => "TICKET_TASK_STATUS_COUNTS_GET"
                    ],
                    [
                        "name" => "TICKETS_GET"
                    ],
                    [
                        "name" => "TICKETS_CLOSED_GET"
                    ],
                    [
                        "name" => "TICKET_GET"
                    ],
                    [
                        "name" => "TICKET_LOG_GET"
                    ],
                    [
                        "name" => "TICKET_CANCEL"
                    ],
                    [
                        "name" => "TICKET_ADD"
                    ],
                    [
                        "name" => "TICKET_UPDATE"
                    ],
                    [
                        "name" => "TICKET_DEADLINE_SET"
                    ],
                    [
                        "name" => "TICKET_ASSIGN"
                    ],
                    [
                        "name" => "TICKET_ITEM_SET"
                    ],
                    [
                        "name" => "TICKET_NOTE_ADD"
                    ],
                    [
                        "name" => "TICKETS_EXPORT"
                    ],
                    [
                        "name" => "TICKET_EXPORT"
                    ]
                ]
            ],
            [
                "name" => "Client Ticket",
                "description" => "Modul yang berisi fitur-fitur untuk ticket client",
                "features" => [
                    [
                        "name" => "TICKETS_CLIENT_GET"
                    ],
                    [
                        "name" => "TICKETS_CLIENT_CLOSED_GET"
                    ],
                    [
                        "name" => "TICKET_CLIENT_GET"
                    ],
                    [
                        "name" => "TICKET_CLIENT_CANCEL"
                    ],
                    [
                        "name" => "TICKET_CLIENT_LOG_GET"
                    ],
                    [
                        "name" => "TICKET_CLIENT_NOTE_ADD"
                    ],
                    [
                        "name" => "TICKET_CLIENT_EXPORT"
                    ],

                    [
                        "name" => "TICKET_TASK_TYPES_GET"
                    ],
                    [
                        "name" => "TICKET_TASK_TYPE_ADD"
                    ],
                    [
                        "name" => "TICKET_TASK_TYPE_UPDATE"
                    ],
                    [
                        "name" => "TICKET_TASK_TYPE_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Tipe Task Ticket",
                "description" => "Modul yang berisi fitur-fitur untuk tipe task ticket",
                "features" => [
                    [
                        "name" => "TICKET_TASK_TYPES_GET"
                    ],
                    [
                        "name" => "TICKET_TASK_TYPE_ADD"
                    ],
                    [
                        "name" => "TICKET_TASK_TYPE_UPDATE"
                    ],
                    [
                        "name" => "TICKET_TASK_TYPE_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Task Umum",
                "description" => "Modul yang berisi fitur-fitur untuk task umum",
                "features" => [
                    [
                        "name" => "TASKS_GET"
                    ],
                    [
                        "name" => "TASK_PICK_LIST_GET"
                    ],
                    [
                        "name" => "TASK_GET"
                    ],
                    [
                        "name" => "TASK_ADD"
                    ],
                    [
                        "name" => "TASK_UPDATE"
                    ],
                    [
                        "name" => "TASK_DELETE"
                    ],
                    [
                        "name" => "TASK_FILES_SAVE"
                    ],
                    [
                        "name" => "TASK_STATUS_TOGGLE"
                    ],
                    [
                        "name" => "TASK_ATTENDANCE_TOGGLE"
                    ],
                    [
                        "name" => "TASK_SUBMIT"
                    ],
                    [
                        "name" => "TASK_DECLINE"
                    ],
                    [
                        "name" => "TASK_APPROVE"
                    ],
                    [
                        "name" => "TASK_ASSIGN_SELF"
                    ]
                ]
            ],
            [
                "name" => "Tipe Task",
                "description" => "Modul yang berisi fitur-fitur untuk tipe task",
                "features" => [
                    [
                        "name" => "TASK_TYPES_GET"
                    ],
                    [
                        "name" => "TASK_TYPE_GET"
                    ],
                    [
                        "name" => "TASK_TYPE_ADD"
                    ],
                    [
                        "name" => "TASK_TYPE_UPDATE"
                    ],
                    [
                        "name" => "TASK_TYPE_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Detail Task",
                "description" => "Modul yang berisi fitur-fitur untuk detail task",
                "features" => [
                    [
                        "name" => "TASK_DETAIL_FILL"
                    ],
                    [
                        "name" => "TASK_DETAIL_ASSIGN"
                    ],
                    [
                        "name" => "TASK_DETAIL_ADD"
                    ],
                    [
                        "name" => "TASK_DETAIL_UPDATE"
                    ],
                    [
                        "name" => "TASK_DETAIL_DELETE"
                    ]
                ]
            ],
            [
                "name" => "Beranda Admin",
                "description" => "Modul yang berisi fitur-fitur untuk beranda admin",
                "features" => [
                    [
                        "name" => "TASK_STAFF_STATUSES_GET"
                    ],
                    [
                        "name" => "TASK_STATUS_LIST_GET"
                    ],
                    [
                        "name" => "TASK_TYPE_COUNTS_GET"
                    ],
                    [
                        "name" => "TASK_DEADLINE_GET"
                    ],
                    [
                        "name" => "TASK_STAFF_COUNTS_GET"
                    ]
                ]
            ],
            [
                "name" => "Beranda Pengguna",
                "description" => "Modul yang berisi fitur-fitur untuk beranda pengguna",
                "features" => [
                    [
                        "name" => "TASK_USER_STATUSES_GET"
                    ],
                    [
                        "name" => "TASKS_USER_LAST_TWO_GET"
                    ],
                    [
                        "name" => "TASKS_USER_GET"
                    ],
                    [
                        "name" => "TASK_TYPE_USER_COUNTS_GET"
                    ]
                ]
            ],
            [
                "name" => "Spare Part",
                "description" => "Modul yang berisi fitur-fitur untuk spare part",
                "features" => [
                    [
                        "name" => "TASK_SPARE_PART_LIST_GET"
                    ],
                    [
                        "name" => "TASK_SEND_INVENTORIES"
                    ],
                    [
                        "name" => "TASK_CANCEL_SEND_IN_INVENTORY"
                    ],
                    [
                        "name" => "TASK_CANCEL_SEND_OUT_INVENTORY"
                    ]
                ]
            ],
            [
                "name" => "Pesanan Pembelian",
                "description" => "Modul yang berisi fitur-fitur untuk pesanan pembelian",
                "features" => [
                    [
                        "name" => "PURCHASE_ORDERS_GET",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_GET",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_ADD",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_UPDATE",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_DELETE",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_REJECT",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_ACCEPT",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_SEND",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_RECEIVE",
                    ]
                ]
            ],
            [
                "name" => "Detail Pesanan Pembelian",
                "description" => "Modul yang berisi fitur-fitur untuk detail pesanan pembelian",
                "features" => [
                    [
                        "name" => "PURCHASE_ORDER_DETAILS_GET",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_DETAIL_ADD",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_DETAIL_UPDATE",
                    ],
                    [
                        "name" => "PURCHASE_ORDER_DETAIL_DELETE",
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
                    ],
                    [
                        "name" => "MESSAGE_DELETE"
                    ]
                ]
            ]
            // [
            //     "name" => "Depreciation",
            //     "description" => "Modul yang berisi fitur-fitur depresiasi",
            //     "features" => [
            //         [
            //             "name" => "DEPRECIATIONS_GET"
            //         ],
            //         [
            //             "name" => "DEPRECIATION_ADD"
            //         ],
            //         [
            //             "name" => "DEPRECIATION_UPDATE"
            //         ],
            //         [
            //             "name" => "DEPRECIATION_DELETE"
            //         ]
            //     ]
            // ],
            // [
            //     "name" => "Contract",
            //     "description" => "Modul yang berisi fitur-fitur kontrak",
            //     "features" => [
            //         [
            //             "name" => "CONTRACTS_GET"
            //         ],
            //         [
            //             "name" => "CONTRACT_GET"
            //         ],
            //         [
            //             "name" => "CONTRACT_ADD"
            //         ],
            //         [
            //             "name" => "CONTRACT_UPDATE"
            //         ],
            //         [
            //             "name" => "CONTRACT_DELETE"
            //         ],
            //         [
            //             "name" => "CONTRACT_ACTIVE"
            //         ],
            //         [
            //             "name" => "CONTRACT_DEACTIVE"
            //         ],
            //         [
            //             "name" => "CONTRACT_SERVICE_ITEM_ACTIVE"
            //         ],
            //         [
            //             "name" => "CONTRACT_SERVICE_ITEM_DEACTIVE"
            //         ],
            //         [
            //             "name" => "CONTRACT_TYPES_GET"
            //         ],
            //         [
            //             "name" => "CONTRACT_TYPE_ADD"
            //         ],
            //         [
            //             "name" => "CONTRACT_TYPE_UPDATE"
            //         ],
            //         [
            //             "name" => "CONTRACT_TYPE_DELETE"
            //         ]
            //     ]
            // ]
        ];
        return $data;
    }
}
