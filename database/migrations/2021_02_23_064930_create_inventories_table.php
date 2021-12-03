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
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->integer('status_condition');
            $table->integer('status_usage');
            $table->integer('location')->nullable();
            $table->string('deskripsi')->nullable();
            $table->integer('manufacturer_id')->nullable();
            $table->string('mig_id');
            $table->string('serial_number')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('model_id');
            $table->index('location');
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
