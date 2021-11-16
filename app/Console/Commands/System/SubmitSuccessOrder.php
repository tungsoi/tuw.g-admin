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
        foreach ($orders as $order) {
            $items = $order->items->count();
            $all_items = $order->items->where('status', '!=', 4)->count();
            $vn_items = $order->items->where('status', 3)->count();
            $cancel_items = $order->items->where('status', 4)->count();

            if ($items == 1 && $cancel_items == 1) {

            }
            else if ($all_items == $vn_items) {
                echo $key . "-" . $order->order_number. "\n";
                $key++;
                $job = new HandleSubmitSuccessOrder($order->id);
                dispatch($job);
            }
        }

        ScheduleLog::create([
            'name'  =>  $this->signature . " - " . $key
        ]);
    }
}
