<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // id card file
            $table->string("name")->nullable();
            $table->string("nip")->nullable();
            $table->string("nik")->nullable();
            $table->string("alias")->nullable();
            $table->string("email_office")->nullable();
            $table->string("email_personal")->nullable();
            $table->string("domicile")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("birth_place")->nullable();
            $table->date("birth_date")->nullable();
            $table->string("gender")->nullable();
            $table->enum("blood_type",["A","B","AB","O"])->nullable();
            $table->unsignedBigInteger("marital_status")->nullable(); //referance to status perkawinan
            $table->integer("number_of_children")->nullable();
            $table->string("bio_mother_name")->nullable();
            $table->string("npwp")->nullable();
            $table->string("bpjs_kesehatan")->nullable();
            $table->string("bpjs_ketenagakerjaan")->nullable();
            $table->string("acc_number_bukopin")->nullable();
            $table->string("acc_number_another")->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean("is_posted")->default('0');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updating_by');
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
        Schema::dropIfExists('employees');
    }
}
