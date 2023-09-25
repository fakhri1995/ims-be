<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumeAchievementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resume_achievements', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("organizer");
            $table->date('year');
            $table->unsignedBigInteger("resume_id");
            $table->unsignedInteger('display_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resume_achievements');
    }
}
