<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class TeamSale extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = "team_sales";

    /**
     * Fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'leader',
        'members'
    ];

    public function getMembersAttribute($value)
    {
        return explode(',', $value);
    }

    public function setMembersAttribute($value)
    {
        $this->attributes['members'] = implode(',', $value);
    }

    public function leaderStaff()
    {
        return $this->hasOne('App\User', 'id', 'leader');
    }
}
