<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FURNITUREImages extends Model
{
    protected $table = 'furniture_images';
    protected $fillable = [
        'furniture_id',
        'image_path',
        'description'
    ];
    public $timestamps = false;
}
