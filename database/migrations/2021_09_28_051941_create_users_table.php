<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string("email")->unique();
            $table->string("password");
            $table->string("fullname");
            $table->string("profile_image")->nullable();
            $table->string("phone_number");
            $table->unsignedBigInteger("company_id");
            $table->tinyInteger("role");
            $table->boolean("is_enabled");
            $table->dateTime('created_time');
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
        Schema::dropIfExists('users');
    }
}
