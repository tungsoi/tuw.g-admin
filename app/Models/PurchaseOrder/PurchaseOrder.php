<?php

namespace App\Models\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\User;
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
        'vn_receive_at',
        'success_at',
        'cancle_at',
        'final_payment',
        'user_created_id',
        'user_deposited_at',
        'user_order_at',
        'user_vn_receive_at',
        'user_success_at',
        'user_cancle_at',
        'transport_code',
    ];

    public function customer() {
        return $this->hasOne('App\User', 'id', 'customer_id');
    }

    public function statusText() {
        return $this->hasOne('App\Models\PurchaseOrder\PurchaseOrderStatus', 'id', 'status');
    }

    public function getStatusTimeline() {
        switch ($this->statusText->code) {
            case "new-order": 
                return $this->created_at != null ? date('H:i | d-m-Y', strtotime($this->created_at)) : "";
            case "deposited": 
                return $this->deposited_at != null ? date('H:i | d-m-Y', strtotime($this->deposited_at)) : "";
            case "ordered": 
                return $this->order_at != null ? date('H:i | d-m-Y', strtotime($this->order_at)) : "";
            case "vn-recevice": 
                return $this->vn_receive_at != null ? date('H:i | d-m-Y', strtotime($this->vn_receive_at)) : "";
            case "success": 
                return $this->success_at != null ? date('H:i | d-m-Y', strtotime($this->success_at)) : "";
            case "cancle": 
                return $this->cancle_at != null ? date('H:i | d-m-Y', strtotime($this->cancle_at)) : "";
        }
    }

    public function getUserAction() {
        switch ($this->statusText->code) {
            case "new-order": 
                return $this->createdUser->name ?? "";
            case "deposited": 
                return $this->depositedUser->name ?? "";
            case "ordered": 
                return $this->orderedUser->name ?? "";
            case "vn-recevice": 
                return $this->vnReceiveUser->name ?? "";
            case "success": 
                return $this->successedUser->name ?? "";
            case "cancle": 
                return $this->userCancle->name ?? "";
        }
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

    public function vnReceiveUser() {
        return $this->hasOne('App\User', 'id', 'user_vn_receive_at');
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

    // nguoi huy
    public function userCancle() {
        return $this->hasOne(User::class, 'id', 'user_cancle_at');
    }

    public function depositeAmountCal($type = 'vn') {
        $rmb =  $this->sumItemPrice(false) / 100 * 70;
        return $type == 'vn' ? str_replace(",", "", number_format($rmb * $this->current_rate, 0)) : $rmb;
    }
}