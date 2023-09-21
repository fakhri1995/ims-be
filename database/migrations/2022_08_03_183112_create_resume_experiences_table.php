<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumeExperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resume_experiences', function (Blueprint $table) {
            $table->id();
            $table->string("role");
            $table->string("company");
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description');
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
        Schema::dropIfExists('resume_experiences');
    }
}
