<?php

namespace App\Console\Commands\Report;

use App\Models\OrderReport\OrderReport as OrderReportModel;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\User;
use Illuminate\Console\Command;

class OrderReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:report';

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
        $today = date('Y-m-d', strtotime(now()));

        for ($i = 1; $i <= 31; $i++) {
            $today = "2021-11-" . str_pad($i, 2, 0, STR_PAD_LEFT);
            echo $today . "\n";
            $orders = PurchaseOrder::where('status', '!=', 10)->where('order_at', 'like', $today."%")->with('items')->get();

            $user_ids = $orders->unique('supporter_order_id')->pluck('supporter_order_id');

            $data = [];
            foreach ($user_ids as $user_id) {
                if ($user_id != "") {
                    $user_orders = $orders->where('supporter_order_id', $user_id);

                    $amount = $final_payment = $percent_service = $offer_cn = $offer_vn = $total = 0;
                    foreach ($user_orders as $user_order) {
                        $amount += $user_order->amount(false);
                        $final_payment += $user_order->final_payment;
                        $percent_service += $user_order->purchase_order_service_fee;
                        $offer_cn += number_format(str_replace(",", "", $user_order->offer_cn), 2, ".", "");
                        $offer_vn += (int) str_replace(",", "", $user_order->offer_vn);

                        $price_rmb = str_replace(",", "", $user_order->sumItemPrice());
                        $ship = str_replace(",", "", $user_order->sumShipFee());
                        $total_order = $price_rmb + $ship;
                        $total += $total_order;
                    }

                    $data[$user_id] = [
                    'user_name'   =>  User::find($user_id)->name ?? $user_id,
                    'number'    =>  $user_orders->count(),
                    'amount'    =>  number_format($amount, 2, ".", ""),
                    'final_payment' =>  number_format($final_payment, 2, ".", ""),
                    'percent_service'   =>  number_format($percent_service, 2, ".", ""),
                    'offer_cn'  =>  number_format($offer_cn, 2, ".", ""),
                    'offer_vn'  =>  number_format($offer_vn, 0, ".", ""),
                    'total' =>  number_format($total, 2, ".", ""),
                ];
                }
            }

            $res = [
                'order_at'  =>  $today,
                'content'   =>  json_encode($data)
            ];

            $flag = OrderReportModel::whereOrderAt($today)->first();

            if ($flag) {
                // update
                $flag->update($res);
            } else {
                OrderReportModel::create($res);
            }
        }
    }
}
