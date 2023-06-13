<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->string("is_employee_active")->nullable();
            $table->string("contract_name")->nullable();
            $table->string("role_id")->nullable();
            $table->string("contract_status_id")->nullable();
            // dokumen_kontrak morph files
            $table->string("pkwt_reference")->nullable();
            $table->date("join_at")->nullable();
            $table->date("contract_start_at")->nullable();
            $table->date("contract_end_at")->nullable();
            $table->string("placement")->nullable();
            $table->string("new_office")->nullable();
            $table->date("resign_at")->nullable();
            $table->integer("annual_leave")->nullable();
            $table->integer("gaji_pokok")->nullable();
            $table->integer("bpjs_ks")->nullable();
            $table->integer("bpjs_tk_jht")->nullable();
            $table->integer("bpjs_tk_jkk")->nullable();
            $table->integer("bpjs_tk_jkm")->nullable();
            $table->integer("bpjs_tk_jp")->nullable();
            $table->integer("pph21")->nullable();
            $table->unsignedBigInteger('employee_id'); //refernece employee.id
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
        Schema::dropIfExists('employee_contracts');
    }
}