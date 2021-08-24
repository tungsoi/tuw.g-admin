<?php

namespace App\Models\SyncData;

use Illuminate\Database\Eloquent\Model;

class AlilogiTransportOrder extends Model {

    protected $connection = "alilogi";

    protected $table = "orders";
}