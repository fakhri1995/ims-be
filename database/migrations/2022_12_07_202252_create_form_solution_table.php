<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormSolutionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_solutions', function (Blueprint $table) {
            $table->id();
            $table->string("company_name")->nullable();
            $table->integer("many_people")->nullable();
            $table->string("contact_name")->nullable();
            $table->string("email")->nullable();
            // dokumen_kontrak morph files
            $table->string("phone_number")->nullable();
            $table->text("kind_project")->nullable();
            $table->string("type_project")->nullable();
            $table->string("purpose")->nullable();
            $table->integer("budget_from")->nullable();
            $table->integer("budget_to")->nullable();
            $table->string("attachment")->nullable();
            $table->dateTime("meeting_schedule")->nullable();
            $table->string("kind_form")->nullable();
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
        Schema::dropIfExists('form_solutions');
    }
}
