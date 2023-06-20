<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseQCDetailAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_q_c_detail_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_q_c_detail_id');
            $table->unsignedBigInteger('model_inventory_column_id');
            $table->boolean('is_checked');
            $table->index('purchase_q_c_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_q_c_detail_attributes');
    }
}
