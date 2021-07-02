<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transport_code')->nullable();
            $table->string('kg')->nullable()->default(0);
            $table->string('length')->nullable()->default(0);
            $table->string('width')->nullable()->default(0);
            $table->string('height')->nullable()->default(0);
            $table->integer('transport_order_id')->nullable();
            $table->string('v')->nullable()->default(0);
            $table->string('m3')->nullable()->default(0);
            $table->string('price_service')->nullable()->default(0);
            $table->string('advance_drag')->nullable()->default(0);
            $table->integer('status');
            $table->timestamp('china_recevie_at')->nullable();
            $table->timestamp('vietnam_recevie_at')->nullable();
            $table->timestamp('waitting_payment_at')->nullable();
            $table->timestamp('payment_at')->nullable();
            $table->timestamp('begin_swap_warehouse_at')->nullable();
            $table->timestamp('finish_swap_warehouse_at')->nullable();
            $table->timestamp('china_receive_user_id')->nullable();
            $table->timestamp('vietnam_receive_user_id')->nullable();
            $table->timestamp('payment_user_id')->nullable();
            $table->timestamp('begin_swap_user_id')->nullable();
            $table->timestamp('finish_swap_user_id')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('customer_code_input')->nullable();
            $table->integer('ware_house_id')->nullable();
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
        Schema::dropIfExists('transport_codes');
    }
}
