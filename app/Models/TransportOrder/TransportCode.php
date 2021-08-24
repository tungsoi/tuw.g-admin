<?php

namespace App\Models\TransportOrder;

use Illuminate\Database\Eloquent\Model;

class TransportCode extends Model
{
    const CHINA_RECEIVE = 0;
    const VIETNAM_RECEIVE = 1;
    const WAITTING_PAYMENT = 2;
    const PAYMENT = 3;
    const SWAP_WAREHOUSE = 4;

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
        'china_recevie_at',
        'vietnam_recevie_at',
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
        'payment_type'
    ];

    public function transportOrder()
    {
    }

    public function warehouse() {
        return $this->hasOne('App\Models\System\Warehouse', 'id', 'ware_house_id');
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
        return $this->status;
    }
}
