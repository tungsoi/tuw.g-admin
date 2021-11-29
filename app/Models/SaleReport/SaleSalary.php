<?php

namespace App\Models\SaleReport;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SaleSalary extends Model
{
    protected $table = "sale_salaries";

    protected $fillable = [
        'report_id',
        'user_id',
        'new_customer',
        'old_customer',
        'all_customer',
        'owed_wallet_new_customer',
        'owed_wallet_old_customer',
        'owed_wallet_all_customer',
        'po_success',
        'po_success_new_customer',
        'po_success_old_customer',
        'po_success_all_customer',
        'po_success_service_fee',
        'po_success_total_rmb',
        'po_success_offer',
        'po_not_success',
        'po_not_success_new_customer',
        'po_not_success_old_customer',
        'po_not_success_all_customer',
        'po_not_success_service_fee',
        'po_not_success_owed',
        'po_not_success_deposited',
        'transport_order',
        'trs_kg_new_customer',
        'trs_m3_new_customer',
        'trs_kg_old_customer',
        'trs_m3_old_customer',
        'trs_kg_all_customer',
        'trs_m3_all_customer',
        'trs_amount_new_customer',
        'trs_amount_old_customer',
        'trs_amount_all_customer',
        'employee_salary',
        'employee_working_point',
        'new_customer_ids',
        'old_customer_ids',
    ];

    public function employee() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function report() {
        return $this->hasOne(Report::class, 'id', 'report_id');
    }
}
