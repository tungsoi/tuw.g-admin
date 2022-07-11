<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseCustomerReport extends Model
{
    protected $table = "purchase_customer_reports";

    protected $fillable = [
        'title',
        'begin',
        'finish',
        'kg'
    ];

    public function details() {
        return $this->hasMany('App\Models\PurchaseCustomerReportDetail', 'report_id', 'id');
    }
}
