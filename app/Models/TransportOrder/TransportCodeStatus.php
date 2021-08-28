<?php

namespace App\Models\TransportOrder;

use Illuminate\Database\Eloquent\Model;

class TransportCodeStatus extends Model
{
    protected $table = "transport_code_statuses";

    protected $fillable = [
        'code',
        'name',
        'label'
    ];
}
