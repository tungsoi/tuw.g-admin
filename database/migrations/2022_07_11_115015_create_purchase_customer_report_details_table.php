<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseCustomerReportDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_customer_report_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('report_id');
            $table->integer('user_id');
            $table->string('total_price_items');
            $table->string('total_service');
            $table->string('total_ship');
            $table->string('total_amount');
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
        Schema::dropIfExists('purchase_customer_report_details');
    }
}
