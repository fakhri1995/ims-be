<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('asset_code');
            $table->string('asset_name');
            $table->string('mig_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('invoice_label')->nullable();
            $table->string('status');
            $table->string('kepemilikan');
            $table->string('kondisi')->nullable();
            $table->date('tanggal_beli')->nullable();
            $table->integer('harga_beli')->nullable();
            $table->date('tanggal_efektif')->nullable();
            $table->integer('depresiasi')->nullable();
            $table->integer('nilai_sisa')->nullable();
            $table->integer('nilai_buku')->nullable();
            $table->integer('masa_pakai')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('departmen')->nullable();
            $table->string('service_point')->nullable();
            $table->string('gudang')->nullable();
            $table->string('used_by')->nullable();
            $table->string('managed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
