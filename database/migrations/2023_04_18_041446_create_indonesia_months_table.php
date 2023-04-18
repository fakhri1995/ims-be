<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIndonesiaMonthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indonesia_months', function (Blueprint $table) {
            $table->integer('month_number')->unsigned();
            $table->string("month");
            $table->primary('month_number');
        });

        DB::table('indonesia_months')->insert([
            ["month_number" => "1", "month" => "Januari"],
            ["month_number" => "2", "month" => "Februari"],
            ["month_number" => "3", "month" => "Maret"],
            ["month_number" => "4", "month" => "April"],
            ["month_number" => "5", "month" => "Mei"],
            ["month_number" => "6", "month" => "Juni"],
            ["month_number" => "7", "month" => "Juli"],
            ["month_number" => "8", "month" => "Agustus"],
            ["month_number" => "9", "month" => "September"],
            ["month_number" => "10", "month" => "Oktober"],
            ["month_number" => "11", "month" => "November"],
            ["month_number" => "12", "month" => "Desember"],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indonesia_months');
    }
}
