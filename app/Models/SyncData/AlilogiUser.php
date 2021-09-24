<?php

namespace App\Models\SyncData;

use Illuminate\Database\Eloquent\Model;

class AlilogiUser extends Model {

    protected $connection = "alilogi";

    protected $table = "admin_users";
}