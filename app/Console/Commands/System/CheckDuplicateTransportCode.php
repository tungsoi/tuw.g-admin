<?php

namespace App\Console\Commands\System;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\TransportOrder\TransportCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicateTransportCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:duplicate-transport-code';

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
        $orders = PaymentOrder::where('status', 'payment_export')
        ->where('is_sub_customer_wallet_weight', 0)
        ->where('amount', 0)
        ->where('created_at', 'like', '2021%')
        ->get();
        
        // dd(implode(",", $orders));
        foreach ($orders as $key => $order) {
            echo ($key+1) 
            . " - Mã đơn hàng: " 
            . $order->order_number 
            . " - KH thanh toán: " . $order->paymentCustomer->symbol_name
            . " - Tổng cân: " 
            . $order->total_kg 
            . " - Ngày tạo: " 
            . date('d-m-Y', strtotime($order->created_at)) 
            . " - Người tạo: " . $order->userCreated->name
            . "\n";
        }

        // dd($orders);
        // $codes = TransportCode::groupBy('transport_code')
        // ->where('china_receive_at', 'like', '2021%')
        // ->orWhere('vietnam_receive_at', 'like', '2021%')
        // ->having(DB::raw('count(transport_code)'), '>', 1)
        // ->pluck('transport_code');

        // foreach ($codes as $code) {
        //     $merge = TransportCode::where('transport_code', $code)->get()->toArray();

        //     dd($merge);
        // }
        // dd($codes);

        // foreach ($codes as $code) {
        //     $flag = TransportCode
        // }
    }
}
