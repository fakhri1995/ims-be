<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("contract_template_id");
            $table->jsonb("invoice_attribute")->default('[]');
            $table->jsonb("service_attribute")->default('[]');
            $table->string("invoice_number")->nullable();
            $table->string("invoice_name")->nullable();
            $table->date("invoice_raise_at");
            $table->integer("invoice_total")->default(0);
            $table->boolean("is_posted")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_invoices');
    }
}
