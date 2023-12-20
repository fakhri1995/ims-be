<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareerV2QuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_v2_questions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('career_id');
            $table->text('description');
            $table->jsonb('details');
            $table->unsignedBigInteger('created_by');
            $table->dateTime('updated_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('career_v2_questions');
    }
}
