<?php

namespace App\Models\OrderReport;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    protected $table = "order_reports";

    protected $fillable = [
        'order_at',
        'content',
        'type' // 1: ngay dat, 2: ngay thanh cong
    ];
}
