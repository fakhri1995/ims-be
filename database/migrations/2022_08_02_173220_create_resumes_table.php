<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("telp");
            $table->string("email");

            // $table->string("role");

            $table->string("city");
            $table->string("province");
            $table->string("location")->nullable();
            $table->string("linkedin");
            $table->string("summary")->nullable();
            $table->unsignedBigInteger("assessment_id")->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->unsignedBigInteger('owner_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resumes');
    }
}
