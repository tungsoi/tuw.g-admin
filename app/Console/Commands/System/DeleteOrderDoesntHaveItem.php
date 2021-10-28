<?php

namespace App\Console\Commands\System;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\System\ScheduleLog;
use Illuminate\Console\Command;

class DeleteOrderDoesntHaveItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-order:delete-non-item';

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
        $time = date('Y-m-d', strtotime(now()));
        $orders = PurchaseOrder::select('id', 'order_number')->where('status', 2)->where('created_at', 'like', $time.'%')->get();
        echo "Total: " . $orders->count() . "\n";
        $numb = 0;
        $text = "";
        foreach ($orders as $key => $order) {
            $items = PurchaseOrderItem::where('order_id', $order->id)->get();

            if ($items->count() == 0) {

                $text .= $order->order_number . ", ";
                PurchaseOrder::find($order->id)->update([
                    'status'    =>  10,
                    'cancle_at' =>  now(),
                    'user_cancle_at'    =>  1
                ]);

                $numb ++;
            } else {
                echo ($key+1) . " - ". $order->order_number . ": pass \n";
            }
        }
        
        ScheduleLog::create([
            'name'  =>  $this->signature . " - " . $numb,
            'content'   =>  $text
        ]);
    }
}
