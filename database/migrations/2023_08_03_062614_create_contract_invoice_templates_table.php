<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractInvoiceTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->jsonb('details');
            $table->integer("invoice_period");
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('updated_by');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_invoice_templates');
    }
}
