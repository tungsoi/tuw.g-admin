<?php

namespace App\Admin\Services;

use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItemStatus;
use App\Models\PurchaseOrder\PurchaseOrderStatus;
use App\Models\Setting\RoleUser;
use App\Models\System\CustomerPercentService;
use App\Models\System\ExchangeRate;
use App\Models\System\Transaction;
use App\Models\System\Warehouse;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransportService {

    public function getItemStatus($code) {
        return PurchaseOrderItemStatus::whereCode($code)->first()->id;
    }
}