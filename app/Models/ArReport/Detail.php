<?php

namespace App\Models\ArReport;

use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    protected $table = "ar_report_details";

    protected $fillable = [
        'ar_report_id',
        'category_id',
        'unit_id',
        'content',
        'note'
    ];

    public function unit() {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }

    public function category() {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}