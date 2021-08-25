<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use App\User;

class WeightPortal extends Model
{
    protected $table = "weight_portals";

    protected $fillable = [
        'user_created_id',
        'type', // 1: value, 2: history
        'value',
        'content',
        'user_receive_id'
    ];

    public function userCreate() {
        return $this->hasOne(User::class, 'id', 'user_created_id');
    }

    public function userReceive() {
        return $this->hasOne(User::class, 'id', 'user_receive_id');
    }
}
