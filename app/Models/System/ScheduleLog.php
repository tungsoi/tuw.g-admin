<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class ScheduleLog extends Model
{
    protected $table = "schedule_logs";

    protected $fillable = [
        'name'
    ];
}
