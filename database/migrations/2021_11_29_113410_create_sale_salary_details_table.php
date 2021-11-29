<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleSalaryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_salary_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sale_salary_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('wallet')->nullable();
            $table->string('po_success')->nullable();
            $table->string('po_payment')->nullable();
            $table->string('po_service_fee')->nullable();
            $table->string('po_rmb')->nullable();
            $table->string('po_offer')->nullable();
            $table->string('po_not_success')->nullable();
            $table->string('po_not_success_payment')->nullable();
            $table->string('po_not_success_service_fee')->nullable();
            $table->string('po_not_success_deposite')->nullable();
            $table->string('po_not_success_owed')->nullable();
            $table->string('trs')->nullable();
            $table->string('trs_kg')->nullable();
            $table->string('trs_m3')->nullable();
            $table->string('trs_payment')->nullable();
            
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
        Schema::dropIfExists('sale_salary_details');
    }
}
