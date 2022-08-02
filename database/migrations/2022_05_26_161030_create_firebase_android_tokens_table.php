<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFirebaseAndroidTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firebase_android_tokens', function (Blueprint $table) {
            $table->string('token');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->dateTime('expires_at');
            
            $table->primary('token');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('firebase_android_tokens');
    }
}
