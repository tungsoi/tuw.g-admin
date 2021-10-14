<?php

namespace App\Models\FinancialReport;

use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    protected $table = "financial_report_details";

    protected $fillable = [
        'title'
    ];
}
