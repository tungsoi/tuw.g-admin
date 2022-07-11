<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseCustomerReportDetail extends Model
{
    protected $table = "purchase_customer_report_details";

    protected $fillable = [
        'report_id',
        'user_id',
        'total_price_items',
        'total_service',
        'total_ship',
        'total_amount',
        'count'
    ];

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function mainReport() {
        return $this->hasOne('App\Models\PurchaseCustomerReport', 'id', 'report_id');
    }
}
