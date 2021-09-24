<?php

namespace App\Models\SyncData;

use Illuminate\Database\Eloquent\Model;

class AlilogiTransaction extends Model {

    protected $connection = "alilogi";

    protected $table = "transport_recharges";
}