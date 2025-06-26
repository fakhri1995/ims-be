<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationAndHonorsAndRelevantCourseworkToResumeEducationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resume_educations', function (Blueprint $table) {
            $table->string('location')->nullable();
            $table->string('honors')->nullable();
            $table->string('relevant_coursework')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resume_educations', function (Blueprint $table) {
            //
        });
    }
}
