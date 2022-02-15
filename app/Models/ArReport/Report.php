<?php

namespace App\Models\ArReport;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = "ar_reports";

    protected $fillable = [
        'title'
    ];

    public function details() {
        return $this->hasMany(Detail::class, 'ar_report_id', 'id');
    }
}