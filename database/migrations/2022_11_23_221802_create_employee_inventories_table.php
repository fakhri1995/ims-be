<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_inventories', function (Blueprint $table) {
            $table->id();
            $table->string("id_number")->nullable();
            $table->string("device_name")->nullable();
            $table->string("referance_invertory")->nullable();
            $table->string("device_type")->nullable();
            $table->string("serial_number")->nullable();
            $table->date("delivery_date")->nullable();
            $table->date("return_date")->nullable();
            $table->string("pic_delivery")->nullable();
            $table->string("pic_return")->nullable();
            // delivery_file morph files
            // return_file morph files
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
        Schema::dropIfExists('employee_inventories');
    }
}
