<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareerV2ApplyQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_v2_apply_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apply_id');
            $table->unsignedBigInteger('career_question_id');
            
            $table->jsonb('details');
            $table->dateTime('updated_at');
            
            $table->index('apply_id');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('career_v2_apply_questions');
    }
}
