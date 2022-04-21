<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fileable_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('fileable_type');
            $table->string('link');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('fileable_id');
            $table->index('fileable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
