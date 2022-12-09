<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormSolutionDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_solution_details', function (Blueprint $table) {
            $table->id();
            $table->string('kind_of_product');
            $table->string('urgently');
            $table->text('list_product');
            $table->string('level_employee');
            $table->integer('many_product');
            $table->integer('maximum_budget');
            $table->string('time_used');
            $table->text('details');
            $table->unsignedBigInteger('form_solution_id'); //refernece form solution id
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
        Schema::dropIfExists('form_solution_details');
    }
}
