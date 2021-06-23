<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = "exchange_rates";

    protected $fillable = [
        'vnd',
        'rmb'
    ];
}
