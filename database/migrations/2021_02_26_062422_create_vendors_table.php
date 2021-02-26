<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('singkatan_nama')->nullable();
            $table->string('npwp')->nullable();
            $table->string('pic');
            $table->string('jabatan_pic');
            $table->string('alamat');
            $table->date('provinsi')->nullable();
            $table->integer('kab_kota')->nullable();
            $table->date('kode_pos')->nullable();
            $table->integer('telepon');
            $table->integer('fax')->nullable();
            $table->integer('email')->nullable();
            $table->integer('website')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}
