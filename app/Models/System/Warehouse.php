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
        'is_active',
        'is_default',
        'user_id', 
        'employees'
    ];

    public function userLead() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function getEmployeesAttribute($value)
    {
        return explode(',', $value);
    }

    public function setEmployeesAttribute($value)
    {
        $this->attributes['employees'] = implode(',', $value);
    }
}
