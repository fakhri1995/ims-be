<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTalentPoolShareMark extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('talent_pool_share_marks', function (Blueprint $table) {
            $table->id();
            $table->integer('talent_pool_share_id', 0, 1);
            $table->integer('talent_pool_id', 0, 1);
            $table->integer('user_id', 0, 1);

            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('talent_pool_share_marks');
    }
}
