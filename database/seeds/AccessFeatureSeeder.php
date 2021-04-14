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
    public function run()
    {
        $account_features = [
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
    }
}
