<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->integer('product_type');
            $table->string('product_id');
            $table->string('pic_name')->nullable();
            $table->string('pic_contact')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->string('problem')->nullable();
            $table->dateTime('incident_time')->nullable();
            $table->jsonb('files')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('incidents');
    }
}
