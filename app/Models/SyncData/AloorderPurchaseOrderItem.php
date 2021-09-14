<?php

namespace App\Models\SyncData;

use Illuminate\Database\Eloquent\Model;

class AloorderPurchaseOrderItem extends Model {

    protected $connection = "aloorder";

    protected $table = "items";
}