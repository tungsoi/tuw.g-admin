<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TransportCodeUpdateLog extends Model
{
    protected $table = "transport_code_update_logs";
    protected $fillable = [
        'transport_code_id',
        'before',
        'after',
        'user_updated_id'
    ];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_updated_id');
    }
}
