<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelationshipInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationship_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('relationship_asset_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('connected_id');
            $table->unsignedBigInteger('detail_connected_id')->nullable();
            $table->integer('type_id');
            $table->boolean('is_inverse');
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
        Schema::dropIfExists('relationship_inventories');
    }
}
