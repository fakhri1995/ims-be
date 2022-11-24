<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_devices', function (Blueprint $table) {
            $table->id();
            $table->string("id_number")->nullable();
            $table->string("device_name")->nullable();
            $table->string("device_type")->nullable();
            $table->string("serial_number")->nullable();
            $table->unsignedBigInteger('employee_inventory_id'); //refernece employee_inventory.id
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
        Schema::dropIfExists('employee_devices');
    }
}
