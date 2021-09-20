<?php

namespace App\Models\ReportWarehouse;

use Illuminate\Database\Eloquent\Model;

class ReportWarehousePortal extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = "report_warehouse_portals";

    /**
     * Fields
     */
    protected $fillable = [
        'date',
        'title',
        'count',
        'weight',
        'cublic_meter',
        'offer_weight',
        'offer_cublic_meter',
        'line',
        'note',
        'status'
    ];
}
