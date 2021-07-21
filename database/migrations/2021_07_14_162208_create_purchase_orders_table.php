<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->text('shop_name')->nullable()->comment('ten shop');
            $table->string('order_number')->nullable()->comment('mã đơn hàng');
            $table->integer('customer_id')->nullable()->comment('mã khách hàng');
            $table->integer('status')->nullable()->comment('trạng thái');
            $table->string('deposited')->nullable()->comment('tiền đã cọc');
            $table->text('customer_note')->nullable()->comment('khách hàng ghi chú');
            $table->text('admin_note')->nullable()->comment('admin ghi chú');
            $table->text('internal_note')->nullable()->comment('ghi chú nội bộ');
            $table->integer('warehouse_id')->nullable()->comment('kho nhan hang');
            $table->string('current_rate')->nullable()->comment('ty gia');
            $table->integer('supporter_order_id')->nullable()->comment('nv order');
            $table->string('purchase_order_service_fee')->nullable()->comment('phi dich vu');
            $table->timestamp('deposited_at')->nullable()->comment('ngay dat coc');
            $table->timestamp('order_at')->nullable()->comment('ngay dat hang');
            $table->timestamp('success_at')->nullable()->comment('ngay hoan thanh');
            $table->timestamp('cancle_at')->nullable()->comment('ngay huy don');
            $table->string('final_payment')->nullable()->comment('tien dam phan');
            $table->integer('user_created_id')->nullable()->comment('user tao don');
            $table->integer('user_deposited_at')->nullable()->comment('user dat coc');
            $table->integer('user_order_at')->nullable()->comment('user dat hang');
            $table->integer('user_success_at')->nullable()->comment('user xac nhan hoan thanh / thanh toan');
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
        Schema::dropIfExists('purchase_orders');
    }
}
