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
        \Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('ticketable_id');
            $table->string('ticketable_type');
            $table->integer('resolved_times')->nullable();
            $table->dateTime('closed_at')->nullable();
            // $table->index('ticketable_id');
            // $table->integer('status_id');
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
