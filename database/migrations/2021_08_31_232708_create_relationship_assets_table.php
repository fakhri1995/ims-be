<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelationshipAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationship_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('relationship_id');
            $table->boolean('is_inverse');
            $table->integer('type_id');
            $table->unsignedBigInteger('connected_id')->nullable();
            $table->softDeletes();
            $table->index('relationship_id');
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
        Schema::dropIfExists('relationship_assets');
    }
}
