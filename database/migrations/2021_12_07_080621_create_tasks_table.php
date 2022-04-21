<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('task_type_id');
            $table->integer('location_id');
            $table->integer('reference_id')->nullable();
            $table->integer('created_by');
            $table->integer('group_id')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('on_hold_at')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->dateTime('first_deadline')->nullable();
            $table->dateTime('end_repeat_at')->nullable();
            $table->tinyInteger('status');
            $table->tinyInteger('repeat')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_replaceable');
            $table->boolean('is_uploadable');
            $table->boolean('is_from_ticket');
            $table->boolean('is_visible');
            $table->index('location_id');
            $table->index('task_type_id');
            $table->index('status');
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
        Schema::dropIfExists('tasks');
    }
}
