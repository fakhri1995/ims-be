<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkdayHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workday_holidays', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("name");
            $table->unsignedBigInteger("workday_id");
            $table->date("from");
            $table->date("to");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workday_holidays');
    }
}
