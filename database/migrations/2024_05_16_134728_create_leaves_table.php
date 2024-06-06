<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            $table->integer('type');
            $table->integer('employee_id');
            $table->integer('delegate_id')->nullable();
            $table->string('status');
            $table->string('notes')->nullable();

            $table->integer('duration');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('issued_date');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}
