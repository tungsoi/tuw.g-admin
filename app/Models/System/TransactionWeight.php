<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class TransactionWeight extends Model
{
    
    protected $table = "transaction_weights";

    protected $fillable = [
        'customer_id',
        'user_id_created',
        'content',
        'updated_user_id',
        'kg',
        'type'
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
}
