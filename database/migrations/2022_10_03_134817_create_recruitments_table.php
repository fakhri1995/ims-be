<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('university');
            $table->unsignedBigInteger('recruitment_role_id');
            $table->unsignedBigInteger('recruitment_jalur_daftar_id');
            $table->unsignedBigInteger("recruitment_stage_id");
            $table->unsignedBigInteger("recruitment_status_id");
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unsignedBigInteger('created_by');
            $table->text("lampiran");
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
        Schema::dropIfExists('recruitments');
    }
}
