<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_id')->nullable();
            $table->string('purchase_order_number');
            $table->dateTime('purchase_order_date');
            $table->dateTime('arrived_date')->nullable();
            $table->integer('vendor_id');
            $table->text('description')->nullable();
            $table->integer('created_by');
            $table->smallInteger('year')->nullable();
            $table->tinyInteger('status');
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
        Schema::dropIfExists('purchase_orders');
    }
}
