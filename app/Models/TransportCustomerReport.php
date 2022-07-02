<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TransportCustomerReport extends Model
{
    protected $table = "transport_customer_reports";

    protected $fillable = [
        'title',
        'begin',
        'finish'
    ];

    public function total() {

        $data = User::selectRaw(
            "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
            sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
        ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
        ->where("payment_orders.created_at", ">=", $this->begin)
        ->where("payment_orders.created_at", "<=", $this->finish)
        ->where('payment_orders.status', 'payment_export')
        ->groupBy("admin_users.id")
        ->orderBy("amount", "desc")
        ->get();

        return [
            'kg'    =>  number_format($data->sum('kg'), 1),
            'm3'    =>  number_format($data->sum('m3'), 3),
            'amount'    =>  number_format($data->sum('amount') - $data->sum('advance_drag'))
        ];
    }
}
