<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = "banks";

    protected $fillable = [
       'bank_name',
       'account_number',
       'card_holder'
    ];
}
