<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $table = "alerts";

    protected $fillable = [
        'title',
        'content',
        'created_user_id'
    ];

    public function userCreated() {
        return $this->hasOne('App\User', 'id', 'created_user_id');
    }
}
