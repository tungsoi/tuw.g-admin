<?php

namespace App\Console\Commands\SyncData;

use App\Models\PaymentOrder\PaymentOrder as PaymentOrderPaymentOrder;
use App\Models\SyncData\AlilogiTransportOrder;
use App\Models\TransportOrder\TransportCode;
use Illuminate\Console\Command;

class PaymentOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:payment-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $transportOrders = AlilogiTransportOrder::orderBy('id', 'desc')->get();

        foreach ($transportOrders as $order) {
            echo $order->order_number . "\n";
            $data = [
                'order_number'  =>  $order->order_number,
                'status'    =>  "payment_export",
                'amount'    =>  str_replace(",", "", number_format($order->final_total_price, 0)),
                'total_kg'  =>  $order->transport_kg,
                'total_m3'  =>  $order->transport_cublic_meter,
                'total_v'   =>  $order->transport_volume,
                'total_advance_drag'    =>  $order->transport_advance_drag,
                'user_created_id'   =>  $order->user_created_id,
                'payment_customer_id'   =>  $order->payment_customer_id,
                'internal_note' =>  $order->internal_note,
                'discount_value'    =>  $order->discount_value,
                'discount_type' =>  $order->discount_type,
                'price_kg'  =>  $this->getPriceService(1, $order->id),
                'price_m3'    =>  $this->getPriceService(-1, $order->id),
                'price_v'     =>  $this->getPriceService(0, $order->id),
                'is_sub_customer_wallet_weight' =>  0,
                'total_sub_wallet_weight'   =>  0,
                'current_rate'  =>  $order->current_rate,
                'transaction_note'  =>  null,
                'export_at' =>  $order->created_at,
                'user_export_id'    =>  $order->user_created_id,
                'owed_purchase_order'   =>  0,
                'purchase_order_id' =>  null,
                '_id'   =>  $order->id
            ];

            PaymentOrderPaymentOrder::firstOrCreate($data);
        }

    }

    public function getPriceService($type, $orderId) {
        $flag = TransportCode::where('order_id', $orderId)->where('payment_type', $type)->first();
        return $flag ? $flag->price_service : 0;
    }
}
