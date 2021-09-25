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
            $flag = PaymentOrderPaymentOrder::whereOrderNumber($order->order_number)->first();

            if ($flag) {
                if ($flag->created_at != $order->created_at) {
                    echo $order->order_number . "\n";
                    $flag->created_at = $order->created_at;
                    $flag->save();
                }
            }
        }

    }

    public function getPriceService($type, $orderId) {
        $flag = TransportCode::where('order_id', $orderId)->where('payment_type', $type)->first();
        return $flag ? $flag->price_service : 0;
    }
}
