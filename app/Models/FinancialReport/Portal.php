<?php

namespace App\Models\FinancialReport;

use Illuminate\Database\Eloquent\Model;

class Portal extends Model
{
    protected $table = "financial_report_portals";

    protected $fillable = [
        'title'
    ];
}
