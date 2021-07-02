<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = "admin_role_users";

    protected $fillable = [
        'role_id',
        'user_id'
    ];
}
