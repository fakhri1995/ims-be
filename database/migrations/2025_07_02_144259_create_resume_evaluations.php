<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumeEvaluations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resume_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string("grammar_and_spelling");
            $table->string("content_validity");
            $table->string("skill_alignment");
            $table->string("flags");
            $table->string("improvement_points");
            $table->unsignedBigInteger("evaluated_by");
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
        Schema::dropIfExists('resume_evaluations');
    }
}
