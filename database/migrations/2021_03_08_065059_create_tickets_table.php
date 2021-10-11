<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_id');
            $table->unsignedBigInteger('subject_id');
            $table->integer('type');
            $table->integer('status');
            $table->dateTime('created_at');
            $table->dateTime('due_to')->nullable();
            $table->unsignedBigInteger('asign_to')->nullable();
            $table->unsignedBigInteger('requester_location');
            $table->unsignedBigInteger('requester');
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
        Schema::dropIfExists('tickets');
    }
}
