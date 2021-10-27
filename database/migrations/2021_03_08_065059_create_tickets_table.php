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
            $table->unsignedBigInteger('subject_id');
            $table->integer('type_id');
            $table->integer('status_id');
            $table->dateTime('raised_at');
            $table->dateTime('closed_at')->nullable();
            $table->boolean('asign_to')->nullable();
            $table->unsignedBigInteger('asign_id')->nullable();
            $table->unsignedBigInteger('requester_id');
            $table->index('status_id');
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
