<?php

namespace App\Console\Commands\SyncData;

use App\Models\PurchaseOrder\PurchaseOrder as ThisPurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\Setting\RoleUser;
use App\Models\SyncData\AloorderPurchaseOrder;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:purchase-order';

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
        $orders = AloorderPurchaseOrder::select('id', 'order_number', 'created_at')->orderBy('id', 'desc')->get();

        foreach ($orders as $order) {
            $thisOrder = ThisPurchaseOrder::select('id', 'order_number', 'created_at')->whereOrderNumber($order->order_number)->first();
            if ($thisOrder && $order->created_at != $thisOrder->created_at) {
                echo $order->order_number . "\n";

                $thisOrder->created_at = $order->created_at;
                $thisOrder->save();
            }
        }
    }
}
