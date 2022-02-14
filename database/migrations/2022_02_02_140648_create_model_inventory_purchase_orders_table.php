<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelInventoryPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_inventory_purchase_order', function (Blueprint $table) {
            $table->unsignedBigInteger('model_inventory_id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('price');
            $table->integer('quantity');
            $table->tinyInteger('warranty_period');
            $table->string('warranty_descripition')->nullable();
            $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_inventory_purchase_order');
    }
}
