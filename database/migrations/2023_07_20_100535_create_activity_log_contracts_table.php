<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log_contracts', function (Blueprint $table) {
            $table->id();
            $table->string("log_name");
            $table->text("notes")->nullable();
            $table->unsignedBigInteger("contract_id")->nullable();
            $table->unsignedBigInteger("causer_id");
            $table->text("properties")->nullable();
            $table->text("description")->nullable();
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_log_contracts');
    }
}
