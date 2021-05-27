<?php

use Illuminate\Database\Seeder;
use App\AccessFeature;

class AccessFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function addAccountFeatures($account_features)
    {
        foreach($account_features as $feature){
            $access_feature = AccessFeature::where('feature_id', $feature['feature_id'])->first();
            if($access_feature === null){
                $access_feature = new AccessFeature;
            }
            $access_feature->feature_key = $feature['feature_key'];
            $access_feature->feature_id = $feature['feature_id'];
            $access_feature->name = $feature['name'];
            $access_feature->description = $feature['description'];
            $access_feature->save();
        }
    }

    public function run()
    {
        $account_features = [
            [
                "feature_id" => 75,
                "name" => "ACCOUNT_STATUS_ACTIVATION",
                "description" => "Member yang terdaftar pada perusahaan dapat secara paksa di matikan accountnya. Menyebabkan tidak dapat login kembali"
            ],
            [
                "feature_id" => 74,
                "name" => "ACCOUNT_UPDATE_FEATURE",
                "description" => "Update/Change/Enable/Disable/Register Feature to spesific account"
            ],
            [
                "feature_id" => 62,
                "name" => "ACCOUNT_CHANGE_PASSWORD",
                "description" => "Mengubah paksa password suatu account yang terdaftar pada member perusahaan yang sama"
            ],
            [
                "feature_id" => 61,
                "name" => "ACCOUNT_UPDATE_MEMBER",
                "description" => "Fitur untuk secara paksa mengubah profile/detail account anggota/member yg terdaftar"
            ],
            [
                "feature_id" => 60,
                "name" => "ACCOUNT_LIST_MEMBER",
                "description" => "Mengubah paksa password suatu account yang terdaftar pada member perusahaan yang sama"
            ],
            [
                "feature_id" => 59,
                "name" => "ACCOUNT_DETAIL_MEMBER",
                "description" => "Fitur untuk melihat detail informasi ke-anggotaan perusahaan"
            ],
        ];

        $company_features = [
            [
                "feature_id" => 58,
                "name" => "COMPANY_CHANGE_STATUS",
                "description" => "Member Perusahaan yang terdaftar dalam anggota. Dapat secara paksa di matikan/aktifkan account perusahaannya. Menyebabkan seluruh penggunanya tidak dapat login"
            ],
            [
                "feature_id" => 57,
                "name" => "COMPANY_UPDATE_PROFILE",
                "description" => "Fitur untuk secara paksa mengubah profile/detail company yg terdaftar sebagai member"
            ],
            [
                "feature_id" => 56,
                "name" => "COMPANY_DETAIL_MEMBER",
                "description" => "Perusahaan dapat memiliki anggota client berupa perusahaan. Dan memberikan informasi detail-nya"
            ],
            [
                "feature_id" => 55,
                "name" => "COMPANY_LIST_MEMBER",
                "description" => "Perusahaan dapat memiliki anggota client berupa perusahaan. Dimana perusahaan dapat melakukan listing untuk melihat anggota perusahaan yang terdaftar di dalamnya"
            ],
            [
                "feature_id" => 54,
                "name" => "COMPANY_ADD_CLIENT",
                "description" => "Tambah Anggota Client	Tambah client perusahaan. untuk perusahaan dengan role admin"
            ]
        ];

        $company_profile_features = [
            [
                "feature_id" => 209,
                "name" => "CAREER_ADD",
                "description" => "Fitur untuk membuat career baru pada company profile",
                "feature_key" => "8066c7cb-df25-4577-b84f-e087c77777b7"
            ],
            [
                "feature_id" => 210,
                "name" => "CAREER_UPDATE",
                "description" => "Fitur untuk memperbarui career pada company profile",
                "feature_key" => "e529eb4d-dcc0-4a30-b4c1-525807737df7"
            ],
            [
                "feature_id" => 211,
                "name" => "CAREER_DELETE",
                "description" => "Fitur untuk menghapus career pada company profile",
                "feature_key" => "0fbd618f-5727-445e-83b2-9093e681383e"
            ],
            [
                "feature_id" => 212,
                "name" => "MESSAGES_GET",
                "description" => "Fitur untuk mengambil list message dari company profile",
                "feature_key" => "7a4e14a1-735c-4281-9a6a-0e56e5ca93c0"
            ]
        ];

        foreach($account_features as $feature){
            $access_feature = AccessFeature::where('feature_id', $feature['feature_id'])->first();
            if($access_feature === null){
                $access_feature = new AccessFeature;
            }
            $access_feature->feature_key = '-';
            $access_feature->feature_id = $feature['feature_id'];
            $access_feature->name = $feature['name'];
            $access_feature->description = $feature['description'];
            $access_feature->save();
        }

        foreach($company_features as $feature){
            $access_feature = AccessFeature::where('feature_id', $feature['feature_id'])->first();
            if($access_feature === null){
                $access_feature = new AccessFeature;
            }
            $access_feature->feature_key = '-';
            $access_feature->feature_id = $feature['feature_id'];
            $access_feature->name = $feature['name'];
            $access_feature->description = $feature['description'];
            $access_feature->save();
        }

        $this->addAccountFeatures($company_profile_features);
    }
}
