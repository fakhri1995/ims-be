<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_service_kategori');
            $table->string('nama_service_item');
            $table->string('deskripsi_singkat');
            $table->string('deskripsi_lengkap');
            $table->boolean('is_publish');
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
        Schema::dropIfExists('service_items');
    }
}
