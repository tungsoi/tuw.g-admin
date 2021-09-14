<?php

namespace App\Models\TransportOrder;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;

class TransportCode extends Model
{
    const CHINA_RECEIVE = 0;
    const VIETNAM_RECEIVE = 1;
    const WAITTING_PAYMENT = 2;
    const PAYMENT = 3;
    const SWAP_WAREHOUSE = 4;
    const NOT_EXPORT = 5;

    protected $table = "transport_codes";

    protected $fillable = [
        'transport_code',
        'kg',
        'length',
        'width',
        'height',
        'order_id',
        'price_service',
        'advance_drag',
        'status',
        'china_receive_at',
        'vietnam_receive_at',
        'waitting_payment_at',
        'payment_at',
        'begin_swap_warehouse_at',
        'finish_swap_warehouse_at',
        'china_receive_user_id',
        'vietnam_receive_user_id',
        'payment_user_id',
        'begin_swap_user_id',
        'finish_swap_user_id',
        'admin_note',
        'customer_note',
        'customer_code_input',
        'ware_house_id',
        'payment_type',
        'ware_house_swap_id',
        'internal_note',
        'payment_note',
        'export_at'
    ];

    public function paymentOrder()
    {
        return $this->hasOne(PaymentOrder::class, 'id', 'order_id');
    }

    public function warehouse() {
        return $this->hasOne('App\Models\System\Warehouse', 'id', 'ware_house_id');
    }

    public function warehouseSwap() {
        return $this->hasOne('App\Models\System\Warehouse', 'id', 'ware_house_swap_id');
    }


    public function userCreated() {
        return $this->hasOne('App\User', 'id', 'created_user_id');
    }
    
    public function v() {
        try {
            $width = ($this->width != "") ? $this->width : 0;
            $height = ($this->height != "") ? $this->height : 0;
            $length = ($this->length != "") ? $this->length : 0;

            return number_format(($width * $height * $length)/6000, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function m3() {
        try {
            $width = ($this->width != "") ? $this->width : 0;
            $height = ($this->height != "") ? $this->height : 0;
            $length = ($this->length != "") ? $this->length : 0;
            
            return number_format(($width * $height * $length)/1000000, 3);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getStatus() {
        switch ($this->status) {
            case self::CHINA_RECEIVE: 
                return 'Đã về kho Trung Quốc';
            case self::VIETNAM_RECEIVE:
                return 'Đã về kho Việt Nam (' . $this->warehouse->name . ')';
            case self::WAITTING_PAYMENT:
                return 'Đã thanh toán tạm';
            case self::PAYMENT:
                return 'Đã xuất kho';
            case self::SWAP_WAREHOUSE:
                return 'Đang luân chuyển từ ' . $this->warehouse->name . ' đến ' . $this->warehouseSwap->name;
        }
    }

    public function paymentType() {
        if ($this->payment_type == "") { return null; }

        return $this->payment_type == 1 ? 'Khối lượng' : 'Mét khối';
    }

    public function statusText() {
        return $this->hasOne(TransportCodeStatus::class, 'id', 'status');
    }

    public function getTimeline() {
        switch ($this->status) {
            case self::CHINA_RECEIVE: 
                return $this->china_receive_at != null ? date('H:i | d-m-Y', strtotime($this->china_receive_at)) : null;
            case self::VIETNAM_RECEIVE:
                return $this->vietnam_receive_at != null ? date('H:i | d-m-Y', strtotime($this->vietnam_receive_at)) : null;
            case self::WAITTING_PAYMENT:
                return $this->waitting_payment_at != null ? date('H:i | d-m-Y', strtotime($this->waitting_payment_at)) : null;
            case self::PAYMENT:
                return $this->payment_at != null ? date('H:i | d-m-Y', strtotime($this->payment_at)) : null;
            case self::SWAP_WAREHOUSE:
                return $this->begin_swap_warehouse_at != null ? date('H:i | d-m-Y', strtotime($this->begin_swap_warehouse_at)) : null;
            case self::NOT_EXPORT:
                    return $this->payment_at != null ? date('H:i | d-m-Y', strtotime($this->payment_at)) : null;
        }
    }

    public function amount() {
        if ($this->payment_type == 1) {
            return $this->price_service * $this->kg;
        } else if ($this->payment_type == -1) {
            return $this->m3() * $this->price_service;
        } else {
            return 0;
        }
    }

    public function getOrdernNumberPurchase() {
        $orders = PurchaseOrder::select('transport_code', 'order_number')->where('transport_code', 'like', '%'.$this->transport_code.'%')->pluck('order_number')->toArray();
        
        return sizeof($orders) > 0 ? implode(", ", $orders) : null;
    }
}
