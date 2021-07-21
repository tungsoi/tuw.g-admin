<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderStatus extends Model
{
    protected $table = "purchase_order_statuses";

    protected $fillable = [
        'code',
        'name',
        'label'
    ];
}
