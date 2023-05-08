<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();
            $table->text("description")->nullable();
            $table->unsignedBigInteger("status_id")->nullable();
            $table->unsignedBigInteger("project_id")->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
        Schema::dropIfExists('project_tasks');
    }
}
