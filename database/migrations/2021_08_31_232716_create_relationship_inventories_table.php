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
        \Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('relationship_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('relationship_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('connected_id');
            $table->boolean('is_inverse');
            $table->integer('type_id');
            $table->index('subject_id');
            $table->index('connected_id');
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
