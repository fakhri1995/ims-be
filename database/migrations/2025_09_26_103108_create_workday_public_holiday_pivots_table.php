<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkdayPublicHolidayPivotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workday_public_holiday_pivots', function (Blueprint $table) {
            $table->unsignedBigInteger("workday_id");
            $table->unsignedBigInteger("holiday_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workday_public_holiday_pivots');
    }
}
