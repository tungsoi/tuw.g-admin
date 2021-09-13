<?php

namespace App\Console\Commands\SyncData;

use App\Models\PurchaseOrder\PurchaseOrder as PurchaseOrderPurchaseOrder;
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
        // $orders = AloorderPurchaseOrder::where('id', '<', '13548')->orderBy('id', 'desc')->get();

        // foreach ($orders as $order) {
        //     echo $order->order_number . "\n";

        //     PurchaseOrderPurchaseOrder::firstOrCreate([
        //         '_id'    =>  $order->id,
        //         'shop_name' =>  $order->shop_name,
        //         'order_number'  =>  $order->order_number,
        //         'customer_id'   =>  $order->customer_id,
        //         'status'    =>  $order->status,
        //         'deposited' =>  (int) $order->deposited,
        //         'customer_note' =>  $order->customer_note,
        //         'admin_note'    =>  $order->admin_note,
        //         'internal_note' =>  $order->internal_note,
        //         'warehouse_id'  =>  $order->warehouse_id,
        //         'current_rate'  =>  $order->current_rate, 
        //         'supporter_order_id'    =>  $order->supporter_order_id,
        //         'purchase_order_service_fee'    =>  $order->purchase_order_service_fee,
        //         'deposited_at'  =>  $order->deposited_at,
        //         'order_at'  =>  $order->order_at,
        //         'vn_receive_at' =>  null,
        //         'success_at'    =>  $order->success_at,
        //         'cancle_at' =>  null,
        //         'final_payment' =>  $order->final_payment,
        //         'user_created_id'   =>  $order->user_created_id,
        //         'user_deposited_at' =>  $order->user_id_deposited,
        //         'user_order_at' =>  $order->user_id_confirm_ordered,
        //         'user_vn_receive_at'    =>  null,
        //         'user_success_at'   =>  null,
        //         'user_cancle_at'    =>  null,
        //         'transport_code'    =>  null,
        //         'supporter_sale_id' =>  ! is_null($order->supporter_id) ? $order->supporter_id : null,
        //     ]);
        // }
        
        $ids = PurchaseOrderItem::select('order_id')->where('cn_code', "!=", null)->groupBy('order_id')->get();

        foreach ($ids as $orderId) {
            $order = $orderId->order;

            if ($order && $order->transport_code == "") {
                if ($order->items->where('cn_code', "!=", null)->count() > 0) {
                    echo $order->order_number . "\n";
                    $cn_code = [];
                    foreach ($order->items->where('cn_code', "!=", null) as $item) {
                        $cn_code[$item->cn_code] = $item->cn_code;
                    }
        
                    $order->transport_code = implode(',', $cn_code);
                    $order->save();
                } 
            } 
        }
    }
}
