<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRawAttemptsToLongLatListsTable extends Migration
{
    public function up()
    {
        Schema::table('long_lat_lists', function (Blueprint $table) {
            // Preserve original attempts value
            $table->integer('raw_attempts')->nullable()->after('raw_geo_location');
        });
    }

    public function down()
    {
        Schema::table('long_lat_lists', function (Blueprint $table) {
            $table->dropColumn('raw_attempts');
        });
    }
}