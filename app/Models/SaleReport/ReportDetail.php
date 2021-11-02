<?php

namespace App\Models\SaleReport;

use Illuminate\Database\Eloquent\Model;

class ReportDetail extends Model
{
    protected $table = "sale_report_details_bk";

    protected $fillable = [
        'sale_report_id',
        'user_id',
        'total_customer',
        'new_customer',
        'total_customer_wallet',
        'success_order',
        'success_order_payment',
        'success_order_payment_rmb',
        'success_order_new_customer',
        'success_order_payment_new_customer',
        'success_order_service_fee',
        'processing_order',
        'processing_order_payment',
        'processing_order_payment_rmb',
        'processing_order_new_customers',
        'processing_order_payment_new_customer',
        'processing_order_service_fee',
        'total_transport_weight',
        'total_transport_weight_new_customer',
        'total_transport_fee',
        'total_transport_fee_new_customer',
        'owed_processing_order_payment',
        'transport_order',
        'transport_order_new_customer',
        'log_customers',
        'log_new_customer',
        'log_success_order',
        'log_success_order_new_customer',
        'log_processing_order',
        'log_processing_order_new_customer',
        'log_transport_order',
        'log_transport_order_new_customer',
        'offer_cn',
        'offer_vn'
    ];


    public function user() {
        return $this->hasOne('\App\User', 'id', 'user_id');
    }

    public function report() {
        return $this->hasOne('\App\Models\SaleReport', 'id', 'sale_report_id');
    }
}
