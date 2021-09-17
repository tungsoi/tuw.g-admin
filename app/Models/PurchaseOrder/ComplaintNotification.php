<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

class ComplaintNotification extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = "complaint_notifications";

    /**
     * Fields
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'content',
        'status',
        'order_id',
        'complaint_id'
    ];
}
