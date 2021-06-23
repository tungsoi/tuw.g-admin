<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    const LIVE = 1;
    const CLOSE = 0;

    const LIVE_TXT = "Mở";
    const CLOSE_TXT = "Đóng";

    protected $table = "ware_houses";

    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active'
    ];

    public function userLead() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
