<?php

namespace App\Admin\Services;

use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItemStatus;
use App\Models\PurchaseOrder\PurchaseOrderStatus;
use App\Models\Setting\RoleUser;
use App\Models\System\CustomerPercentService;
use App\Models\System\ExchangeRate;
use App\Models\System\Transaction;
use App\Models\System\Warehouse;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService {

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
        return number_format($total / 100 * $percent, 2);
    }

    public function getItemStatus($code) {
        return PurchaseOrderItemStatus::whereCode($code)->first()->id;
    }
}