<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogProjectsTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log_projects_tasks', function (Blueprint $table) {
            $table->id();
            $table->string("log_name");
            $table->string("notes")->nullable();
            $table->unsignedBigInteger("project_id")->nullable();
            $table->unsignedBigInteger("task_id")->nullable();
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
        Schema::dropIfExists('activity_log_projects_tasks');
    }
}
