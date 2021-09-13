<?php

namespace App\Models\SyncData;

use Illuminate\Database\Eloquent\Model;

class AloorderPurchaseOrder extends Model {

    protected $connection = "aloorder";

    protected $table = "purchase_orders";
}