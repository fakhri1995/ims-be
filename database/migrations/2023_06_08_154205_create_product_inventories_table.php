<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('product_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->string('price_option');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->softDeletes();
            $table->index('model_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_inventories');
    }
}
