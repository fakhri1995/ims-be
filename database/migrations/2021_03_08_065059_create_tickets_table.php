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
            // $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('ticketable_id');
            $table->unsignedBigInteger('created_by');
            $table->string('ticketable_type');
            $table->integer('resolved_times')->nullable();
            $table->integer('status');
            $table->dateTime('raised_at');
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('deadline')->nullable();
            // $table->index('ticketable_id');
            // $table->dateTime('raised_at');
            // $table->string('assignable_type')->nullable();
            // $table->unsignedBigInteger('assignable_id')->nullable();
            // $table->unsignedBigInteger('requester_id');
            // $table->index('requester_id');
            // $table->index('raised_at');
            // $table->index('assignable_id');
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
