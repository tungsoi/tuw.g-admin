<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_salaries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('report_id')->nullable()->comment('');
            $table->integer('user_id')->nullable()->comment('');
            $table->text('new_customer_ids')->nullable()->comment('DS ID KH moi');
            $table->text('old_customer_ids')->nullable()->comment('DS ID KH cu');
            $table->integer('new_customer')->nullable()->comment('Tổng số khách hàng mới');
            $table->integer('old_customer')->nullable()->comment('Tổng số khách hàng cũ');
            $table->integer('all_customer')->nullable()->comment('Tổng số khách hàng');
            $table->string('owed_wallet_new_customer')->nullable()->comment('Âm ví khách hàng mới');
            $table->string('owed_wallet_old_customer')->nullable()->comment('Âm ví khách hàng cũ');
            $table->string('owed_wallet_all_customer')->nullable()->comment('Tổng âm ví');
            $table->integer('po_success')->nullable()->comment('Số lượng đơn order thành công');
            $table->integer('po_success_new_customer')->nullable()->comment('Doanh số order khách hàng mới');
            $table->integer('po_success_old_customer')->nullable()->comment('Doanh số order khách hàng cũ');
            $table->integer('po_success_all_customer')->nullable()->comment('Tổng doanh số order');
            $table->integer('po_success_service_fee')->nullable()->comment('Tổng phí dịch vụ');
            $table->string('po_success_total_rmb')->nullable()->comment('Tổng giá tệ đơn hàng order thành công');
            $table->integer('po_success_offer')->nullable()->comment('Tổng tiền đàm phán order thành công');
            $table->integer('po_not_success')->nullable()->comment('Số lượng đơn order chưa hoàn thành');
            $table->integer('po_not_success_new_customer')->nullable()->comment('Doanh số KH mới đơn chưa hoàn thành');
            $table->integer('po_not_success_old_customer')->nullable()->comment('Doanh số KH cũ đơn chưa hoàn thành');
            $table->integer('po_not_success_all_customer')->nullable()->comment('Doanh số tổng đơn chưa hoàn thành');
            $table->integer('po_not_success_service_fee')->nullable()->comment('Phí dịch vụ đơn chưa hoàn thành');
            $table->integer('po_not_success_owed')->nullable()->comment('Công nợ đơn chưa hoàn thành');
            $table->integer('po_not_success_deposited')->nullable()->comment('Tổng cọc đơn chưa hoàn thành');
            $table->integer('transport_order')->nullable()->comment('Tổng số đơn vận chuyển');
            $table->string('trs_kg_new_customer')->nullable()->comment('Tổng kg khách hàng mới');
            $table->string('trs_m3_new_customer')->nullable()->comment('Tổng m3 khách hàng mới');
            $table->string('trs_kg_old_customer')->nullable()->comment('Tổng kg khách hàng cũ');
            $table->string('trs_m3_old_customer')->nullable()->comment('Tổng m3 khách hàng cũ');
            $table->string('trs_kg_all_customer')->nullable()->comment('Tổng k3');
            $table->string('trs_m3_all_customer')->nullable()->comment('Tổng m3');
            $table->integer('trs_amount_new_customer')->nullable()->comment('Doanh số KH mới');
            $table->integer('trs_amount_old_customer')->nullable()->comment('Doanh số KH cũ');
            $table->integer('trs_amount_all_customer')->nullable()->comment('Tổng doanh số');
            $table->integer('employee_salary')->nullable()->comment('Lương thực nhận');
            $table->integer('employee_working_point')->nullable()->comment('Hiệu quả sau trừ lương');
            
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
        Schema::dropIfExists('sale_salaries');
    }
}
