<?php

namespace App\Models\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Models\TransportOrder\TransportCode;
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
        'supporter_sale_id',
        '_id'
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
                $price = (float) $item->price;
                $price = number_format($price, 2, '.', '');
                $total += $item->qty_reality * $price;
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
        if ($this->transport_code == null) {
            return 0;
        } else {
            $arr_codes = explode(",", $this->transport_code);
            if (! is_array($arr_codes) || sizeof($arr_codes) == 0) {
                return 0;
            }
            else {
                return TransportCode::whereIn('transport_code', $arr_codes)->sum('kg');
            }
        }
    }

    public function amount($format = true) {
        // try {
            $total = $this->sumItemPrice(false) + $this->sumShipFee(false) + $this->purchase_order_service_fee;
            return $format ? str_replace(".00", "", number_format($total, 2)) : $total;
        // } catch (\Exception $e) {
        //     // dd($this->id);
        // }
        
    }

    // nguoi huy
    public function userCancle() {
        return $this->hasOne(User::class, 'id', 'user_cancle_at');
    }

    public function depositeAmountCal($type = 'vn') {
        $rmb =  $this->sumItemPrice(false) / 100 * 70;
        return $type == 'vn' ? str_replace(",", "", number_format($rmb * $this->current_rate, 0)) : $rmb;
    }

    public function countItemFollowStatus() {
        switch ($this->statusText->code) {
            case "vn-recevice": 
                if ($this->transport_code != "") {
                    $arr = explode(',', $this->transport_code);
                    $arr = array_filter($arr);
                    $done = TransportCode::whereIn('transport_code', $arr)->whereNotNull('transport_code')->where('status', '!=', 0)->count();
                    return " (".$done."/".sizeof($arr).")";
                } else {
                    return " (0/0)";
                }
            case "deposited":
                return " (" . $this->items()->whereStatus(1)->count() . "/" . $this->items()->where('status', '!=', 4)->count() .")";
        }
    }

    public function countProductFollowStatus() {
        $service = new OrderService();
        switch ($this->statusText->code) {
            case "vn-recevice": 
                $allItems = $this->items->where('status', '!=', $service->getItemStatus('out_stock'))->count();
                $vnItems = $this->items->where('status', $service->getItemStatus('vn_received'))->count();

                return " (".$vnItems."/".$allItems.")";
        }
    }
}
