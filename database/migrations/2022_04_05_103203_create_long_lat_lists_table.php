<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongLatListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_lat_lists', function (Blueprint $table) {
            $table->id();
            $table->decimal('longitude', 7, 4);
            $table->decimal('latitude', 7, 4);
            $table->text('geo_location')->nullable();
            $table->tinyInteger('attempts');

            $table->index('longitude');
            $table->index('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('long_lat_lists');
    }
}
