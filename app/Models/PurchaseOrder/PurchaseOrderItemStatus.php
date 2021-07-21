<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItemStatus extends Model
{
    protected $table = "purchase_order_item_statuses";

    protected $fillable = [
        'code',
        'name',
        'label'
    ];
}
