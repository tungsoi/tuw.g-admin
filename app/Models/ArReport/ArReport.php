<?php

namespace App\Models\ArReport;

use Illuminate\Database\Eloquent\Model;

class ArReport extends Model
{
    protected $table = "ar_reports";

    protected $fillable = [
        'title'
    ];
}
