<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = "provinces";

    protected $fillable = [
        'province_id',
        'name',
        'type',
    ];

    public function districts() {
        return $this->hasMany('App\Models\System\District', 'province_id', 'province_id');
    }
}
