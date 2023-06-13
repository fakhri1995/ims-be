<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceFormUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_form_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_form_id');
            $table->unsignedBigInteger('user_id');

            $table->index('attendance_form_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_form_user');
    }
}
