<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->unsignedBigInteger("top_parent_id")->nullable();
            $table->string("name");
            $table->string("phone_number");
            $table->string("address");
            $table->tinyInteger("role");
            $table->boolean("is_enabled");
            $table->dateTime('created_time');
            $table->string('singkatan');
            $table->date('tanggal_pkp')->nullable();
            $table->string('penanggung_jawab');
            $table->string('npwp');
            $table->string('fax');
            $table->string('email');
            $table->string('website');
            $table->time('check_in_time')->nullable();	
            $table->softDeletes();
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
