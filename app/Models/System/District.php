<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = "districts";

    protected $fillable = [
        'district_id',
        'name',
        'type',
        'province_id'
    ];

    public function province() {
        return $this->hasOne('App\Models\System\Province', 'province_id', 'province_id');
    }
}
