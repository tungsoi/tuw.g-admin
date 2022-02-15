<?php

namespace App\Models\ArReport;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = "ar_units";

    protected $fillable = [
        'title'
    ];
}
