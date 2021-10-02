<?php

namespace App\Models\PaymentOrder;

use App\Models\TransportOrder\TransportCode;
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
        'transaction_note',
        'export_at',
        'user_export_id',
        'owed_purchase_order',
        'purchase_order_id',
        '_id',
        'user_cancel_id',
        'cancel_at'
    ];

    public function paymentCustomer() {
        return $this->hasOne(User::class, 'id', 'payment_customer_id');
    }

    public function userCreated() {
        return $this->hasOne(User::class, 'id', 'user_created_id');
    }

    public function transportCode() {
        return $this->hasMany(TransportCode::class, 'order_id', 'id');
    }

    public function statusText() {
        switch ($this->status) {
            case "payment_export": 
                return "Thanh toán xuất kho";
            case "payment_not_export": 
                return "Thanh toán chưa xuất kho";
            case "payment_temp":
                return "Thanh toán tạm";
            case "cancel":
                return "Huỷ";
        }
    }

    public function statusColor() {
        switch ($this->status) {
            case "payment_export": 
                return "success";
            case "payment_not_export": 
                return "warning";
            case "payment_temp":
                return "primary";
            case "cancel":
                return "danger";
        }
    }

    
}
