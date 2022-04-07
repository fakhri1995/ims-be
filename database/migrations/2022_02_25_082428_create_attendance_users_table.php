<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->string('long_check_in');
            $table->string('lat_check_in');
            $table->string('long_check_out')->nullable();
            $table->string('lat_check_out')->nullable();
            $table->string('evidence');
            $table->boolean('is_wfo');
            $table->boolean('checked_out_by_system');

            $table->index('user_id');
            $table->index('check_in');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_users');
    }
}
