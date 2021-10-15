<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = "transactions";

    protected $fillable = [
        'customer_id',
        'user_id_created',
        'type_recharge',
        'content',
        'order_type',
        'note',
        'updated_user_id',
        'money',
        'bank_id',
        'created_at'
    ];

    public function userCreated() {
        return $this->hasOne('App\User', 'id', 'user_id_created');
    }

    public function userUpdated() {
        return $this->hasOne('App\User', 'id', 'updated_user_id');
    }

    public function customer() {
        return $this->hasOne('App\User', 'id', 'customer_id');
    }

    public function type() {
        return $this->hasOne('App\Models\System\TransactionType', 'id', 'type_recharge');
    }

    public function paymentOrder() {
        return $this->hasOne('App\Models\PaymentOrder\PaymentOrder', 'transaction_note', 'content');
    }

    public function bank() {
        return $this->hasOne('App\Models\System\Bank', 'id', 'bank_id');
    }
}
