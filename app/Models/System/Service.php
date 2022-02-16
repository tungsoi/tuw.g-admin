<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = "fr_service";

    /**
     * Fields
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'image',
        'description'
    ];
    public function getImageAttribute($image)
    {
        if (is_string($image)) {
            return json_decode($image, true);
        }

        return $image;
    }

    public function setImageAttribute($image)
    {
        if (is_array($image)) {
            $this->attributes['image'] = json_encode($image);
        }
    }
}
