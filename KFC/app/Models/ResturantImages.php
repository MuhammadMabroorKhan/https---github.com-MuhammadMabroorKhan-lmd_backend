<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResturantImages extends Model
{
    protected $table = 'restaurant_images';
    protected $fillable = [
        'restaurant_id',
        'image_path',
        'description'
    ];
    public $timestamps = false;
}
