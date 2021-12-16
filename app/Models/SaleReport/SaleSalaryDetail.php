<?php

namespace App\Models\SaleReport;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SaleSalaryDetail extends Model
{
    protected $table = "sale_salary_details";

    protected $fillable = [
        'sale_salary_id',
        'customer_id',
        'wallet',
        'po_success',
        'po_payment',
        'po_service_fee',
        'po_rmb',
        'po_offer',
        'po_not_success',
        'po_not_success_payment',
        'po_not_success_service_fee',
        'po_not_success_deposite',
        'po_not_success_owed',
        'trs',
        'trs_kg',
        'trs_m3',
        'trs_payment',
    ];

    public function customer() {
        return $this->hasOne(User::class, 'id', 'customer_id');
    }

    public function report() {
        return $this->hasOne(SaleSalary::class, 'id', 'sale_salary_id');
    }
}
