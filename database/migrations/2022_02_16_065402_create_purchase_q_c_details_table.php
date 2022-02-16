<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseQCDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_q_c_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("purchase_q_c_id")->nullable();
            $table->unsignedBigInteger("model_id");
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->tinyInteger('status');
            $table->string('description')->nullable();
            $table->index('purchase_q_c_id');
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
        Schema::dropIfExists('purchase_q_c_details');
    }
}
