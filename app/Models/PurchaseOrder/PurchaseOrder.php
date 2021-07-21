<?php

namespace App\Models\PurchaseOrder;

use App\Admin\Services\OrderService;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = "purchase_orders";

    protected $fillable = [
        'shop_name',
        'order_number',
        'customer_id',
        'status',
        'deposited',
        'customer_note',
        'admin_note',
        'internal_note',
        'warehouse_id',
        'current_rate',
        'supporter_order_id',
        'purchase_order_service_fee',
        'deposited_at',
        'order_at',
        'success_at',
        'cancle_at',
        'final_payment',
        'user_created_id',
        'user_deposited_at',
        'user_order_at',
        'user_success_at',
        'transport_code'
    ];

    public function customer() {
        return $this->hasOne('App\User', 'id', 'customer_id');
    }

    public function statusText() {
        return $this->hasOne('App\Models\PurchaseOrder\PurchaseOrderStatus', 'id', 'status');
    }

    public function warehouse() {
        return $this->hasOne('App\Models\System\Warehouse', 'id', 'warehouse_id');
    }

    public function orderEmployee() {
        return $this->hasOne('App\User', 'id', 'supporter_order_id');
    }

    public function createdUser() {
        return $this->hasOne('App\User', 'id', 'user_created_id');
    }

    public function depositedUser() {
        return $this->hasOne('App\User', 'id', 'user_deposited_at');
    }

    public function orderedUser() {
        return $this->hasOne('App\User', 'id', 'user_order_at');
    }

    public function successedUser() {
        return $this->hasOne('App\User', 'id', 'user_success_at');
    }

    public function items() {
        return $this->hasMany('App\Models\PurchaseOrder\PurchaseOrderItem', 'order_id', 'id');
    }

    // items

    public function totalItems() {
        $service = new OrderService();
        return $this->items->where('status', '!=', $service->getItemStatus('out_stock'))->sum('qty_reality');
    }

    public function sumItemPrice($format = true) {
        $service = new OrderService();
        $total = 0;
        foreach ($this->items as $item) {
            if ($item->status != $service->getItemStatus('out_stock')) {
                $total += $item->qty_reality * str_replace(',', '.', $item->price);
            }   
        }

        return $format ? str_replace(".00", "", number_format($total, 2)) : $total;
    }

    public function sumShipFee($format = true) {
        $service = new OrderService();
        $total = 0;
        foreach ($this->items as $item) {
            if ($item->status != $service->getItemStatus('out_stock')) {
                if ($item->purchase_cn_transport_fee != "") {
                    $total += str_replace(",", ".", $item->purchase_cn_transport_fee);
                }
                
            }   
        }

        return $format ? str_replace(".00", "", number_format($total, 2)) : $total;
    }

    public function sumItemWeight() {
        // return "Tính link theo mã vận đơn";

        $service = new OrderService();
        $total = 0;
        foreach ($this->items as $item) {
            if ($item->status != $service->getItemStatus('out_stock')) {
                if ($item->purchase_cn_transport_fee != "") {
                    $total += str_replace(",", ".", $item->purchase_cn_transport_fee);
                }
                
            }   
        }

        return str_replace(".00", "", number_format($total, 2));
    }

    public function amount($format = true) {
        $total = $this->sumItemPrice(false) + $this->sumShipFee(false) + $this->purchase_order_service_fee;
        return $format ? str_replace(".00", "", number_format($total, 2)) : $total;
    }
}
