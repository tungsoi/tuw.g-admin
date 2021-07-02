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
        'transport_order_id',
        'v',
        'm3',
        'price_service',
        'advance_drag',
        'status',
        'china_recevie_at',
        'vietnam_recevie_at',
        'waitting_payment_at',
        'payment_at',
        'begin_swap_warehouse_at',
        'finish_swap_warehouse_at',
        'created_user_id',
        'admin_note',
        'customer_note',
        'customer_code_input',
        'ware_house_id'
    ];

    public function transportOrder() {

    }

    public function warehouse() {
        return $this->hasOne('App\Models\System\Warehouse', 'id', 'ware_house_id');
    }

    public function userCreated() {
        return $this->hasOne('App\User', 'id', 'created_user_id');
    }
}
