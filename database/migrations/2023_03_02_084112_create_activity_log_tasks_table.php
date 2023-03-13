<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log_tasks', function (Blueprint $table) {
            $table->id();
            $table->string("log_name");
            $table->string("notes")->nullable();
            $table->unsignedBigInteger("subject_id");
            $table->unsignedBigInteger("causer_id");
            $table->text("properties")->nullable();
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
        Schema::dropIfExists('activity_log_tasks');
    }
}
