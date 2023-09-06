<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['initial', 'addendum']);
            $table->unsignedBigInteger('contract_id');
            $table->string('code_number')->nullable();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->date('initial_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('extras')->default(NULL);
            $table->boolean('is_posted')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_histories');
    }
}
