<?php

namespace App\Admin\Services;

use App\Jobs\HandleCustomerWallet;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItemStatus;
use App\Models\PurchaseOrder\PurchaseOrderStatus;
use App\Models\Setting\RoleUser;
use App\Models\System\CustomerPercentService;
use App\Models\System\ExchangeRate;
use App\Models\System\Transaction;
use App\Models\System\Warehouse;
use App\Models\TransportOrder\TransportCodeStatus;
use App\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService {

    public function generatePaymentOrderNumber() {
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'W', 'J', 'Z'];
        $orderObj = PaymentOrder::select('order_number')->latest('id')->first();
        if ($orderObj) {
            $orderNumber = $orderObj->order_number;
            $firstOrderNumber = $orderNumber[0];
            $removed1char = substr($orderNumber, 1);
            $generateOrder_nr = str_pad((string)($removed1char + 1), 4, "0", STR_PAD_LEFT);
            $key = array_search($firstOrderNumber, $letters);
            if ((int)$removed1char === 9999) {
                $key++;
                $generateOrder_nr = str_pad('1', 4, "0", STR_PAD_LEFT);
            }
            $generateOrder_nr = $letters[$key] . $generateOrder_nr;
        } else {
            $generateOrder_nr = 'A' . str_pad('1', 4, "0", STR_PAD_LEFT);
        }
        return $generateOrder_nr;
    }

    public static function generateOrderNR()
    {
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'W', 'J', 'Z'];
        $orderObj = DB::table('purchase_orders')->whereRaw('LENGTH(order_number) < 9')->select('order_number')->latest('id')->first();
        if ($orderObj) {
            $orderNumber = $orderObj->order_number;
            $orderNumber = explode("-", $orderNumber)[1];
            $firstOrderNumber = $orderNumber[0];
            $removed1char = substr($orderNumber, 1);
            $generateOrder_nr = str_pad((string)($removed1char + 1), 4, "0", STR_PAD_LEFT);
            $key = array_search($firstOrderNumber, $letters);
            if ((int)$removed1char === 9999) {
                $key++;
                $generateOrder_nr = str_pad('1', 4, "0", STR_PAD_LEFT);
            }
            $generateOrder_nr = $letters[$key] . $generateOrder_nr;
        } else {
            $generateOrder_nr = 'A' . str_pad('1', 4, "0", STR_PAD_LEFT);
        }
        return 'MH-'.$generateOrder_nr;
    }

    // l???y id purchase order status b???ng code -> tr??? v??? id
    // new-order -> 2
    public function getStatus($code) {
        return PurchaseOrderStatus::whereCode($code)->first()->id;
    }

    public function getCurrentRate() {
        return ExchangeRate::first()->vnd;
    }

    public function getItemTotalAmount($ids) {
        $items = PurchaseOrderItem::select('qty', 'price')->whereIn('id', $ids)->get();

        $total = 0;

        foreach ($items as $item) {
            $total += ($item->qty * str_replace(",", ".", $item->price));
        }

        return number_format($total, 2);
    }

    public function calOrderService($total, $percent) {
        $total = (float) str_replace(",", "", $total);
        $service = number_format($total / 100 * $percent, 2);

        return $service;
    }

    public function getItemStatus($code) {
        return PurchaseOrderItemStatus::whereCode($code)->first()->id;
    }

    // Hu??? ????n mua h???
    public function canclePurchaseOrder($orderId) {
        $order = PurchaseOrder::find($orderId);

        switch ($order->status) {
            case $this->getStatus('new-order'):
                $order->update([
                    'status'    =>  $this->getStatus('cancle'),
                    'cancle_at' =>  now(),
                    'user_cancle_at'    =>  Admin::user()->id
                ]);

                return true;
            case $this->getStatus('deposited'):
                $order->update([
                    'status'    =>  $this->getStatus('cancle'),
                    'cancle_at' =>  now(),
                    'user_cancle_at'    =>  Admin::user()->id
                ]);

                $job = new HandleCustomerWallet(
                    $order->customer_id,
                    1,
                    $order->deposited,
                    2,
                    "Kh??ch h??ng hu??? ????n. Ho??n ti???n c???c ????n h??ng mua h??? $order->order_number",
                    $order->id
                );

                dispatch($job);

                return true;
            case $this->getStatus('ordered'):
                $order->update([
                    'status'    =>  $this->getStatus('cancle'),
                    'cancle_at' =>  now(),
                    'user_cancle_at'    =>  Admin::user()->id
                ]);

                $job = new HandleCustomerWallet(
                    $order->customer_id,
                    1,
                    $order->deposited,
                    2,
                    "Hu??? ????n h??ng. Ho??n ti???n c???c ????n h??ng mua h??? $order->order_number",
                    $order->id
                );

                dispatch($job);

                return true;
        }
    }

    public function getTransportCodeStatus($code) {
        return TransportCodeStatus::whereCode($code)->first()->id;
    }
}