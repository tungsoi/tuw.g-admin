<?php

namespace App\Models\SyncData;

use Illuminate\Database\Eloquent\Model;

class AlilogiTransportCode extends Model {

    protected $connection = "alilogi";

    protected $table = "transport_order_items";

    public function order() {
        return $this->hasOne(AlilogiTransportOrder::class, 'id', 'order_id');
    }
}