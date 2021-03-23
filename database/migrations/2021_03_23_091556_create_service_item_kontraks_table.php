<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceItemKontraksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_item_kontraks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_service_item');
            $table->unsignedBigInteger('id_kontrak');
            $table->unsignedBigInteger('id_terms_of_payment');
            $table->integer('harga');
            $table->boolean('is_active');
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
        Schema::dropIfExists('service_item_kontraks');
    }
}
