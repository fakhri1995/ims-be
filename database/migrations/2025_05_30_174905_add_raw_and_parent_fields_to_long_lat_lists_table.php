<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRawAndParentFieldsToLongLatListsTable extends Migration
{
    public function up()
    {
        Schema::table('long_lat_lists', function (Blueprint $table) {
            // Flag to mark processed records
            $table->boolean('is_nearby_processed')->default(false)->after('attempts');
            // Parent reference
            $table->unsignedBigInteger('parent_id')->nullable()->after('is_nearby_processed');
            $table->foreign('parent_id')->references('id')->on('long_lat_lists')->onDelete('set null');
            // Preserve original coordinates & geo data
            $table->decimal('raw_latitude', 7, 4)->nullable()->after('parent_id');
            $table->decimal('raw_longitude', 7, 4)->nullable()->after('raw_latitude');
            $table->text('raw_geo_location')->nullable()->after('raw_longitude');
            // Composite index for faster bounding-box queries
            $table->index(['longitude', 'latitude'], 'long_lat_lists_long_lat_index');
        });
    }

    public function down()
    {
        Schema::table('long_lat_lists', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('long_lat_lists_long_lat_index');
            $table->dropColumn([
                'is_nearby_processed',
                'parent_id',
                'raw_latitude',
                'raw_longitude',
                'raw_geo_location',
            ]);
        });
    }
}

