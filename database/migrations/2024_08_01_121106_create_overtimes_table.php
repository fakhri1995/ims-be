<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();

            $table->integer('employee_id');
            $table->integer('manager_id');
            $table->integer('project_id');
            $table->integer('status_id');
            $table->string('notes')->nullable();

            $table->integer('duration');
            $table->time('start_at');
            $table->time('end_at');
            $table->dateTime('issued_date');
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
        Schema::dropIfExists('overtimes');
    }
}
