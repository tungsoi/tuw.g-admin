<?php

namespace App\Console\Commands\SyncData;

use App\Models\PurchaseOrder\PurchaseOrder as PurchaseOrderPurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem as PurchaseOrderPurchaseOrderItem;
use App\Models\Setting\RoleUser;
use App\Models\SyncData\AloorderPurchaseOrder;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurchaseOrderItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:purchase-order-item';

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
        // case 1: giá để dạng 200,00 -> đúng là 200.00 
        // $items = PurchaseOrderPurchaseOrderItem::where('price', 'like', '%,00')->get();

        // foreach ($items as $item) {
        //     echo $item->id . "\n";
        //     $item->price = str_replace(',', '.', $item->price);
        //     $item->save();
        // }

        // case 2: price 1,05 -> 1.05
        // $items = PurchaseOrderPurchaseOrderItem::where('price', 'like', '%,%')->whereRaw('LENGTH(price) = 12')->get();

        // echo $items->count() . "\n";
        // foreach ($items as $item) {
        //     echo $item->id . "\n";
        //     $price = explode(',', $item->price);
        //     $item->price = $price[0];
        //     $item->save();
        // }
    }
}
