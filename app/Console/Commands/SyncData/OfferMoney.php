<?php

namespace App\Console\Commands\SyncData;

use App\Models\PaymentOrder\PaymentOrder as PaymentOrderPaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SyncData\AlilogiTransportOrder;
use App\Models\TransportOrder\TransportCode;
use Illuminate\Console\Command;

class OfferMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:offer_money';

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
        $orders = PurchaseOrder::select('id', 'final_payment', 'offer_vn', 'offer_cn')
            ->whereNotIn('final_payment',[0, ""])
            ->where('offer_vn', 0)
            ->where('offer_cn', 0)
            ->orderBy('id', 'desc')
            ->get();


        foreach ($orders as $order) {
            $amount = $order->amount();
            $amount = str_replace(",", "", $amount);

            if ($amount != $order->final_payment) {

                unset($order->items);

                echo "amount: " . $amount . "\n";
                echo "offer: " . $order->final_payment . "\n";
                dd("oke");
                // dd($order);
            }

        }

    }
}
