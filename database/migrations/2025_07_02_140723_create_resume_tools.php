<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumeTools extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resume_tools', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("proficiency")->nullable();
            $table->string("details")->nullable();
            $table->string("certifications")->nullable();
            $table->string("category")->nullable();
            $table->integer("display_order")->default(2);
            $table->unsignedBigInteger("resume_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resume_tools');
    }
}

