<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeSalaryColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_salary_columns', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->unsignedDecimal("percent")->nullable(); // in decimal
            $table->integer("type")->nullable(); // 1 : Penerimaan, 2 : Pengurangan
            $table->boolean("is_amount_for_bpjs")->default(false);
            $table->boolean("required")->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unsignedBigInteger('created_by');
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
        Schema::dropIfExists('employee_salary_columns');
    }
}
