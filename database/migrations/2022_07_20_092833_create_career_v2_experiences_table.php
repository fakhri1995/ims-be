<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareerV2ExperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_v2_experiences', function (Blueprint $table) {
            $table->id();
            $table->integer('min'); // experince minimal, ex : 0
            $table->integer('max')->nullable(); // experince maksimal, ex : 1
            $table->string('str'); // experince string, ex : 0 - 1 tahun, lebih dari / kurang dari X tahun (jika salah satu min/max null)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('career_v2_experiences');
    }
}
