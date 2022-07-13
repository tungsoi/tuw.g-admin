<?php

namespace App\Models\ReportWarehouse;

use App\Models\TransportOrder\TransportCode;
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
        'status',
        'input_count',
        'input_type',
        'input_price'
    ];

    public function transportCode() {
        return $this->hasMany(TransportCode::class, "title", "title");
    }

    public function amount_output() {
        $transport_codes = $this->transportCode;
        $sum_kg = 0;
        foreach ($transport_codes->where('payment_type', 1) as $code) {
            $sum_kg += $code->paymentOrder->price_kg * $code->kg;
        }

        $sum_m3 = 0;
        foreach ($transport_codes->where('payment_type', -1) as $code) {
            $sum_m3 += $code->paymentOrder->price_m3 * $code->m3_cal();
        }

        $customer_code = [];
        if ($transport_codes->count() > 0) {
            $customer_code = $transport_codes->pluck('customer_code_input')->toArray();
            $customer_code = array_unique(array_values($customer_code));
        }


        $data = [
            'kg'    =>  $sum_kg,
            'm3'    =>  $sum_m3,
            'amount'    =>  $sum_kg+$sum_m3,
            'customer_code' =>  $customer_code
        ];
        return $data;
    }

    public function amount_input() {
        return $this->input_count * $this->input_price;
    }
}
