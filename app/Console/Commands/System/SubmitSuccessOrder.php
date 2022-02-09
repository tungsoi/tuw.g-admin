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
        $orders = PurchaseOrder::whereIn('status', [5, 7])->orderBy('id', 'desc')->with('items')->get();
        // da dat hang hoac da ve viet nam

        echo $orders->count() . "\n";
        
        $key = 0;
        $data = [];
        foreach ($orders as $order) {
            echo $order->order_number . "\n";

            $all_items = $order->items->count(); // tat ca san pham
            $vn_items = $order->items->where('status', 3)->count(); // da ve viet nam
            $cancel_items = $order->items->where('status', 4)->count(); // da het hang
            $order_items = $order->items->where('status', 1)->count(); // da dat hang
            $new_items = $order->items->where('status', 0)->count(); // chua dat hang

            $flag = [
                $order->order_number,
                'all_items' =>  $all_items,
                'vn_items' =>  $vn_items,
                'cancel_items' =>  $cancel_items,
                'order_items' =>  $order_items,
                'new_items' =>  $new_items,
            ];
            // case 1: tat ca san pham chua dat hang // all_items == new_items
            // nothing

            // case 2: tat ca san pham da dat hang // all_items == order_items
            // nothing

            // case 3: tat ca san pham da ve viet nam // all_items == vn_items || all_items == (vn_items + cancel_items)
            // chot thanh cong
            if ( ($all_items != $cancel_items) && ($all_items == $vn_items || $all_items == ($vn_items + $cancel_items))) {
                $flag['flag'] = "submit";
                // echo $key . "-" . $order->order_number. "\n";
                // $key++;
                // $job = new HandleSubmitSuccessOrder($order->id);
                // dispatch($job);
            }

            // case 4: tat ca san pham da het hang // all_items == cancel_items
            // huy don

            else if ($all_items == $cancel_items) {
                $flag['flag'] = "cancel";
            }

            else {
                $flag['flag'] = "nothing";
            }

            $data[] = $flag;
        }

        ScheduleLog::create([
            'name'  =>  $this->signature . " - " . $key,
            'content'   =>  json_encode($data)
        ]);
    }
}
