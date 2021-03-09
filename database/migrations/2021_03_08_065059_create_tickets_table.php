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
            $table->unsignedBigInteger('subject_type_id');
            $table->string('type');
            $table->string('status')->nullable();
            $table->string('priority')->nullable();
            $table->string('source')->nullable();
            $table->string('urgency')->nullable();
            $table->string('impact')->nullable();
            $table->date('due_to')->nullable();
            $table->unsignedBigInteger('asign_to')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
