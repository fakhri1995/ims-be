<?php

use App\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function addDefaultVendors()
    {   
        $datas = $this->defaultVendors();
        foreach($datas as $data){
            $vendor = new Vendor;
            $vendor->name = $data['name'];
            $vendor->singkatan_nama = $data['singkatan_nama'];
            $vendor->npwp = $data['npwp'];
            $vendor->pic = $data['pic'];
            $vendor->jabatan_pic = $data['jabatan_pic'];
            $vendor->alamat = $data['alamat'];
            $vendor->provinsi = $data['provinsi'];
            $vendor->kab_kota = $data['kab_kota'];
            $vendor->kode_pos = $data['kode_pos'];
            $vendor->telepon = $data['telepon'];
            $vendor->fax = $data['fax'];
            $vendor->email = $data['email'];
            $vendor->website = $data['website'];
            $vendor->save();
        }
    }

    public function run()
    {
        $this->addDefaultVendors();
    }

    public function defaultVendors()
    {
        $data = [
            [
                "name" => "PT. ACCESS MICRO SYSTEM",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "Mangihut Ashianti",
                "jabatan_pic" => "Sales Manager",
                "alamat" => "Gedung Central Mas Pacific 3rd Floor Jl. Palmerah Utara No. 14",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. ADANI WICAKSANA MANDIRI SEJAHTERA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "BAPAK PRANOWO",
                "jabatan_pic" => "GENERAL MANAGER",
                "alamat" => "Jl. Daksinapati Raya No. 9, Rawamangun",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 4721 612",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. BERCA HERDAYAPERKASA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "Bp. Robert Antonius",
                "jabatan_pic" => "",
                "alamat" => "Jl. Abdul Muis No. 62 RT. 4 / RW. 3 Petojo Selatan Gambir",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. BISMACINDO PERKASA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "Bp. Budi Pranoto",
                "jabatan_pic" => "Direktur",
                "alamat" => "Jl. Raya Pesanggrahan No. 1128",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 5814 220",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. DIEBOLD INDONESIA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "Bp. Hendra Gabriel",
                "jabatan_pic" => "",
                "alamat" => "German Centre 1st Floor Suite #1350-1520 Jl. Kapt. Subijakto Dj. Blok COA No. 1 Bumi Serpong Damai",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. INTI BISNIS MULTIMEDIA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "",
                "jabatan_pic" => "",
                "alamat" => "Komplek Delta Building Blok A No. 30 - 31 Jl. Suryopranoto No. 1 - 9",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 3520 002",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. INTIKOM BERLIAN MUSTIKA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "",
                "jabatan_pic" => "",
                "alamat" => "GRAHA INTIKOM Jl. Kuningan Barat II no.11 Kuningan Barat - Mampang Prapatan",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 5297 1700",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. KUSUMO MEGAH JAYASAKTI",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "",
                "jabatan_pic" => "",
                "alamat" => "Jl. Sawah Lio Raya No. 8C",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 6339 360",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. MASTERSYSTEM INFOPARAMA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "",
                "jabatan_pic" => "",
                "alamat" => "Wisma Nugra Santana; 6th Floor Jl. Jend. Sudirman Kav.7-8",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 5790 1111",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. METRODATA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "Ibu Yuviani",
                "jabatan_pic" => "Sales Manager",
                "alamat" => "WISMA METROPOLITAN I LT.16. JL. JEND. SUDIRMAN KAV.29-31",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 5279 318",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. MITRA BISINFO UTAMA",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "",
                "jabatan_pic" => "",
                "alamat" => "Wisma BSG, Lt. 5 Jl. Abdul Muis 40",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "(021) 3448 848",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. MULTIKOM",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "Ibu Rina",
                "jabatan_pic" => "Manager",
                "alamat" => "Jakarta",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
            [
                "name" => "PT. PANCA PUTRA SOLUSINDO",
                "singkatan_nama" => "",
                "npwp" => "",
                "pic" => "",
                "jabatan_pic" => "",
                "alamat" => "Ruko Mangga Dua Square Blok D No. 25 Jl. Gunung Sahari Raya No. 1",
                "provinsi" => "",
                "kab_kota" => "",
                "kode_pos" => "",
                "telepon" => "",
                "fax" => "",
                "email" => "",
                "website" => "",
            ],
        ];
        return $data;
    }
}