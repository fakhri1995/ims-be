<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareerV2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_v2', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('career_role_type_id');
            $table->unsignedBigInteger('recruitment_role_id');
            $table->unsignedBigInteger('career_experience_id');
            $table->integer('salary_min'); // gaji minimal;
            $table->integer('salary_max'); // gaji maksimal;
            $table->text('overview');
            $table->text('description');
            $table->text('qualification');
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_posted');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->index('slug');
            $table->index('created_by');
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
        Schema::dropIfExists('career_v2');
    }
}
