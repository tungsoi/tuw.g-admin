<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class CustomerPercentService extends Model
{
    protected $table = "customer_percent_services";

    protected $fillable = [
        'name',
        'percent'
    ];
}
