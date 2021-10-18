<?php

namespace App\Console\Commands\System;

use App\Admin\Services\OrderService;
use App\Jobs\HandleSubmitSuccessOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\System\ScheduleLog;
use App\Models\System\Transaction;
use App\Models\TransportOrder\TransportCode;
use Illuminate\Console\Command;

class SubmitSuccessOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submit:success-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chốt thành công đơn mua hộ';

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
        $service = new OrderService();
        $orders = PurchaseOrder::whereStatus(7)->orderBy('id', 'desc')->get();

        echo $orders->count() . "\n";
 
        $key = 0;
        foreach ($orders as $order) {
            $all_items = $order->items->where('status', '!=', 4)->count();
            $vn_items = $order->items->where('status', 3)->count();

            if ($order->transport_code != "") {
                $arr = explode(',', $order->transport_code);
                $arr = array_filter($arr);

                $all_trscs = sizeof($arr);
                $vn_trscs = TransportCode::whereIn('transport_code', $arr)->whereIn('status', [1,3,5])->count();

                if ($all_items == $vn_items && $all_trscs == $vn_trscs) {

                    echo $key . "-" . $order->order_number. "\n";

                    $key++;

                    $job = new HandleSubmitSuccessOrder($order->id);
                    dispatch($job);
                }
            }
        }

        ScheduleLog::create([
            'name'  =>  $this->signature . " - " . $key
        ]);
    }

    public function toString($arr) {
        echo implode(" -- ", $arr). "\n";
    }
}
