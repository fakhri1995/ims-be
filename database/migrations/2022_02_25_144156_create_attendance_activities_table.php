<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attendance_form_id');
            // $table->unsignedBigInteger('attendance_project_id');
            // $table->integer('attendance_project_status_id')->nullable();
            $table->jsonb('details');
            $table->dateTime('updated_at');
            
            $table->index('user_id');
            $table->index('updated_at');
            // $table->index('attendance_project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_activities');
    }
}
