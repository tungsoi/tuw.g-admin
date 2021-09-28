<?php

namespace App\Models\SaleReport;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{   
    /**
     * Table name
     *
     * @var string
     */
    protected $table = "sale_reports";

    /**
     * Fields
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'begin_date',
        'finish_date',
        'user_created_id',
        'status',
        'order'
    ];

    public function reportDetail() {
        return $this->hasMany('App\Models\SaleReport\ReportDetail', 'sale_report_id', 'id');
    }
}
