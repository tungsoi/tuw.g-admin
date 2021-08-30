<?php

namespace App\Models\PaymentOrder;

use Illuminate\Database\Eloquent\Model;
use App\User;

class PaymentOrder extends Model
{
    protected $table = "payment_orders";

    protected $fillable = [
        'order_number',
        'status',
        'amount',
        'total_kg',
        'total_m3',
        'total_v',
        'total_advance_drag',
        'user_created_id',
        'payment_customer_id',
        'internal_note',
        'discount_value',
        'discount_type',
        'price_kg',
        'price_m3',
        'price_v',
        'is_sub_customer_wallet_weight',
        'total_sub_wallet_weight',
        'current_rate',
        'transaction_note'
    ];

    public function paymentCustomer() {
        return $this->hasOne(User::class, 'id', 'payment_customer_id');
    }
}
