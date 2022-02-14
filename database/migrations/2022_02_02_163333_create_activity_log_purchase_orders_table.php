<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('activity_log_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('log_name');
            $table->string('description');
            $table->unsignedBigInteger('purchase_order_id');
            $table->dateTime('created_at');
            $table->unsignedBigInteger('causer_id');
            $table->unsignedBigInteger('connectable_id');
            $table->string('connectable_type');
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
        Schema::dropIfExists('activity_log_purchase_orders');
    }
}
