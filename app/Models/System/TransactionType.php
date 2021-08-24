<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $table = "transaction_types";

    protected $fillable = [
        'name',
        'type'
    ];
}
