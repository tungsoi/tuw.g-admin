<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class TransportLine extends Model
{
    protected $table = "transport_lines";

    protected $fillable = [
        'name', 
        'code'
    ];
}
