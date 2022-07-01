<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportCustomerReport extends Model
{
    protected $table = "transport_customer_reports";

    protected $fillable = [
        'title',
        'begin',
        'finish'
    ];
}
